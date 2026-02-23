<?php

namespace App\Services;

use App\Contracts\SiakadApiServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * DosenSiakadService
 *
 * Menangani resolusi ID numerik SEVIMA dari NIP dosen yang login,
 * lalu mengambil semua kelas yang diampu oleh dosen tersebut.
 *
 * Alur:
 * 1. NIP (userid login) → GET /dosen?f-nip={nip}&f-id_status_aktif=AA
 * 2. Ambil id numerik SEVIMA dari data[0].id
 * 3. Cache ID tersebut (tidak berubah, cache 1 hari)
 * 4. GET /dosen/{siakadId}/kelas → kumpulkan semua halaman
 * 5. Cache hasil kelas (TTL dari config)
 */
class DosenSiakadService
{
    private int    $cacheTtlMinutes;
    private string $cachePrefix;

    public function __construct(
        private readonly SiakadApiServiceInterface $api
    ) {
        $this->cacheTtlMinutes = config('siakad.cache.ttl_minutes', 60);
        $this->cachePrefix     = config('siakad.cache.prefix', 'siakad_api_');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resolve ID numerik internal SEVIMA dari NIP dosen.
     *
     * Hasil cache 24 jam — ID tidak berubah, tidak perlu sering di-fetch.
     * Return null jika dosen tidak ditemukan di SEVIMA.
     */
    public function resolveSiakadId(string $nip): ?int
    {
        $cacheKey = $this->cachePrefix . 'dosen_id_' . md5($nip);

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($nip) {
            return $this->fetchSiakadId($nip);
        });
    }

    /**
     * Ambil semua kelas yang diampu dosen (berdasarkan SEVIMA ID).
     *
     * Jika siakadId null, return collection kosong.
     * Cache TTL sesuai config (default 60 menit).
     *
     * @return \Illuminate\Support\Collection<\App\DTOs\KelasDTO>
     */
    public function getKelas(?int $siakadId, string $periode = ''): \Illuminate\Support\Collection
    {
        if ($siakadId === null) {
            return collect();
        }

        $cacheKey = $this->cachePrefix . 'kelas_by_siakad_' . $siakadId . '_' . md5($periode);

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheTtlMinutes), function () use ($siakadId, $periode) {
            return $this->fetchKelas($siakadId, $periode);
        });
    }

    /**
     * Clear cache SEVIMA ID (force re-resolve).
     */
    public function clearIdCache(string $nip): void
    {
        Cache::forget($this->cachePrefix . 'dosen_id_' . md5($nip));
    }

    /**
     * Clear cache kelas dosen.
     */
    public function clearKelasCache(int $siakadId, string $periode = ''): void
    {
        Cache::forget($this->cachePrefix . 'kelas_by_siakad_' . $siakadId . '_' . md5($periode));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Fetch ID numerik SEVIMA dari API.
     */
    private function fetchSiakadId(string $nip): ?int
    {
        try {
            $response = $this->api->getDosenByNip($nip);
            $data     = $response['data'] ?? [];

            if (empty($data)) {
                Log::warning('[DosenSiakadService] Dosen tidak ditemukan di SEVIMA', ['nip' => $nip]);
                return null;
            }

            // Ambil item pertama
            $first    = $data[0] ?? null;
            $siakadId = $first['id'] ?? null;

            if (!$siakadId) {
                Log::warning('[DosenSiakadService] ID SEVIMA tidak ada di response', [
                    'nip'   => $nip,
                    'first' => $first,
                ]);
                return null;
            }

            Log::info('[DosenSiakadService] Resolved SEVIMA ID', [
                'nip'       => $nip,
                'siakad_id' => $siakadId,
            ]);

            return (int) $siakadId;

        } catch (\Throwable $e) {
            Log::error('[DosenSiakadService] Gagal resolve SEVIMA ID', [
                'nip'   => $nip,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Fetch kelas dari API dan map ke Collection<KelasDTO>.
     * Otomatis handle semua halaman (pagination).
     *
     * @return \Illuminate\Support\Collection<\App\DTOs\KelasDTO>
     */
    private function fetchKelas(int $siakadId, string $periode): \Illuminate\Support\Collection
    {
        try {
            $rawResponse = $this->api->getKelasByDosenId($siakadId);
            $items       = $rawResponse['data'] ?? [];

            if (!is_array($items)) {
                return collect();
            }

            $collection = collect($items)
                ->filter(fn ($item) => is_array($item) && isset($item['attributes']))
                ->map(fn (array $item) => \App\DTOs\KelasDTO::fromApiResponse($item))
                ->values();

            // Filter per periode jika diisi
            if (!empty($periode)) {
                $collection = $collection->filter(
                    fn (\App\DTOs\KelasDTO $k) => $k->idPeriode === $periode
                )->values();
            }

            return $collection;

        } catch (\Throwable $e) {
            Log::error('[DosenSiakadService] Gagal fetch kelas', [
                'siakad_id' => $siakadId,
                'periode'   => $periode,
                'error'     => $e->getMessage(),
            ]);
            return collect();
        }
    }
}
