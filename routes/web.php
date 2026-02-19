<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    // Master Data Routes
    Route::resource('unsur-penilaian', \App\Http\Controllers\UnsurPenilaianController::class);
});
