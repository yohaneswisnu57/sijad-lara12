<?php

namespace App\Repositories\Contracts;

use App\DTOs\KelasDTO;
use Illuminate\Support\Collection;

/**
 * Kontrak Repository untuk data Kelas.
 */
interface KelasRepositoryInterface
{
    /**
     * Dapatkan semua kelas mengajar seorang dosen pada semester tertentu.
     *
     * @param  string  $nip       NIP / userid dosen
     * @param  string  $semester  Kode semester (kosong = semester aktif)
     * @return Collection<KelasDTO>
     */
    public function getByDosen(string $nip, string $semester = ''): Collection;

    /**
     * Dapatkan detail satu kelas berdasarkan ID.
     *
     * @param  string  $kelasId
     * @return KelasDTO|null
     */
    public function findById(string $kelasId): ?KelasDTO;

    /**
     * Kosongkan cache kelas dosen (trigger re-fetch dari API).
     *
     * @param  string  $nip
     * @param  string  $semester
     */
    public function clearCache(string $nip, string $semester = ''): void;
}
