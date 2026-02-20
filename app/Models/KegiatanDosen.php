<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KegiatanDosen extends Model
{
    use HasFactory;

    protected $table = 'tr_kegiatan_dosens';
    protected $guarded = ['id'];

    protected $casts = [
        'volume'              => 'double',
        'angka_kredit_murni'  => 'double',
        'ak_hasil_pengusul'   => 'double',
        'ak_hasil_penilai'    => 'double',
    ];

    /**
     * Relasi ke Unsur Penilaian (detail/leaf node).
     */
    public function unsur()
    {
        return $this->belongsTo(UnsurPenilaian::class, 'unsur_id');
    }

    /**
     * Relasi ke Dosen (via model User, primary key = userid).
     */
    public function dosen()
    {
        return $this->belongsTo(User::class, 'user_id', 'userid');
    }
}
