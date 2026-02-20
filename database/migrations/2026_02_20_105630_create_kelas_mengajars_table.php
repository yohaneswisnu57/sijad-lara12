<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel untuk menampung klaim kelas mengajar dosen.
     *
     * Dua sumber data:
     * 1. 'siakad'  → dari API SEVIMA, status langsung 'aktif'
     * 2. 'manual'  → input manual, status 'pending' sampai disetujui admin
     */
    public function up(): void
    {
        Schema::create('tr_kelas_mengajars', function (Blueprint $table) {
            $table->id();

            // Identitas dosen (tanpa FK, mengikuti pola tr_kegiatan_dosens)
            $table->string('user_id', 50)->index()->comment('userid / NIP dosen');

            // ── Data kelas ────────────────────────────────────────────────
            // kelas_id_siakad = id integer dari SEVIMA (nullable untuk manual)
            $table->string('kelas_id_siakad')->nullable()->comment('ID kelas dari SEVIMA API');
            $table->string('kode_mata_kuliah', 50);
            $table->string('nama_mata_kuliah');
            $table->string('nama_kelas', 10)->comment('Kelas A / B / C / ...');
            $table->unsignedTinyInteger('sks')->default(0);

            // ── Periode / Semester ────────────────────────────────────────
            // Format SEVIMA: '20251' = 2025/2026 Gasal, '20252' = 2025/2026 Genap
            $table->string('id_periode', 10)->index()->comment('Kode periode SEVIMA, ex: 20251');
            $table->string('periode_label', 50)->nullable()->comment('Label: 2025/2026 Gasal');

            // ── Info tambahan (dari API) ──────────────────────────────────
            $table->string('id_program_studi', 20)->nullable();
            $table->string('program_studi')->nullable();
            $table->string('jenjang', 10)->nullable()->comment('S1 / S2 / D3 ...');
            $table->string('id_kurikulum', 20)->nullable();
            $table->unsignedSmallInteger('daya_tampung')->nullable();
            $table->boolean('is_mbkm')->default(false);

            // ── Sumber & Status ──────────────────────────────────────────
            $table->enum('source', ['siakad', 'manual'])
                  ->default('siakad')
                  ->comment('siakad = dari API, manual = diinput manual');

            $table->enum('status', ['aktif', 'pending', 'ditolak'])
                  ->default('aktif')
                  ->comment('aktif=approved, pending=menunggu, ditolak=rejected');

            // ── Berkas SK Mengajar ────────────────────────────────────────
            $table->string('sk_mengajar_path')->nullable()->comment('Path file relatif dalam storage/private');
            $table->string('sk_mengajar_original_name')->nullable();
            $table->string('sk_mengajar_mime', 100)->nullable();
            $table->unsignedInteger('sk_mengajar_size')->nullable()->comment('Ukuran file dalam bytes');

            // ── Keterangan ────────────────────────────────────────────────
            $table->text('catatan')->nullable()->comment('Keterangan tambahan (wajib untuk manual)');
            $table->text('catatan_admin')->nullable()->comment('Alasan approve/tolak dari admin');

            $table->timestamp('diklaim_at')->nullable()->comment('Waktu klaim pertama');

            $table->timestamps();

            // Cegah klaim ganda di periode yang sama
            $table->unique(['user_id', 'kelas_id_siakad', 'id_periode'], 'unique_kelas_klaim');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr_kelas_mengajars');
    }
};
