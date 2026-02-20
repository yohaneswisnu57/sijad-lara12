<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapPenilaian extends Model
{
    /use HasFactory;

    protected $table = 'tr_rekap_penilaians';
    protected $guarded = ['id'];

    // Relasi balik ke Master Unsur
    public function unsur()
    {
        return $this->belongsTo(UnsurPenilaian::class, 'unsur_id');
    }

    // Relasi ke Dosen
    public function dosen()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
