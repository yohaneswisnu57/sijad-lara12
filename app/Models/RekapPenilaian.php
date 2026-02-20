<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RekapPenilaian extends Model
{
    use HasFactory;

    protected $table = 'tr_rekap_penilaians';
    protected $guarded = ['id'];

    protected $casts = [
        'ak_lama'            => 'double',
        'ak_baru_pengusul'   => 'double',
        'ak_baru_penilai'    => 'double',
    ];

    /**
     * Relasi ke Unsur Penilaian.
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
