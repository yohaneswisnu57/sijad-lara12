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
        Schema::create('tr_kegiatan_dosens', function (Blueprint $table) {
            $table->id();
            // Asumsi Anda sudah punya tabel 'users' untuk data dosen
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Relasi ke tabel unsur (pilih ID yang is_header = 0)
            $table->unsignedBigInteger('unsur_id');
            $table->foreign('unsur_id')->references('id')->on('ms_unsur_penilaians')->onDelete('cascade');
            
            // Contoh: "Teknologi Pengolahan Roti"
            $table->text('uraian_kegiatan');
            
            // Contoh: "Gasal 2007/2008"
            $table->string('periode_semester', 50);
            
            // Contoh: "sks", "ijazah"
            $table->string('satuan_hasil', 50)->nullable();
            
            // Volume SKS (misal: 10, 2)
            $table->double('volume')->default(0);
            
            // Bobot satuan (misal: 0.5 atau 0.25 untuk SKS lebih dari 10)
            $table->double('angka_kredit_murni')->default(0);
            
            // Volume * angka_kredit_murni (Hitungan Dosen)
            $table->double('ak_hasil_pengusul')->default(0);
            
            // Hitungan setelah divalidasi Tim Penilai
            $table->double('ak_hasil_penilai')->nullable();
            
            // Path/URL penyimpanan file SK/Bukti fisik
            $table->text('bukti_fisik_url')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tr_kegiatan_dosens');
    }
};
