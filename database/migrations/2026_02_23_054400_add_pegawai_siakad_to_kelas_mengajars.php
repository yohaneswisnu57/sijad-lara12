<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom id_pegawai_siakad ke tr_kelas_mengajars.
     *
     * Dua kunci identitas dosen:
     *   user_id          = NIP dari sistem login lokal
     *   id_pegawai_siakad = ID numerik internal SEVIMA (dari /dosen?f-nip=xxx)
     *
     * Sks_pengusul ditambahkan untuk menampung SKS dari SK Mengajar
     * yang mungkin berbeda dari SKS di SIAKAD.
     */
    public function up(): void
    {
        Schema::table('tr_kelas_mengajars', function (Blueprint $table) {
            // ID numerik internal SEVIMA — nullable karena mungkin resolve gagal
            $table->unsignedInteger('id_pegawai_siakad')
                  ->nullable()
                  ->after('user_id')
                  ->comment('ID numerik internal SEVIMA dari /dosen?f-nip=xxx');

            // SKS dari SK Mengajar (bisa berbeda dari sks di SIAKAD)
            $table->unsignedTinyInteger('sks_pengusul')
                  ->nullable()
                  ->after('sks')
                  ->comment('SKS sesuai SK Mengajar (diisi saat klaim, bisa berbeda dari API)');
        });
    }

    public function down(): void
    {
        Schema::table('tr_kelas_mengajars', function (Blueprint $table) {
            $table->dropColumn(['id_pegawai_siakad', 'sks_pengusul']);
        });
    }
};
