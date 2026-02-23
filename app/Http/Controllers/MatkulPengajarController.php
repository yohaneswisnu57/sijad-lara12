<?php

namespace App\Http\Controllers;

use App\DTOs\KelasDTO;
use App\Models\KelasMengajar;
use App\Services\DosenSiakadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MatkulPengajarController extends Controller
{
    public function __construct(
        private readonly DosenSiakadService $dosenService
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Halaman daftar mata kuliah yang diampu dosen.
     *
     * Alur:
     * 1. NIP dari Auth → resolve SEVIMA ID (cache 24 jam)
     * 2. Fetch semua kelas via /dosen/{id}/kelas (cache 60 menit)
     * 3. Load set kelas yang sudah diklaim dari DB (untuk status tombol)
     * 4. Grouped per periode → view
     */
    public function index(Request $request)
    {
        $user    = Auth::user();
        $nip     = $user->userid;
        $periode = $request->get('periode', '');

        // ── Resolve SEVIMA ID ────────────────────────────────────────────────
        $siakadId   = $this->dosenService->resolveSiakadId($nip);
        $dosenError = null;

        if ($siakadId === null) {
            $dosenError = "Data dosen dengan NIP {$nip} tidak ditemukan di SIAKAD. "
                        . "Pastikan NIP terdaftar dan berstatus aktif.";
        }

        // ── Fetch kelas dari API ─────────────────────────────────────────────
        $kelasList = collect();
        $apiError  = null;

        if ($siakadId !== null) {
            try {
                $kelasList = $this->dosenService->getKelas($siakadId, $periode);
            } catch (\Exception $e) {
                $apiError = 'Gagal memuat data kelas: ' . $e->getMessage();
            }
        }

        // ── Sudah diklaim: set kelas_id_siakad dari DB ───────────────────────
        // Digunakan di view untuk menentukan tombol Klaim vs badge Sudah Diklaim
        $dbQuery = KelasMengajar::forUser($nip)->whereNotNull('kelas_id_siakad');
        if ($periode) {
            $dbQuery->periode($periode);
        }
        $sudahDiklaim = $dbQuery
            ->pluck('kelas_id_siakad')
            ->map(fn ($v) => (string) $v)
            ->toArray();

        // ── Grouping: periode → kode+sks (merge kelas A,B,C jadi satu baris) ──
        $grouped = $kelasList
            ->sortByDesc('idPeriode')
            ->groupBy(fn (KelasDTO $k) => $k->idPeriode)
            ->map(function ($kelasPeriode) {
                // Group by kode_mata_kuliah + sks → merge kelas (A, B, C)
                return $kelasPeriode
                    ->groupBy(fn (KelasDTO $k) => $k->kodeMatKul . '|' . $k->sks)
                    ->values();
            });

        // ── Dropdown periode dari data API ────────────────────────────────────
        $periodeFromApi = $kelasList
            ->pluck('idPeriode')
            ->unique()
            ->sortDesc()
            ->mapWithKeys(fn (string $kode) => [
                $kode => KelasDTO::formatPeriode($kode),
            ])
            ->toArray();

        $totalKelas  = $kelasList->count();
        $totalSks    = $kelasList->sum('sks');
        // Jumlah mata kuliah unik (kode + sks)
        $totalMatkul = $kelasList->groupBy(fn (KelasDTO $k) => $k->kodeMatKul . '|' . $k->sks)->count();

        return view('pages.matkul-pengajar.index', compact(
            'kelasList',
            'grouped',
            'periodeFromApi',
            'periode',
            'siakadId',
            'nip',
            'totalKelas',
            'totalSks',
            'totalMatkul',
            'sudahDiklaim',
            'dosenError',
            'apiError',
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // KLAIM
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Simpan klaim kelas ke tr_kelas_mengajars.
     *
     * Menyimpan DUA kunci identitas dosen:
     *   user_id           = NIP (dari login lokal)
     *   id_pegawai_siakad = ID numerik SEVIMA (dari cache DosenSiakadService)
     *
     * SKS pengusul (sks_pengusul) bisa berbeda dari sks di SIAKAD.
     * SK Mengajar wajib diupload. Status langsung 'aktif'.
     */
    public function klaim(Request $request)
    {
        $validated = $request->validate([
            'kelas_ids'        => 'required|string',       // comma-separated kelas IDs
            'kode_mata_kuliah' => 'required|string|max:50',
            'nama_mata_kuliah' => 'required|string|max:255',
            'nama_kelas'       => 'required|string|max:255', // "A, B, C" gabungan
            'sks_pengusul'     => 'required|integer|min:1|max:20',
            'id_periode'       => 'required|string|max:10',
            'periode_label'    => 'nullable|string|max:50',
            'program_studi'    => 'nullable|string|max:255',
            'jenjang'          => 'nullable|string|max:10',
            'id_kurikulum'     => 'nullable|string|max:20',
            'id_program_studi' => 'nullable|string|max:20',
            'sks_siakad'       => 'nullable|integer',
            'daya_tampung'     => 'nullable|integer',
            'is_mbkm'          => 'nullable|boolean',
            'sk_mengajar'      => 'required|file|mimes:pdf,jpg,jpeg,png,docx|max:5120',
        ]);

        $user     = Auth::user();
        $nip      = $user->userid;
        $kelasIds = array_filter(explode(',', $validated['kelas_ids']));

        if (empty($kelasIds)) {
            return back()->with('error', 'Tidak ada kelas yang dipilih.');
        }

        // Filter: hanya kelas yang belum pernah diklaim
        $sudahDiklaim = KelasMengajar::forUser($nip)
            ->whereIn('kelas_id_siakad', $kelasIds)
            ->where('id_periode', $validated['id_periode'])
            ->pluck('kelas_id_siakad')
            ->map(fn ($v) => (string) $v)
            ->toArray();

        $belumDiklaim = array_filter($kelasIds, fn ($id) => !in_array((string)$id, $sudahDiklaim));

        if (empty($belumDiklaim)) {
            return back()->with('warning', 'Semua kelas ini sudah pernah diklaim.');
        }

        // Resolve SEVIMA ID dari cache
        $siakadId = $this->dosenService->resolveSiakadId($nip);

        // Upload SK Mengajar ke private storage (1 file untuk semua kelas group)
        $file       = $request->file('sk_mengajar');
        $tahun      = substr($validated['id_periode'], 0, 4);
        $fileName   = date('YmdHis') . '_' . $nip . '.' . $file->getClientOriginalExtension();
        $folderPath = "sk_mengajar/{$nip}/{$tahun}";
        $storedPath = $file->storeAs($folderPath, $fileName, 'private');

        if (!$storedPath) {
            return back()
                ->withInput()
                ->with('open_modal_klaim', true)
                ->with('error', 'Gagal menyimpan file SK Mengajar. Hubungi administrator.');
        }

        // Parse detail kelas dari JSON (kelas_detail) untuk nama kelas individual
        $kelasDetail = [];
        if ($request->has('kelas_detail_json')) {
            $kelasDetail = collect(json_decode($request->input('kelas_detail_json'), true) ?? [])
                ->keyBy('id');
        }

        // Buat record per kelas
        $now = now();
        foreach ($belumDiklaim as $kelasId) {
            $namaKelasIndividual = isset($kelasDetail[$kelasId])
                ? $kelasDetail[$kelasId]['namaKelas']
                : $validated['nama_kelas']; // fallback ke gabungan

            KelasMengajar::create([
                'user_id'                   => $nip,
                'id_pegawai_siakad'         => $siakadId,
                'kelas_id_siakad'           => $kelasId,
                'kode_mata_kuliah'          => $validated['kode_mata_kuliah'],
                'nama_mata_kuliah'          => $validated['nama_mata_kuliah'],
                'nama_kelas'                => $namaKelasIndividual,
                'sks'                       => $validated['sks_siakad'] ?? $validated['sks_pengusul'],
                'sks_pengusul'              => $validated['sks_pengusul'],
                'id_periode'                => $validated['id_periode'],
                'periode_label'             => $validated['periode_label'],
                'id_program_studi'          => $validated['id_program_studi'] ?? null,
                'program_studi'             => $validated['program_studi'] ?? null,
                'jenjang'                   => $validated['jenjang'] ?? null,
                'id_kurikulum'              => $validated['id_kurikulum'] ?? null,
                'daya_tampung'              => $validated['daya_tampung'] ?? null,
                'is_mbkm'                   => (bool) ($validated['is_mbkm'] ?? false),
                'source'                    => 'siakad',
                'status'                    => 'aktif',
                'sk_mengajar_path'          => $storedPath,
                'sk_mengajar_original_name' => $file->getClientOriginalName(),
                'sk_mengajar_mime'          => $file->getMimeType(),
                'sk_mengajar_size'          => $file->getSize(),
                'diklaim_at'                => $now,
            ]);
        }

        $jumlah = count($belumDiklaim);
        $periode = $request->input('redirect_periode', '');

        return redirect()
            ->route('matkul-pengajar.index', $periode ? ['periode' => $periode] : [])
            ->with('success', "{$validated['kode_mata_kuliah']} - {$validated['nama_mata_kuliah']} berhasil diklaim ({$jumlah} kelas)!");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // REFRESH CACHE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Force refresh cache — hapus cache SEVIMA ID + kelas, redirect kembali.
     */
    public function refresh(Request $request)
    {
        $nip     = Auth::user()->userid;
        $periode = $request->get('periode', '');

        $siakadId = $this->dosenService->resolveSiakadId($nip);

        $this->dosenService->clearIdCache($nip);
        if ($siakadId) {
            $this->dosenService->clearKelasCache($siakadId, $periode);
        }

        return redirect()
            ->route('matkul-pengajar.index', $periode ? ['periode' => $periode] : [])
            ->with('success', 'Data kelas berhasil diperbarui dari SIAKAD.');
    }
}
