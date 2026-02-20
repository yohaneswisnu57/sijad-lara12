<?php

namespace App\Http\Controllers;

use App\Models\KegiatanDosen;
use App\Models\UnsurPenilaian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KegiatanDosenController extends Controller
{
    /**
     * Menampilkan daftar kegiatan milik dosen yang sedang login,
     * dikelompokkan per Unsur Induk (Root).
     */
    public function index()
    {
        $kegiatanList = KegiatanDosen::with(['unsur.parent'])
            ->where('user_id', Auth::user()->userid)
            ->orderBy('created_at', 'desc')
            ->get();

        // Kelompokkan berdasarkan unsur root (induk paling atas)
        $grouped = $kegiatanList->groupBy(function ($item) {
            // Telusuri ke atas hingga menemukan root
            $unsur = $item->unsur;
            while ($unsur && $unsur->parent_id !== null) {
                $unsur = $unsur->parent;
            }
            return $unsur ? $unsur->nama_unsur : 'Lainnya';
        });

        return view('pages.kegiatan-dosen.index', compact('grouped'));
    }

    /**
     * Menampilkan form tambah kegiatan baru.
     * Struktur unsur ditampilkan sebagai hierarki untuk memilih sub-unsur.
     */
    public function create()
    {
        // Ambil semua unsur root beserta children rekursif
        $rootUnsurs = UnsurPenilaian::whereNull('parent_id')
            ->orderBy('kode_nomor')
            ->with('childrenRecursive')
            ->get();

        // Hanya unsur DETAIL (is_header = false) yang bisa dipilih
        $detailUnsurs = UnsurPenilaian::where('is_header', false)
            ->orderBy('kode_nomor')
            ->get();

        return view('pages.kegiatan-dosen.create', compact('rootUnsurs', 'detailUnsurs'));
    }

    /**
     * Simpan kegiatan baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unsur_id'          => 'required|exists:ms_unsur_penilaians,id',
            'uraian_kegiatan'   => 'required|string',
            'periode_semester'  => 'required|string|max:50',
            'satuan_hasil'      => 'nullable|string|max:50',
            'volume'            => 'required|numeric|min:0',
            'angka_kredit_murni'=> 'required|numeric|min:0',
        ]);

        // Hitung otomatis AK Pengusul = volume * angka_kredit_murni
        $validated['ak_hasil_pengusul'] = $validated['volume'] * $validated['angka_kredit_murni'];
        $validated['user_id'] = Auth::user()->userid;

        KegiatanDosen::create($validated);

        return redirect()->route('kegiatan-dosen.index')
            ->with('success', 'Kegiatan berhasil disimpan.');
    }

    /**
     * Tampilkan form edit kegiatan.
     */
    public function edit(KegiatanDosen $kegiatanDosen)
    {
        // Pastikan hanya pemilik yang bisa edit
        if ($kegiatanDosen->user_id !== Auth::user()->userid) {
            abort(403);
        }

        $rootUnsurs = UnsurPenilaian::whereNull('parent_id')
            ->orderBy('kode_nomor')
            ->with('childrenRecursive')
            ->get();

        $detailUnsurs = UnsurPenilaian::where('is_header', false)
            ->orderBy('kode_nomor')
            ->get();

        return view('pages.kegiatan-dosen.edit', compact('kegiatanDosen', 'rootUnsurs', 'detailUnsurs'));
    }

    /**
     * Update kegiatan.
     */
    public function update(Request $request, KegiatanDosen $kegiatanDosen)
    {
        if ($kegiatanDosen->user_id !== Auth::user()->userid) {
            abort(403);
        }

        $validated = $request->validate([
            'unsur_id'          => 'required|exists:ms_unsur_penilaians,id',
            'uraian_kegiatan'   => 'required|string',
            'periode_semester'  => 'required|string|max:50',
            'satuan_hasil'      => 'nullable|string|max:50',
            'volume'            => 'required|numeric|min:0',
            'angka_kredit_murni'=> 'required|numeric|min:0',
        ]);

        $validated['ak_hasil_pengusul'] = $validated['volume'] * $validated['angka_kredit_murni'];

        $kegiatanDosen->update($validated);

        return redirect()->route('kegiatan-dosen.index')
            ->with('success', 'Kegiatan berhasil diperbarui.');
    }

    /**
     * Hapus kegiatan.
     */
    public function destroy(KegiatanDosen $kegiatanDosen)
    {
        if ($kegiatanDosen->user_id !== Auth::user()->userid) {
            abort(403);
        }

        $kegiatanDosen->delete();

        return redirect()->route('kegiatan-dosen.index')
            ->with('success', 'Kegiatan berhasil dihapus.');
    }
}
