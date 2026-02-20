<?php

namespace App\Console\Commands;

use App\Contracts\SiakadApiServiceInterface;
use App\Exceptions\SiakadApiException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestSiakadApi extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'siakad:test
                            {--url=         : URL lengkap untuk ditest (opsional, override config)}
                            {--nidn=2304    : NIDN/ID dosen (default: 2304)}
                            {--app-key=     : X-App-Key (opsional, override .env)}
                            {--secret-key=  : X-Secret-Key (opsional, override .env)}
                            {--raw          : Tampilkan raw JSON response tanpa format}';

    /**
     * The console command description.
     */
    protected $description = 'Test koneksi ke API SEVIMA (SiakadCloud). Contoh: php artisan siakad:test --nidn=2304';

    public function __construct(private readonly SiakadApiServiceInterface $apiService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->line('┌─────────────────────────────────────────┐');
        $this->line('│      <fg=cyan>SIAKAD API Connection Tester</fg=cyan>         │');
        $this->line('└─────────────────────────────────────────┘');
        $this->newLine();

        // Tampilkan konfigurasi aktif
        $baseUrl   = config('siakad.base_url');
        $appKey    = $this->option('app-key')    ?: config('siakad.app_key');
        $secretKey = $this->option('secret-key') ?: config('siakad.secret_key');
        $nidn      = $this->option('nidn');
        $customUrl = $this->option('url');

        $this->table(
            ['Setting', 'Value'],
            [
                ['Base URL',    $baseUrl],
                ['X-App-Key',   $appKey    ? substr($appKey, 0, 8)    . '...(hidden)' : '<fg=red>TIDAK DISET</fg=red>'],
                ['X-Secret-Key',$secretKey ? substr($secretKey, 0, 8) . '...(hidden)' : '<fg=red>TIDAK DISET</fg=red>'],
                ['NIDN Test',   $nidn],
            ]
        );

        $this->newLine();

        // ─── TEST 1: Endpoint Kelas Dosen ────────────────────────────────────
        $targetUrl = $customUrl ?: "{$baseUrl}/dosen/{$nidn}/kelas";

        $this->line("▶  Testing endpoint: <fg=yellow>{$targetUrl}</fg=yellow>");
        $this->line('   Method: <fg=cyan>GET</fg=cyan>');
        $this->newLine();

        $startTime = microtime(true);

        try {
            // Buat request manual (dengan token dari option atau .env)
            $httpClient = Http::timeout(config('siakad.timeout', 30))
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                    'X-App-Key'    => $appKey,
                    'X-Secret-Key' => $secretKey,
                ]);

            $response  = $httpClient->get($targetUrl);
            $elapsed   = round((microtime(true) - $startTime) * 1000, 2);
            $status    = $response->status();
            $body      = $response->json() ?? [];

            // Status indicator
            if ($response->successful()) {
                $this->line("   Status: <fg=green>✓ {$status} OK</fg=green>  ({$elapsed}ms)");
            } else {
                $this->line("   Status: <fg=red>✗ {$status}</fg=red>  ({$elapsed}ms)");
            }

            $this->newLine();

            if ($this->option('raw')) {
                // Tampilkan raw JSON
                $this->line('<fg=gray>Raw Response:</fg=gray>');
                $this->line(json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } else {
                // Tampilkan ringkasan terformat
                $this->formatResponse($status, $body, $response->successful());
            }

            if (!$response->successful()) {
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $elapsed = round((microtime(true) - $startTime) * 1000, 2);
            $this->newLine();
            $this->line("   <fg=red>✗ GAGAL ({$elapsed}ms): {$e->getMessage()}</fg=red>");
            $this->newLine();
            $this->error('Cek koneksi internet atau pastikan token API sudah benar.');
            return Command::FAILURE;
        }

        // ─── TEST 2: Endpoint via Service (menggunakan config) ───────────────
        $this->newLine();
        $this->line('─────────────────────────────────────────────');
        $this->line('▶  Testing via <fg=cyan>SiakadApiService</fg=cyan> (menggunakan config)...');
        $this->newLine();

        try {
            $data = $this->apiService->getKelasByDosen($nidn);

            $itemCount = count($data);
            $this->line("   <fg=green>✓ Service berhasil. Jumlah data diterima: {$itemCount} item</fg=green>");

            if ($itemCount > 0) {
                $this->line('   <fg=gray>Keys dari item pertama:</fg=gray>');
                $firstItem = $data[0] ?? [];
                foreach (array_keys($firstItem) as $key) {
                    $this->line("   <fg=cyan>   • {$key}</fg=cyan>");
                }
            }

        } catch (SiakadApiException $e) {
            $this->line("   <fg=red>✗ Service error [{$e->getHttpStatusCode()}]: {$e->getMessage()}</fg=red>");
        } catch (\Exception $e) {
            $this->line("   <fg=red>✗ Exception: {$e->getMessage()}</fg=red>");
        }

        $this->newLine();
        $this->line('─────────────────────────────────────────────');
        $this->line('<fg=green>Test selesai.</fg=green> Jika berhasil, update KelasDTO sesuai keys yang tampil di atas.');
        $this->newLine();

        return Command::SUCCESS;
    }

    /**
     * Format tampilan response di terminal.
     */
    private function formatResponse(int $status, array $body, bool $success): void
    {
        if (!$success) {
            $this->line('   <fg=red>Error Response:</fg=red>');
            $message = $body['message'] ?? $body['error'] ?? json_encode($body);
            $this->line("   <fg=red>{$message}</fg=red>");
            return;
        }

        // Deteksi apakah data ada di dalam key 'data'
        $items = $body['data'] ?? $body;

        if (!is_array($items)) {
            $this->line('   Response bukan array. Raw:');
            $this->line('   ' . json_encode($body, JSON_PRETTY_PRINT));
            return;
        }

        // Jika array of objects (list kelas)
        $isIndexed = array_is_list($items);

        if ($isIndexed && count($items) > 0) {
            $this->line("   <fg=green>✓ Data diterima: " . count($items) . " item</fg=green>");
            $this->newLine();

            // Tampilkan 3 item pertama sebagai tabel
            $firstItem = $items[0];
            $headers   = array_keys($firstItem);
            $rows      = array_slice(
                array_map(fn ($item) => array_values(array_map(
                    fn ($v) => is_array($v) ? '[array]' : (strlen((string)$v) > 40 ? substr($v, 0, 40) . '...' : $v),
                    $item
                )), $items),
                0, 5
            );

            $this->line('   <fg=gray>Preview (5 data pertama):</fg=gray>');
            $this->table($headers, $rows);

        } elseif (!$isIndexed) {
            // Response adalah object tunggal
            $this->line('   <fg=green>✓ Response object:</fg=green>');
            foreach ($body as $key => $value) {
                $val = is_array($value) ? '[...array]' : $value;
                $this->line("   <fg=cyan>{$key}</fg=cyan>: {$val}");
            }
        } else {
            $this->line('   <fg=yellow>Response data kosong/array kosong.</fg=yellow>');
        }
    }
}
