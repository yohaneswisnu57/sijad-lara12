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
        Schema::create('ms_unsur_penilaians', function (Blueprint $table) {
            $table->id();
            // Self-Referencing Foreign Key (Relasi ke tabel ini sendiri)
            // Kita sebutkan nama tabelnya secara eksplisit 'unsur_penilaians'
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('ms_unsur_penilaians')
                  ->cascadeOnDelete();
            $table->string('kode_nomor', 10)->nullable(); // Contoh: "I", "A", "1"
            $table->text('nama_unsur');
            $table->boolean('is_header')->default(true);
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unsur_penilaians');
    }
};
