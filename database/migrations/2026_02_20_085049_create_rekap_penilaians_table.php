<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tr_rekap_penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Relasi ke unsur (bisa header maupun sub-unsur untuk grouping)
            $table->unsignedBigInteger('unsur_id');
            $table->foreign('unsur_id')->references('id')->on('ms_unsur_penilaians')->onDelete('cascade');
            
            // Penanda tahun/periode pengajuan DUPAK (misal: "2023-1")
            $table->string('periode_pengajuan', 20);
            
            // Nilai AK dari SK lama
            $table->double('ak_lama')->default(0);
            
            // Total penjumlahan kegiatan baru (versi dosen pengusul)
            $table->double('ak_baru_pengusul')->default(0);
            
            // Total penjumlahan kegiatan baru (versi tim penilai)
            $table->double('ak_baru_penilai')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tr_rekap_penilaians');
    }
};
