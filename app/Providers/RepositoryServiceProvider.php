<?php

namespace App\Providers;

use App\Contracts\SiakadApiServiceInterface;
use App\Repositories\Contracts\KelasRepositoryInterface;
use App\Repositories\KelasRepository;
use App\Services\SiakadApiService;
use Illuminate\Support\ServiceProvider;

/**
 * RepositoryServiceProvider
 *
 * Mendaftarkan binding Interface → Implementasi ke Laravel DI Container.
 * Dengan ini, hanya perlu ganti implementasi di sini untuk switch
 * antara real API, mock, atau versi API berbeda.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind Interface API Service → Implementasi Konkret
        // Untuk testing: ganti SiakadApiService dengan MockSiakadApiService
        $this->app->bind(
            SiakadApiServiceInterface::class,
            SiakadApiService::class
        );

        // Bind Interface Repository → Implementasi Konkret
        $this->app->bind(
            KelasRepositoryInterface::class,
            KelasRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
