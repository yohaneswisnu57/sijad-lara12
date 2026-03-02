<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkPeriode extends Model
{
    protected $table = 'tr_sk_periodes';

    protected $fillable = [
        'user_id',
        'id_periode',
        'periode_label',
        'sk_path',
        'sk_original_name',
        'sk_mime',
        'sk_size',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'sk_size'     => 'integer',
    ];

    // ── Scopes ───────────────────────────────────────────────────────────────

    /**
     * Filter berdasarkan user/NIP.
     */
    public function scopeForUser($query, string $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Ukuran file dalam format human-readable (KB / MB).
     */
    public function getSizeReadableAttribute(): string
    {
        if (!$this->sk_size) return '—';
        if ($this->sk_size >= 1_048_576) {
            return number_format($this->sk_size / 1_048_576, 1) . ' MB';
        }
        return number_format($this->sk_size / 1_024, 1) . ' KB';
    }
}
