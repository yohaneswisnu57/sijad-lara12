<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UnsurPenilaianController;
use App\Http\Controllers\KegiatanDosenController;
use App\Http\Controllers\KelasMengajarController;
use App\Http\Controllers\MatkulPengajarController;

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    // Master Data Routes
    Route::get('unsur-penilaian/tree', [UnsurPenilaianController::class, 'tree'])->name('unsur-penilaian.tree');
    Route::resource('unsur-penilaian', UnsurPenilaianController::class);

    // Transaksi Routes
    Route::resource('kegiatan-dosen', KegiatanDosenController::class);

    // Kelas Mengajar Routes
    Route::get('kelas-mengajar', [KelasMengajarController::class, 'index'])->name('kelas-mengajar.index');
    Route::post('kelas-mengajar/klaim', [KelasMengajarController::class, 'klaim'])->name('kelas-mengajar.klaim');
    Route::get('kelas-mengajar/tambah-manual', [KelasMengajarController::class, 'create'])->name('kelas-mengajar.create');
    Route::post('kelas-mengajar/tambah-manual', [KelasMengajarController::class, 'store'])->name('kelas-mengajar.store');
    Route::get('kelas-mengajar/{kelasMengajar}/sk', [KelasMengajarController::class, 'downloadSK'])->name('kelas-mengajar.sk');
    Route::delete('kelas-mengajar/{kelasMengajar}', [KelasMengajarController::class, 'destroy'])->name('kelas-mengajar.destroy');

    // Mata Kuliah Pengajar (read-only dari SIAKAD)
    Route::get('matkul-pengajar', [MatkulPengajarController::class, 'index'])->name('matkul-pengajar.index');
    Route::post('matkul-pengajar/klaim', [MatkulPengajarController::class, 'klaim'])->name('matkul-pengajar.klaim');
    Route::post('matkul-pengajar/refresh', [MatkulPengajarController::class, 'refresh'])->name('matkul-pengajar.refresh');
});
