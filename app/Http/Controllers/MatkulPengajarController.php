<?php

namespace App\Http\Controllers;

use App\DTOs\KelasDTO;
use App\Services\DosenSiakadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MatkulPengajarController extends Controller
{
    public function __construct(
        private readonly DosenSiakadService $dosenService
    ) {}

    /**
     * Halaman daftar mata kuliah yang diampu dosen yang sedang login.
     *
     * Alur:
     * 1. Ambil NIP dari Auth user
     * 2. Resolve SEVIMA ID (cache 24 jam)
     * 3. Fetch semua kelas (cache 60 menit)
     * 4. Grouping per periode untuk tampilan
     */
    public function index(Request $request)
    {
        $user    = Auth::user();
        $nip     = $user->userid;
        $periode = $request->get('periode', '');

        // ── Resolve SEVIMA ID dari NIP ──────────────────────────────────────
        $siakadId  = $this->dosenService->resolveSiakadId($nip);
        $dosenError = null;

        if ($siakadId === null) {
            $dosenError = "Data dosen dengan NIP {$nip} tidak ditemukan di SIAKAD. "
                        . "Pastikan NIP terdaftar dan berstatus aktif.";
        }

        // ── Ambil kelas ─────────────────────────────────────────────────────
        $kelasList = collect();
        $apiError  = null;

        if ($siakadId !== null) {
            try {
                $kelasList = $this->dosenService->getKelas($siakadId, $periode);
            } catch (\Exception $e) {
                $apiError = 'Gagal memuat data kelas: ' . $e->getMessage();
            }
        }

        // ── Grouping per periode (untuk accordion / tab) ────────────────────
        $grouped = $kelasList
            ->sortByDesc('idPeriode')
            ->groupBy(fn (KelasDTO $k) => $k->idPeriode);

        // ── Daftar periode unik dari data API (untuk dropdown filter) ────────
        $periodeFromApi = $kelasList
            ->pluck('idPeriode')
            ->unique()
            ->sortDesc()
            ->mapWithKeys(fn (string $kode) => [
                $kode => KelasDTO::formatPeriode($kode),
            ])
            ->toArray();

        // Statistik ringkasan
        $totalKelas = $kelasList->count();
        $totalSks   = $kelasList->sum('sks');

        return view('pages.matkul-pengajar.index', compact(
            'kelasList',
            'grouped',
            'periodeFromApi',
            'periode',
            'siakadId',
            'nip',
            'totalKelas',
            'totalSks',
            'dosenError',
            'apiError',
        ));
    }

    /**
     * Force refresh cache — hapus cache SEVIMA ID + kelas, lalu redirect kembali.
     */
    public function refresh(Request $request)
    {
        $nip     = Auth::user()->userid;
        $periode = $request->get('periode', '');

        $siakadId = $this->dosenService->resolveSiakadId($nip);

        // Clear caches
        $this->dosenService->clearIdCache($nip);
        if ($siakadId) {
            $this->dosenService->clearKelasCache($siakadId, $periode);
        }

        return redirect()->route('matkul-pengajar.index', $periode ? ['periode' => $periode] : [])
            ->with('success', 'Data kelas berhasil diperbarui dari SIAKAD.');
    }
}
