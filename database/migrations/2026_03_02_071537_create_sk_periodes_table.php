<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel untuk menyimpan SK Mengajar per dosen per periode.
     *
     * Satu dosen dapat meng-upload satu SK untuk satu periode.
     * SK ini kemudian di-reuse untuk semua klaim matkul di periode tersebut.
     * File disimpan di private storage (tidak bisa diakses langsung via URL).
     */
    public function up(): void
    {
        Schema::create('tr_sk_periodes', function (Blueprint $table) {
            $table->id();

            // Identitas pemilik (tanpa FK, mengikuti pola tabel lain)
            $table->string('user_id', 50)->index()->comment('userid / NIP dosen');

            // Periode semester
            $table->string('id_periode', 10)->index()->comment('Kode periode SEVIMA, ex: 20251');
            $table->string('periode_label', 50)->nullable()->comment('Label: 2025/2026 Gasal');

            // Berkas SK
            $table->string('sk_path')->comment('Path relatif di private storage');
            $table->string('sk_original_name')->comment('Nama file asli saat upload');
            $table->string('sk_mime', 100)->nullable()->comment('MIME type file');
            $table->unsignedInteger('sk_size')->nullable()->comment('Ukuran file dalam bytes');

            $table->timestamp('uploaded_at')->nullable()->comment('Waktu upload terakhir');
            $table->timestamps();

            // Satu dosen hanya boleh punya satu SK aktif per periode
            // (INSERT → update jika sudah ada, lewat upsert)
            $table->unique(['user_id', 'id_periode'], 'unique_sk_user_periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr_sk_periodes');
    }
};
