<?php

namespace App\Http\Controllers;

use App\DTOs\KelasDTO;
use App\Exceptions\SiakadApiException;
use App\Models\KelasMengajar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KelasMengajarController extends Controller
{
    public function __construct() {}

    // ──────────────────────────────────────────────────────────────────────────
    // INDEX: Halaman utama — lihat kelas dari SIAKAD + yang sudah diklaim
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Menampilkan halaman daftar kelas mengajar.
     *
     * Dua bagian utama:
     * 1. Kelas yang sudah diklaim (aktif/pending/ditolak)
     * 2. Modal pengajuan manual
     *
     * Catatan: Klaim kelas dari SIAKAD kini dilakukan di halaman matkul-pengajar.
     */
    public function index(Request $request)
    {
        $user    = Auth::user();
        $nip     = $user->userid;
        $periode = $request->get('periode', '');

        // ── Ambil kelas yang sudah diklaim dari DB ───────────────────────────
        $dbQuery = KelasMengajar::forUser($nip)->orderByDesc('diklaim_at');
        if ($periode) {
            $dbQuery->periode($periode);
        }
        $kelasDiklaim = $dbQuery->get();

        $kelasAktif   = $kelasDiklaim->where('status', 'aktif');
        $kelasPending = $kelasDiklaim->where('status', 'pending');
        $kelasDitolak = $kelasDiklaim->where('status', 'ditolak');

        // ── Daftar periode unik (untuk dropdown filter) ──────────────────────
        $periodeList = $this->buildPeriodeList();

        return view('pages.kelas-mengajar.index', compact(
            'kelasAktif',
            'kelasPending',
            'kelasDitolak',
            'periodeList',
            'periode',
        ));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // KLAIM: Klaim satu kelas dari SIAKAD → langsung aktif
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Simpan klaim kelas dari data SIAKAD.
     * Data kelas dikirim dari form (hidden fields dari card kelas).
     * Source = 'siakad', status langsung = 'aktif' (tidak perlu approval).
     */
    public function klaim(Request $request)
    {
        $validated = $request->validate([
            'kelas_id_siakad'  => 'required|string',
            'kode_mata_kuliah' => 'required|string|max:50',
            'nama_mata_kuliah' => 'required|string',
            'nama_kelas'       => 'required|string|max:10',
            'sks'              => 'required|integer|min:0|max:20',
            'id_periode'       => 'required|string|max:10',
            'periode_label'    => 'nullable|string|max:50',
            'program_studi'    => 'nullable|string',
            'jenjang'          => 'nullable|string|max:10',
            'id_kurikulum'     => 'nullable|string|max:20',
            'id_program_studi' => 'nullable|string|max:20',
            'daya_tampung'     => 'nullable|integer',
            'is_mbkm'          => 'nullable|boolean',
        ]);

        $nip = Auth::user()->userid;

        // Cek apakah sudah pernah diklaim
        $sudahAda = KelasMengajar::forUser($nip)
            ->where('kelas_id_siakad', $validated['kelas_id_siakad'])
            ->where('id_periode', $validated['id_periode'])
            ->exists();

        if ($sudahAda) {
            return back()->with('warning', 'Kelas ini sudah pernah diklaim.');
        }

        KelasMengajar::create(array_merge($validated, [
            'user_id'    => $nip,
            'source'     => 'siakad',
            'status'     => 'aktif',
            'diklaim_at' => now(),
            'is_mbkm'    => (bool) ($validated['is_mbkm'] ?? false),
        ]));

        return back()->with('success',
            "Kelas {$validated['kode_mata_kuliah']} - {$validated['nama_mata_kuliah']} berhasil diklaim!"
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // MANUAL: Form + Simpan pengajuan manual (perlu approval)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Form pengajuan kelas manual.
     */
    public function create()
    {
        $periodeList = $this->buildPeriodeList();
        return view('pages.kelas-mengajar.create', compact('periodeList'));
    }

    /**
     * Simpan pengajuan kelas manual.
     * Source = 'manual', status = 'pending' (menunggu approval admin).
     * SK Mengajar WAJIB diupload.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_mata_kuliah' => 'required|string|max:50',
            'nama_mata_kuliah' => 'required|string|max:255',
            'nama_kelas'       => 'required|string|max:10',
            'sks'              => 'required|integer|min:1|max:20',
            'id_periode'       => 'required|string|max:10',
            'program_studi'    => 'nullable|string|max:255',
            'catatan'          => 'nullable|string|max:1000',
            'sk_mengajar'      => 'required|file|mimes:pdf,jpg,jpeg,png,docx|max:5120', // max 5MB
        ]);

        $nip = Auth::user()->userid;

        // Upload SK Mengajar ke private storage
        $file            = $request->file('sk_mengajar');
        $fileName        = date('YmdHis') . '_' . $nip . '.' . $file->getClientOriginalExtension();
        $folderPath      = "sk_mengajar/{$nip}/" . substr($validated['id_periode'], 0, 4);
        $storedPath      = $file->storeAs($folderPath, $fileName, 'private');

        $periodeLabel = KelasDTO::formatPeriode($validated['id_periode']);

        KelasMengajar::create([
            'user_id'                   => $nip,
            'kelas_id_siakad'           => null,
            'kode_mata_kuliah'          => $validated['kode_mata_kuliah'],
            'nama_mata_kuliah'          => $validated['nama_mata_kuliah'],
            'nama_kelas'                => $validated['nama_kelas'],
            'sks'                       => $validated['sks'],
            'id_periode'                => $validated['id_periode'],
            'periode_label'             => $periodeLabel,
            'program_studi'             => $validated['program_studi'] ?? null,
            'catatan'                   => $validated['catatan'] ?? null,
            'source'                    => 'manual',
            'status'                    => 'pending',
            'sk_mengajar_path'          => $storedPath,
            'sk_mengajar_original_name' => $file->getClientOriginalName(),
            'sk_mengajar_mime'          => $file->getMimeType(),
            'sk_mengajar_size'          => $file->getSize(),
            'diklaim_at'                => now(),
        ]);

        return redirect()->route('kelas-mengajar.index')
            ->with('success', 'Pengajuan manual berhasil dikirim dan sedang menunggu persetujuan admin.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DOWNLOAD SK MENGAJAR (private file)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Download file SK Mengajar dari private storage.
     * Hanya pemilik yang bisa download.
     */
    public function downloadSK(KelasMengajar $kelasMengajar)
    {
        if ($kelasMengajar->user_id !== Auth::user()->userid) {
            abort(403);
        }

        if (!$kelasMengajar->hasSK()) {
            abort(404, 'File SK tidak ditemukan.');
        }

        if (!Storage::disk('private')->exists($kelasMengajar->sk_mengajar_path)) {
            abort(404, 'File tidak ada di storage.');
        }

        return Storage::disk('private')->download(
            $kelasMengajar->sk_mengajar_path,
            $kelasMengajar->sk_mengajar_original_name
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HAPUS / BATALKAN KLAIM
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Hapus klaim (hanya bisa untuk status pending atau aktif dari siakad).
     */
    public function destroy(KelasMengajar $kelasMengajar)
    {
        if ($kelasMengajar->user_id !== Auth::user()->userid) {
            abort(403);
        }

        // Hapus file SK jika ada
        if ($kelasMengajar->hasSK()) {
            Storage::disk('private')->delete($kelasMengajar->sk_mengajar_path);
        }

        $kelasMengajar->delete();

        return back()->with('success', 'Klaim berhasil dibatalkan.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Buat daftar periode untuk dropdown filter.
     * 5 tahun ke depan + 5 tahun ke belakang dari tahun ini.
     */
    private function buildPeriodeList(): array
    {
        $year  = (int) date('Y');
        $list  = [];

        for ($y = $year + 1; $y >= $year - 4; $y--) {
            $list[(string)($y * 10 + 1)] = "{$y}/" . ($y + 1) . " Gasal";
            $list[(string)($y * 10 + 2)] = "{$y}/" . ($y + 1) . " Genap";
        }

        return $list;
    }
}
