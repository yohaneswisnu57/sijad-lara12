<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\DTOs\KelasDTO;

class KelasMengajar extends Model
{
    use HasFactory;
    
      
    protected $table = 'tr_kelas_mengajars';
    protected $guarded = ['id'];

    protected $casts = [
        'id_pegawai_siakad' => 'integer',
        'sks'              => 'integer',
        'sks_pengusul'     => 'integer',
        'daya_tampung'     => 'integer',
        'is_mbkm'          => 'boolean',
        'diklaim_at'       => 'datetime',
    ];

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePeriode($query, string $idPeriode)
    {
        return $query->where('id_periode', $idPeriode);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDariSiakad($query)
    {
        return $query->where('source', 'siakad');
    }

    public function scopeManual($query)
    {
        return $query->where('source', 'manual');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Apakah kelas ini berasal dari klaim SIAKAD?
     */
    public function isDariSiakad(): bool
    {
        return $this->source === 'siakad';
    }

    /**
     * Label badge status untuk tampilan UI.
     */
    public function statusBadge(): array
    {
        return match($this->status) {
            'aktif'   => ['label' => 'Aktif',   'class' => 'badge-success'],
            'pending' => ['label' => 'Pending', 'class' => 'badge-warning'],
            'ditolak' => ['label' => 'Ditolak', 'class' => 'badge-danger'],
            default   => ['label' => 'Unknown', 'class' => 'badge-secondary'],
        };
    }

    /**
     * URL download SK Mengajar melalui controller (private storage).
     */
    public function hasSK(): bool
    {
        return !empty($this->sk_mengajar_path);
    }

    // ── Factory: Buat dari KelasDTO (klaim dari SIAKAD) ──────────────────────

    /**
     * Buat instance KelasMengajar dari KelasDTO (siap di-save).
     */
    public static function fromKelasDTO(KelasDTO $dto, string $userId): self
    {
        return new self([
            'user_id'              => $userId,
            'kelas_id_siakad'      => (string) $dto->id,
            'kode_mata_kuliah'     => $dto->kodeMatKul,
            'nama_mata_kuliah'     => $dto->namaMatKul,
            'nama_kelas'           => $dto->namaKelas,
            'sks'                  => $dto->sks,
            'id_periode'           => $dto->idPeriode,
            'periode_label'        => $dto->periodeLabel,
            'id_program_studi'     => $dto->idProgramStudi,
            'program_studi'        => $dto->programStudi,
            'jenjang'              => $dto->jenjang,
            'id_kurikulum'         => $dto->idKurikulum,
            'daya_tampung'         => $dto->dayaTampung,
            'is_mbkm'              => $dto->isMbkm,
            'source'               => 'siakad',
            'status'               => 'aktif',    // langsung aktif tanpa approval
            'diklaim_at'           => now(),
        ]);
    }

    // ── Relasi ───────────────────────────────────────────────────────────────

    public function dosen()
    {
        return $this->belongsTo(User::class, 'user_id', 'userid');
    }
}
