<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KegiatanDosen extends Model
{
    use HasFactory;

    protected $table = 'tr_kegiatan_dosens';
    protected $guarded = ['id'];
    // Relasi balik ke Master Unsur
    public function unsur()
    {
        return $this->belongsTo(UnsurPenilaian::class, 'unsur_id');
    }

    // Relasi ke Dosen (Asumsi pakai model User bawaan Laravel)
    public function dosen()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
