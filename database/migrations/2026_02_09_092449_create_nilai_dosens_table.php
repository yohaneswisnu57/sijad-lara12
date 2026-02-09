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
        Schema::create('tr_nilai_dosens', function (Blueprint $table) {
            $table->id();

            // ID Dosen (Diasumsikan merujuk ke tabel users/dosen)
            // Jika Anda punya tabel 'users', ganti baris ini dengan:
            // $table->foreignId('dosen_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('dosen_id');

            // Relasi ke tabel unsur_penilaians
            $table->foreignId('unsur_id')
                  ->constrained('ms_unsur_penilaians')
                  ->cascadeOnDelete();

            $table->decimal('nilai_kredit', 10, 2)->nullable();
            $table->text('keterangan')->nullable();

            // Menggunakan timestamp() bawaan Laravel untuk created_at & updated_at
            // atau ->timestamp('created_at')->useCurrent() jika ingin persis SQL manual
            $table->timestamps();

            // Mencegah duplikasi: 1 Dosen hanya boleh punya 1 nilai per unsur
            $table->unique(['dosen_id', 'unsur_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_dosens');
    }
};
