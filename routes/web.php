<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UnsurPenilaianController;

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    // Master Data Routes
    Route::get('unsur-penilaian/tree', [UnsurPenilaianController::class, 'tree'])->name('unsur-penilaian.tree');
    Route::resource('unsur-penilaian', UnsurPenilaianController::class);
});
