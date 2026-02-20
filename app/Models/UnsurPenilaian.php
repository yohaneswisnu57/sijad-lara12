<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnsurPenilaian extends Model
{
    /**
     * Nama tabel di database.
     */
    protected $table = 'ms_unsur_penilaians';

    /**
     * Kolom yang dapat diisi secara massal.
     */
    protected $fillable = [
        'parent_id',
        'kode_nomor',
        'nama_unsur',
        'is_header',
    ];

    /**
     * Konversi tipe data otomatis.
     */
    protected $casts = [
        'is_header' => 'boolean',
    ];

    /**
     * Mendapatkan unsur induk (parent).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(UnsurPenilaian::class, 'parent_id');
    }

    /**
     * Mendapatkan sub-unsur (children).
     */
    public function children(): HasMany
    {
        return $this->hasMany(UnsurPenilaian::class, 'parent_id')->orderBy('kode_nomor');
    }

    /**
     * Mendapatkan sub-unsur secara rekursif (untuk tree view).
     */
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function kegiatan_dosens()
    {
        return $this->hasMany(KegiatanDosen::class, 'unsur_id');
    }
}
