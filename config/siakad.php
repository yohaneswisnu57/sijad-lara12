<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SIAKAD / SEVIMA API Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk koneksi ke API SEVIMA (Edlink/SIAKAD).
    | Semua nilai sensitif disimpan di .env.
    |
    */

    'base_url' => env('SIAKAD_API_URL', 'https://api.sevimaplatform.com/siakadcloud/v1'),

    // X-App-Key header (dari dashboard SEVIMA)
    'app_key' => env('SIAKAD_APP_KEY', ''),

    // X-Secret-Key header (dari dashboard SEVIMA)
    'secret_key' => env('SIAKAD_SECRET_KEY', ''),

    // Timeout dalam detik
    'timeout' => (int) env('SIAKAD_API_TIMEOUT', 30),

    // Retry otomatis jika request gagal
    'retry_times'  => (int) env('SIAKAD_API_RETRY', 2),
    'retry_sleep'  => (int) env('SIAKAD_API_RETRY_SLEEP', 500), // milliseconds

    /*
    |--------------------------------------------------------------------------
    | Endpoint Mapping
    |--------------------------------------------------------------------------
    | Format path menggunakan :param sebagai placeholder.
    | Contoh: '/dosen/:nidn/kelas' → '/dosen/2304/kelas'
    |
    */
    'endpoints' => [
        'kelas_dosen'     => '/dosen/:nidn/kelas',        // Kelas mengajar dosen (path param)
        'detail_kelas'    => '/kelas/:id',                 // Detail kelas
        'mahasiswa_kelas' => '/kelas/:id/mahasiswa',       // Mahasiswa per kelas
        'semester_aktif'  => '/semester/aktif',            // Semester aktif
        'data_dosen'      => '/dosen/:nidn',               // Info dosen
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    | Data API dapat di-cache untuk mengurangi beban request ke SEVIMA.
    |
    */
    'cache' => [
        'enabled'     => env('SIAKAD_CACHE_ENABLED', true),
        'ttl_minutes' => (int) env('SIAKAD_CACHE_TTL', 60), // 1 jam
        'prefix'      => 'siakad_api_',
    ],

];
