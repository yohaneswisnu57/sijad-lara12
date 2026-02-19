<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UnsurPenilaianController;

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    // Master Data Routes
    Route::resource('unsur-penilaian', UnsurPenilaianController::class);
});
