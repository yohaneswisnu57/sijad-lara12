<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Masalah asal: user_id dibuat sebagai foreignId (bigint unsigned)
     * yang mereferensi tabel 'users' di MySQL. Namun autentikasi aplikasi
     * menggunakan sc_user di PostgreSQL dengan primary key 'userid' bertipe string
     * (contoh: '003130771'). FK constraint ini menyebabkan insert gagal.
     *
     * Solusi: Hapus FK constraint, ubah kolom menjadi varchar(50).
     */
    public function up(): void
    {
        // ---- Tabel tr_kegiatan_dosens ----
        Schema::table('tr_kegiatan_dosens', function (Blueprint $table) {
            // 1. Hapus foreign key constraint dulu
            $table->dropForeign(['user_id']);
        });

        Schema::table('tr_kegiatan_dosens', function (Blueprint $table) {
            // 2. Ubah tipe kolom dari bigint unsigned → varchar(50)
            $table->string('user_id', 50)->change();
        });

        // ---- Tabel tr_rekap_penilaians ----
        Schema::table('tr_rekap_penilaians', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('tr_rekap_penilaians', function (Blueprint $table) {
            $table->string('user_id', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu rollback karena tabel lama tidak kompatibel
    }
};
