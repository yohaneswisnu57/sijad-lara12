<?php

namespace App\Services;

use App\Contracts\SiakadApiServiceInterface;
use App\Exceptions\SiakadApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SiakadApiService
 *
 * Implementasi konkret untuk komunikasi dengan API SEVIMA / SiakadCloud.
 * Base URL: https://api.sevimaplatform.com/siakadcloud/v1
 *
 * Fitur:
 * - Bearer token authentication
 * - Path parameter substitution (:nidn, :id, dll)
 * - Retry otomatis
 * - Logging request/response
 * - Mapping endpoint alias dari config
 * - Error handling terstandar via SiakadApiException
 */
class SiakadApiService implements SiakadApiServiceInterface
{
    private string $baseUrl;
    private string $appKey;
    private string $secretKey;
    private int    $timeout;
    private int    $retryTimes;
    private int    $retrySleep;
    private array  $endpoints;

    public function __construct()
    {
        $this->baseUrl    = rtrim(config('siakad.base_url', ''), '/');
        $this->appKey     = config('siakad.app_key', '');
        $this->secretKey  = config('siakad.secret_key', '');
        $this->timeout    = config('siakad.timeout', 30);
        $this->retryTimes = config('siakad.retry_times', 2);
        $this->retrySleep = config('siakad.retry_sleep', 500);
        $this->endpoints  = config('siakad.endpoints', []);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $endpoint, array $params = []): array
    {
        $url = $this->resolveUrl($endpoint, $params);

        // Hapus path params dari query string (sudah dipakai di URL)
        $queryParams = $this->filterQueryParams($endpoint, $params);

        Log::debug('[SIAKAD API] GET', ['url' => $url, 'query' => $queryParams]);

        try {
            $response = $this->buildHttpClient()
                ->retry($this->retryTimes, $this->retrySleep)
                ->get($url, $queryParams);

            return $this->handleResponse($response, 'GET', $url);

        } catch (ConnectionException $e) {
            Log::error('[SIAKAD API] Connection error', ['url' => $url, 'msg' => $e->getMessage()]);
            throw SiakadApiException::connectionError($e->getMessage(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function post(string $endpoint, array $data = []): array
    {
        $url = $this->resolveUrl($endpoint, $data);

        Log::debug('[SIAKAD API] POST', ['url' => $url]);

        try {
            $response = $this->buildHttpClient()
                ->retry($this->retryTimes, $this->retrySleep)
                ->post($url, $data);

            return $this->handleResponse($response, 'POST', $url);

        } catch (ConnectionException $e) {
            Log::error('[SIAKAD API] Connection error', ['url' => $url, 'msg' => $e->getMessage()]);
            throw SiakadApiException::connectionError($e->getMessage(), $e);
        }
    }

    /**
     * {@inheritdoc}
     * Contoh URL: /dosen/2304/kelas
     */
    public function getKelasByDosen(string $nip, string $semester = ''): array
    {
        $params = ['nidn' => $nip];

        if (!empty($semester)) {
            $params['semester'] = $semester;
        }

        return $this->get('kelas_dosen', $params);
    }

    /**
     * {@inheritdoc}
     * Contoh URL: /kelas/123
     */
    public function getDetailKelas(string $kelasId): array
    {
        return $this->get('detail_kelas', ['id' => $kelasId]);
    }

    /**
     * {@inheritdoc}
     */
    public function getSemesterAktif(): array
    {
        return $this->get('semester_aktif');
    }

    // =========================================================================
    // Private Helpers
    // =========================================================================

    /**
     * Build HTTP client dengan header autentikasi SEVIMA:
     * X-App-Key dan X-Secret-Key.
     */
    private function buildHttpClient()
    {
        return Http::timeout($this->timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
                'X-App-Key'    => $this->appKey,
                'X-Secret-Key' => $this->secretKey,
            ]);
    }

    /**
     * Resolusi endpoint ke URL lengkap.
     *
     * Mendukung dua format:
     * 1. Alias + path params:  'kelas_dosen' + ['nidn' => '2304']
     *    → https://api.sevimaplatform.com/siakadcloud/v1/dosen/2304/kelas
     *
     * 2. Absolute URL:         'https://api.sevimaplatform.com/...'
     *    → digunakan langsung
     *
     * 3. Raw path:             '/custom/endpoint'
     *    → base_url + path
     */
    private function resolveUrl(string $endpoint, array $params = []): string
    {
        // Jika sudah URL lengkap, gunakan langsung
        if (str_starts_with($endpoint, 'http')) {
            return $endpoint;
        }

        // Ambil path dari alias atau gunakan langsung sebagai path
        $path = $this->endpoints[$endpoint] ?? $endpoint;

        // Substitusi path parameters (:nidn, :id, dll)
        foreach ($params as $key => $value) {
            $path = str_replace(":{$key}", $value, $path);
        }

        return $this->baseUrl . '/' . ltrim($path, '/');
    }

    /**
     * Filter params yang sudah dipakai sebagai path parameter
     * agar tidak ikut ter-append sebagai query string.
     *
     * Contoh: endpoint '/dosen/:nidn/kelas', params ['nidn' => '2304', 'semester' => '20241']
     * → hanya ['semester' => '20241'] yang jadi query string
     */
    private function filterQueryParams(string $endpoint, array $params): array
    {
        $path = $this->endpoints[$endpoint] ?? $endpoint;

        // Cari semua placeholder (:nidn, :id, dll)
        preg_match_all('/:(\w+)/', $path, $matches);
        $pathParamKeys = $matches[1] ?? [];

        return array_diff_key($params, array_flip($pathParamKeys));
    }

    /**
     * Handle response HTTP: throw exception jika non-2xx.
     *
     * @throws SiakadApiException
     */
    private function handleResponse(Response $response, string $method, string $url): array
    {
        $status = $response->status();
        $body   = $response->json() ?? [];

        Log::debug("[SIAKAD API] {$method} Response {$status}", ['url' => $url]);

        if ($response->successful()) {
            // Return full body — repository bertanggung jawab mengekstrak 'data'
            // SEVIMA response: { "meta": {...}, "urls": {...}, "data": [...] }
            return $body;
        }

        Log::error("[SIAKAD API] {$method} Failed {$status}", [
            'url'  => $url,
            'body' => $body,
        ]);

        throw SiakadApiException::fromHttpResponse($status, $body);
    }
}
