<?php

namespace App\Repositories;

use App\Contracts\SiakadApiServiceInterface;
use App\DTOs\KelasDTO;
use App\Exceptions\SiakadApiException;
use App\Repositories\Contracts\KelasRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * KelasRepository
 *
 * Layer Repository untuk data Kelas dari API SEVIMA.
 *
 * Tanggung jawab:
 * - Memanggil SiakadApiService untuk data mentah
 * - Mapping data mentah → KelasDTO (via Collection)
 * - Caching response API agar tidak boros request
 * - Error handling agar controller tetap bersih
 */
class KelasRepository implements KelasRepositoryInterface
{
    private bool   $cacheEnabled;
    private int    $cacheTtl;      // dalam menit
    private string $cachePrefix;

    public function __construct(
        private readonly SiakadApiServiceInterface $apiService
    ) {
        $this->cacheEnabled = config('siakad.cache.enabled', true);
        $this->cacheTtl     = config('siakad.cache.ttl_minutes', 60);
        $this->cachePrefix  = config('siakad.cache.prefix', 'siakad_api_');
    }

    /**
     * {@inheritdoc}
     */
    public function getByDosen(string $nip, string $semester = ''): Collection
    {
        $cacheKey = $this->buildCacheKey('kelas_dosen', $nip, $semester);

        if ($this->cacheEnabled) {
            return Cache::remember(
                $cacheKey,
                now()->addMinutes($this->cacheTtl),
                fn () => $this->fetchKelasByDosen($nip, $semester)
            );
        }

        return $this->fetchKelasByDosen($nip, $semester);
    }

    /**
     * {@inheritdoc}
     */
    public function findById(string $kelasId): ?KelasDTO
    {
        $cacheKey = $this->buildCacheKey('kelas_detail', $kelasId);

        if ($this->cacheEnabled) {
            return Cache::remember(
                $cacheKey,
                now()->addMinutes($this->cacheTtl),
                fn () => $this->fetchDetailKelas($kelasId)
            );
        }

        return $this->fetchDetailKelas($kelasId);
    }

    /**
     * {@inheritdoc}
     */
    public function clearCache(string $nip, string $semester = ''): void
    {
        $cacheKey = $this->buildCacheKey('kelas_dosen', $nip, $semester);
        Cache::forget($cacheKey);

        Log::info('[KelasRepository] Cache cleared', [
            'nip'      => $nip,
            'semester' => $semester,
        ]);
    }

    // =========================================================================
    // Private: Data fetching dan mapping
    // =========================================================================

    /**
     * Fetch data dari API dan map ke Collection<KelasDTO>.
     *
     * Response SEVIMA format:
     * {
     *   "meta": { "total": 111, ... },
     *   "data": [ { "id": ..., "attributes": { ... } } ]
     * }
     *
     * Note: API SEVIMA mengembalikan SEMUA kelas dari semua periode.
     * Jika $semester diisi, kita filter di sisi aplikasi.
     */
    private function fetchKelasByDosen(string $nip, string $semester): Collection
    {
        try {
            $rawData = $this->apiService->getKelasByDosen($nip, $semester);

            // Data ada di key 'data' (sudah di-unwrap oleh handleResponse di service)
            // handleResponse mengembalikan $body['data'] ?? $body
            // SEVIMA: jika body punya key 'data', itu adalah array of items
            $items = $rawData;

            // Jika masih terbungkus (service tidak unwrap seluruhnya)
            if (isset($rawData['data'])) {
                $items = $rawData['data'];
            }

            if (!is_array($items)) {
                return collect();
            }

            $collection = collect($items)
                ->filter(fn ($item) => is_array($item) && isset($item['attributes']))
                ->map(fn (array $item) => KelasDTO::fromApiResponse($item));

            // Filter per periode jika diisi
            if (!empty($semester)) {
                $collection = $collection->filter(
                    fn (KelasDTO $k) => $k->idPeriode === $semester
                );
            }

            return $collection->values();

        } catch (SiakadApiException $e) {
            Log::warning('[KelasRepository] Gagal fetch kelas dosen', [
                'nip'      => $nip,
                'semester' => $semester,
                'error'    => $e->getMessage(),
                'status'   => $e->getHttpStatusCode(),
            ]);

            return collect();
        }
    }

    /**
     * Fetch detail kelas dan map ke KelasDTO.
     * Return null jika tidak ditemukan atau error.
     */
    private function fetchDetailKelas(string $kelasId): ?KelasDTO
    {
        try {
            $rawData = $this->apiService->getDetailKelas($kelasId);

            if (empty($rawData)) {
                return null;
            }

            return KelasDTO::fromApiResponse($rawData);

        } catch (SiakadApiException $e) {
            Log::warning('[KelasRepository] Gagal fetch detail kelas', [
                'kelas_id' => $kelasId,
                'error'    => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Build cache key yang konsisten berdasarkan parameter.
     */
    private function buildCacheKey(string $type, string ...$params): string
    {
        $suffix = implode('_', array_filter($params));
        return $this->cachePrefix . $type . '_' . md5($suffix);
    }
}
