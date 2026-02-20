<?php

namespace App\Contracts;

/**
 * Interface SiakadApiServiceInterface
 *
 * Kontrak untuk semua operasi HTTP ke API SEVIMA/SIAKAD.
 * Implementasi lain (mock, testing) cukup implement interface ini.
 */
interface SiakadApiServiceInterface
{
    /**
     * Melakukan HTTP GET ke endpoint API.
     *
     * @param  string  $endpoint  Alias endpoint dari config('siakad.endpoints')
     *                            atau path langsung (misal: '/jadwal/dosen')
     * @param  array   $params    Query string parameters
     * @return array              Response data dari API
     *
     * @throws \App\Exceptions\SiakadApiException
     */
    public function get(string $endpoint, array $params = []): array;

    /**
     * Melakukan HTTP POST ke endpoint API.
     *
     * @param  string  $endpoint
     * @param  array   $data      Request body
     * @return array
     *
     * @throws \App\Exceptions\SiakadApiException
     */
    public function post(string $endpoint, array $data = []): array;

    /**
     * Mengambil daftar kelas/jadwal mengajar seorang dosen.
     * Menggunakan path param: /dosen/:nidn/kelas
     *
     * @param  string  $nip       NIP/userid dosen (SEVIMA internal ID)
     * @param  string  $semester  Kode periode (misal: '20251'), kosong = semua
     * @return array
     */
    public function getKelasByDosen(string $nip, string $semester = ''): array;

    /**
     * Mengambil daftar kelas mengajar berdasarkan NIP dosen.
     * Menggunakan query filter: /kelas?f-inip=NIP&f-id_periode=PERIODE
     *
     * @param  string  $nip      NIP dosen (userid)
     * @param  string  $periode  Kode periode (misal: '20251'), kosong = semua
     * @return array
     */
    public function getKelasByNip(string $nip, string $periode = ''): array;

    /**
     * Mengambil detail satu kelas.
     *
     * @param  string  $kelasId
     * @return array
     */
    public function getDetailKelas(string $kelasId): array;

    /**
     * Mengambil semester yang sedang aktif.
     *
     * @return array
     */
    public function getSemesterAktif(): array;
}
