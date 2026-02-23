<?php

namespace App\DTOs;

/**
 * Data Transfer Object untuk data Kelas dari API SEVIMA (SiakadCloud v1).
 *
 * Struktur response API (verified dari live test):
 * {
 *   "meta": { "total": 111, "current_page": 1, "per_page": 100, "last_page": 2 },
 *   "urls": { "self": "...", "next": "...", "last": "..." },
 *   "data": [
 *     {
 *       "type": "kelas",
 *       "url": "https://api.sevimaplatform.com/siakadcloud/v1/kelas/108512",
 *       "id": 108512,
 *       "attributes": {
 *         "id_periode":        "20212",
 *         "id_kurikulum":      "2019",
 *         "kode_mata_kuliah":  "BIO225",
 *         "mata_kuliah":       "Biologi Sel dan Molekuler",
 *         "sks_mata_kuliah":   "3",
 *         "id_program_studi":  "62030",
 *         "program_studi":     "Biologi (Kampus Kota Madiun)",
 *         "id_jenjang":        "S1",
 *         "nama_kelas":        "S",
 *         "daya_tampung":      "40",
 *         "is_mbkm":           "0",
 *         "is_deleted":        "0",
 *         "created_at":        "2023-12-08 14:03:46.118356"
 *       }
 *     }
 *   ]
 * }
 */
class KelasDTO
{
    public function __construct(
        public readonly int    $id,              // id dari root object
        public readonly string $kelasUrl,        // url detail kelas
        public readonly string $kodeMatKul,      // attributes.kode_mata_kuliah
        public readonly string $namaMatKul,      // attributes.mata_kuliah
        public readonly int    $sks,             // attributes.sks_mata_kuliah
        public readonly string $namaKelas,       // attributes.nama_kelas (A, B, C, ...)
        public readonly string $idPeriode,       // attributes.id_periode (ex: "20231")
        public readonly string $idProgramStudi,  // attributes.id_program_studi
        public readonly string $programStudi,    // attributes.program_studi
        public readonly string $jenjang,         // attributes.id_jenjang (S1, S2, ...)
        public readonly string $idKurikulum,     // attributes.id_kurikulum
        public readonly int    $dayaTampung,     // attributes.daya_tampung
        public readonly bool   $isMbkm,         // attributes.is_mbkm
        public readonly string $periodeLabel,    // Formatted: "2023/2024 Gasal"
        public readonly array  $rawAttributes = [],
    ) {}

    /**
     * Factory: Buat KelasDTO dari satu item dalam array 'data' response SEVIMA.
     *
     * Item format:
     * { "type": "kelas", "url": "...", "id": 108512, "attributes": { ... } }
     */
    public static function fromApiResponse(array $item): self
    {
        $attr = $item['attributes'] ?? [];

        $idPeriode = (string) ($attr['id_periode'] ?? '');

        return new self(
            id:             (int)    ($item['id']                ?? 0),
            kelasUrl:       (string) ($item['url']               ?? ''),
            kodeMatKul:     (string) ($attr['kode_mata_kuliah']  ?? ''),
            namaMatKul:     (string) ($attr['mata_kuliah']       ?? ''),
            sks:            (int)    ($attr['sks_mata_kuliah']   ?? 0),
            namaKelas:      (string) ($attr['nama_kelas']        ?? ''),
            idPeriode:      $idPeriode,
            idProgramStudi: (string) ($attr['id_program_studi']  ?? ''),
            programStudi:   (string) ($attr['program_studi']     ?? ''),
            jenjang:        (string) ($attr['id_jenjang']        ?? 'S1'),
            idKurikulum:    (string) ($attr['id_kurikulum']      ?? ''),
            dayaTampung:    (int)    ($attr['daya_tampung']      ?? 0),
            isMbkm:         ($attr['is_mbkm'] ?? '0') === '1',
            periodeLabel:   self::formatPeriode($idPeriode),
            rawAttributes:  $attr,
        );
    }

    /**
     * Format kode periode SEVIMA menjadi label yang mudah dibaca.
     *
     * Contoh: "20231" → "2023/2024 Gasal"
     *          "20232" → "2023/2024 Genap"
     *          "20233" → "2023/2024 Antara"
     */
    public static function formatPeriode(string $idPeriode): string
    {
        if (strlen($idPeriode) !== 5) {
            return $idPeriode;
        }

        $year   = (int) substr($idPeriode, 0, 4);
        $sem    = (int) substr($idPeriode, 4, 1);

        $semLabel = match($sem) {
            1 => 'Gasal',
            2 => 'Genap',
            3 => 'Sisipan',
            default => "Semester {$sem}",
        };

        return "{$year}/" . ($year + 1) . " {$semLabel}";
    }

    /**
     * Label lengkap kelas: "BIO225 - Biologi Sel dan Molekuler (Kelas S)"
     */
    public function labelLengkap(): string
    {
        return "{$this->kodeMatKul} - {$this->namaMatKul} (Kelas {$this->namaKelas})";
    }

    /**
     * Konversi ke array (untuk view/JSON response).
     */
    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'kelas_url'       => $this->kelasUrl,
            'kode_mat_kul'    => $this->kodeMatKul,
            'nama_mat_kul'    => $this->namaMatKul,
            'sks'             => $this->sks,
            'nama_kelas'      => $this->namaKelas,
            'id_periode'      => $this->idPeriode,
            'periode_label'   => $this->periodeLabel,
            'program_studi'   => $this->programStudi,
            'jenjang'         => $this->jenjang,
            'daya_tampung'    => $this->dayaTampung,
            'is_mbkm'         => $this->isMbkm,
            'label_lengkap'   => $this->labelLengkap(),
        ];
    }
}
