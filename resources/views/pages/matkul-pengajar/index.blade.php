@extends('partials.layouts.app-layout')

@section('title', 'Mata Kuliah Pengajar')

@section('content')

    {{-- ── Page Title ──────────────────────────────────────────────────────── --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-right">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">SIJAD</a></li>
                        <li class="breadcrumb-item active">Mata Kuliah Pengajar</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="fas fa-chalkboard-teacher mr-1"></i> Mata Kuliah Pengajar
                </h4>
            </div>
        </div>
    </div>

    {{-- ── Error ───────────────────────────────────────────────────────────── --}}
    @if($dosenError)
        <div class="alert alert-warning">
            <i class="fas fa-user-times mr-2"></i>
            <strong>Dosen tidak ditemukan di SIAKAD:</strong> {{ $dosenError }}
        </div>
    @endif
    @if($apiError)
        <div class="alert alert-danger">
            <i class="fas fa-plug mr-2"></i>
            <strong>Gagal memuat data:</strong> {{ $apiError }}
        </div>
    @endif

    {{-- ── Statistik ───────────────────────────────────────────────────────── --}}
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm text-center py-3">
                <div class="text-muted small mb-1">Mata Kuliah</div>
                <div class="h2 font-weight-bold text-primary mb-0">{{ $totalMatkul }}</div>
                <div class="text-muted small">mata kuliah unik</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center py-3">
                <div class="text-muted small mb-1">Total Kelas</div>
                <div class="h2 font-weight-bold text-info mb-0">{{ $totalKelas }}</div>
                <div class="text-muted small">kelas (semua semester)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center py-3">
                <div class="text-muted small mb-1">Total SKS</div>
                <div class="h2 font-weight-bold text-success mb-0">{{ $totalSks }}</div>
                <div class="text-muted small">SKS (semua periode)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center py-3">
                <div class="text-muted small mb-1">NIP Login</div>
                <div class="h5 font-weight-bold text-dark mb-0">{{ $nip }}</div>
                <form action="{{ route('matkul-pengajar.refresh') }}" method="POST" class="d-inline">
                    @csrf
                    @if($periode) <input type="hidden" name="periode" value="{{ $periode }}"> @endif
                    <button type="submit" class="btn btn-xs btn-outline-secondary mt-1">
                        <i class="fas fa-sync-alt mr-1"></i>Refresh Data
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Filter Periode ──────────────────────────────────────────────────── --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('matkul-pengajar.index') }}" class="d-flex align-items-center flex-wrap">
                <label class="mb-0 font-weight-bold text-muted small mr-2">Filter Semester:</label>
                <select name="periode" class="form-control form-control-sm mr-2" style="width:auto;min-width:210px"
                        onchange="this.form.submit()">
                    <option value="">— Semua Semester —</option>
                    @foreach($periodeFromApi as $kode => $label)
                        <option value="{{ $kode }}" {{ $periode == $kode ? 'selected' : '' }}>
                            {{ $label }} ({{ $kode }})
                        </option>
                    @endforeach
                </select>
                @if($periode)
                    <a href="{{ route('matkul-pengajar.index') }}" class="btn btn-sm btn-light ml-1">
                        <i class="fas fa-times mr-1"></i>Reset
                    </a>
                    <span class="badge badge-primary ml-2 px-3 py-2">
                        {{ $periodeFromApi[$periode] ?? $periode }}
                    </span>
                @endif
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: UPLOAD SK PER PERIODE                                           --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="modalUploadSk" tabindex="-1" role="dialog"
         aria-labelledby="modalUploadSkLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUploadSkLabel">
                        <i class="fas fa-upload mr-2"></i>
                        Upload SK Mengajar — <span id="uploadSk_periodeLabel">...</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        SK ini akan digunakan untuk semua klaim matkul di semester
                        <strong id="uploadSk_periodeLabelInfo">ini</strong>.
                        Cukup upload <strong>satu kali</strong>.
                    </div>

                    {{-- Info SK existing jika ada --}}
                    <div id="uploadSk_existingInfo" class="alert alert-success py-2 mb-3 d-none">
                        <i class="fas fa-check-circle mr-1"></i>
                        SK tersedia: <strong id="uploadSk_existingName"></strong>
                        — upload file baru untuk mengganti.
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold">File SK Mengajar <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="uploadSkFile"
                                   accept=".pdf,.jpg,.jpeg,.png,.docx">
                            <label class="custom-file-label" for="uploadSkFile">Pilih file SK...</label>
                        </div>
                        <small class="text-muted">Format: PDF, JPG, PNG, DOCX — Maks. 5 MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnDoUploadSk">
                        <i class="fas fa-upload mr-1"></i>Upload SK
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- /.modal upload SK --}}

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- KELAS PER PERIODE                                                     --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}

    @if($kelasList->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3 d-block"></i>
                @if($dosenError)
                    <p class="text-muted">Tidak dapat memuat kelas karena data dosen tidak ditemukan di SIAKAD.</p>
                @elseif($apiError)
                    <p class="text-muted">Gagal terhubung ke SIAKAD. Coba beberapa saat lagi.</p>
                @else
                    <p class="text-muted">
                        Belum ada kelas yang ditemukan
                        @if($periode) untuk semester <strong>{{ $periodeFromApi[$periode] ?? $periode }}</strong> @endif.
                    </p>
                @endif
            </div>
        </div>
    @else
        <div id="accordion-kelas">
            @php $firstPeriode = true; @endphp
            @foreach($grouped as $idPeriode => $matkulGroups)
            @php
                $periodeLabel    = \App\DTOs\KelasDTO::formatPeriode($idPeriode);
                $collapseId      = 'collapse-' . $idPeriode;
                $kelasCount      = $matkulGroups->flatten(1)->count();
                $totalSksPeriode = $matkulGroups->flatten(1)->sum('sks');
                $diklaimCount    = $matkulGroups->flatten(1)->filter(fn ($k) => in_array((string)$k->id, $sudahDiklaim))->count();
            @endphp
            <div class="card shadow-sm mb-2">
                <div class="card-header p-0">
                    <button class="btn btn-link w-100 text-left px-3 py-2 d-flex align-items-center justify-content-between
                                   {{ $firstPeriode ? '' : 'collapsed' }}"
                            data-toggle="collapse" data-target="#{{ $collapseId }}"
                            aria-expanded="{{ $firstPeriode ? 'true' : 'false' }}">
                        <span>
                            <i class="fas fa-calendar-alt text-primary mr-2"></i>
                            <strong>{{ $periodeLabel }}</strong>
                            <span class="text-muted ml-2 small">({{ $idPeriode }})</span>
                        </span>
                        <span class="d-flex align-items-center">
                            <span class="badge badge-primary mr-1">{{ $matkulGroups->count() }} MK</span>
                            <span class="badge badge-info mr-1">{{ $kelasCount }} kelas</span>
                            <span class="badge badge-success mr-1">{{ $totalSksPeriode }} SKS</span>
                            @if($diklaimCount > 0)
                                <span class="badge badge-warning mr-2">{{ $diklaimCount }} diklaim</span>
                            @endif
                            {{-- Badge status SK per periode --}}
                            @if(isset($skPerPeriode[$idPeriode]))
                                <span class="badge badge-light border text-success mr-1 sk-badge-periode"
                                      data-periode="{{ $idPeriode }}"
                                      title="{{ $skPerPeriode[$idPeriode]['original_name'] }}">
                                    <i class="fas fa-paperclip mr-1"></i>SK Tersedia
                                </span>
                                <a href="{{ route('matkul-pengajar.sk-download', $skPerPeriode[$idPeriode]['id']) }}"
                                   target="_blank"
                                   class="badge badge-light border text-primary mr-2 sk-link-periode"
                                   data-periode="{{ $idPeriode }}"
                                   onclick="event.stopPropagation()"
                                   title="Lihat/download SK Mengajar">
                                    <i class="fas fa-eye mr-1"></i>Lihat SK
                                </a>
                            @else
                                <span class="badge badge-light border text-warning mr-2 sk-badge-periode"
                                      data-periode="{{ $idPeriode }}">
                                    <i class="fas fa-exclamation-circle mr-1"></i>Belum ada SK
                                </span>
                            @endif
                            {{-- Tombol Upload SK (stop propagation agar tidak toggle accordion) --}}
                            <button type="button"
                                    class="btn btn-xs btn-outline-secondary btn-upload-sk mr-2"
                                    title="Upload SK Mengajar untuk semester ini"
                                    data-periode="{{ $idPeriode }}"
                                    data-periode-label="{{ $periodeLabel }}"
                                    @if(isset($skPerPeriode[$idPeriode]))
                                        data-sk-existing="{{ $skPerPeriode[$idPeriode]['original_name'] }}"
                                    @endif
                                    onclick="event.stopPropagation(); bukaBtnUploadSk(this)">
                                <i class="fas fa-upload mr-1"></i>
                                {{ isset($skPerPeriode[$idPeriode]) ? 'Ganti SK' : 'Upload SK' }}
                            </button>
                            <i class="fas fa-chevron-down small text-muted"></i>
                        </span>
                    </button>
                </div>

                <div id="{{ $collapseId }}" class="collapse {{ $firstPeriode ? 'show' : '' }}"
                     data-parent="#accordion-kelas">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="4%">#</th>
                                    <th width="12%">Kode MK</th>
                                    <th>Nama Mata Kuliah</th>
                                    <th width="6%" class="text-center">SKS</th>
                                    <th width="18%">Program Studi</th>
                                    <th width="10%" class="text-center">Kelas</th>
                                    <th width="12%" class="text-center">Status Klaim</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($matkulGroups as $groupIdx => $kelasItems)
                                @php
                                    $first      = $kelasItems->first();
                                    // Gabung nama kelas: A, B, C
                                    $namaKelasGabung = $kelasItems->pluck('namaKelas')->sort()->implode(', ');
                                    // Semua ID kelas dalam group ini
                                    $kelasIds   = $kelasItems->pluck('id')->map(fn($v) => (string)$v)->toArray();
                                    // Cek apakah SEMUA kelas di group sudah diklaim
                                    $allDiklaim = collect($kelasIds)->every(fn ($id) => in_array($id, $sudahDiklaim));
                                @endphp
                                <tr class="{{ $allDiklaim ? 'table-success' : '' }}">
                                    <td class="text-muted small align-middle">{{ $groupIdx + 1 }}</td>
                                    <td class="align-middle"><code>{{ $first->kodeMatKul }}</code></td>
                                    <td class="align-middle font-weight-bold">{{ $first->namaMatKul }}</td>
                                    <td class="text-center align-middle font-weight-bold text-primary">{{ $first->sks }}</td>
                                    <td class="align-middle"><small>{{ $first->programStudi ?: '—' }}</small></td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-secondary px-2 py-1">{{ $namaKelasGabung }}</span>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($allDiklaim)
                                            <span class="badge badge-success px-2 py-1">
                                                <i class="fas fa-check mr-1"></i>Sudah Diklaim
                                            </span>
                                        @else
                                            <button type="button"
                                                    class="btn btn-xs btn-primary btn-klaim"
                                                    title="Klaim kelas ini"
                                                    data-kelas-ids="{{ $kelasItems->pluck('id')->implode(',') }}"
                                                    data-kode="{{ $first->kodeMatKul }}"
                                                    data-nama="{{ $first->namaMatKul }}"
                                                    data-kelas="{{ $namaKelasGabung }}"
                                                    data-sks="{{ $first->sks }}"
                                                    data-periode="{{ $first->idPeriode }}"
                                                    data-periode-label="{{ $first->periodeLabel }}"
                                                    data-prodi="{{ $first->programStudi }}"
                                                    data-prodi-id="{{ $first->idProgramStudi }}"
                                                    data-jenjang="{{ $first->jenjang }}"
                                                    data-kurikulum="{{ $first->idKurikulum }}"
                                                    data-daya-tampung="{{ $first->dayaTampung }}"
                                                    data-is-mbkm="{{ $first->isMbkm ? '1' : '0' }}"
                                                    data-kelas-detail='@json($kelasItems->map(fn($k) => ["id" => $k->id, "namaKelas" => $k->namaKelas])->values())'>
                                                <i class="fas fa-hand-pointer mr-1"></i>Klaim
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="3" class="text-right text-muted small font-weight-bold">
                                        Total: {{ $matkulGroups->count() }} mata kuliah, {{ $kelasCount }} kelas
                                    </td>
                                    <td class="text-center font-weight-bold text-success">{{ $totalSksPeriode }}</td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @php $firstPeriode = false; @endphp
            @endforeach
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL KLAIM                                                            --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="modalKlaim" tabindex="-1" role="dialog" aria-labelledby="modalKlaimLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="formKlaim" action="{{ route('matkul-pengajar.klaim') }}" method="POST"
                  enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="redirect_periode" value="{{ $periode }}">

                {{-- Hidden fields — diisi oleh JS --}}
                <input type="hidden" name="kelas_ids"          id="f_kelas_ids">
                <input type="hidden" name="kelas_detail_json"  id="f_kelas_detail_json">
                <input type="hidden" name="kode_mata_kuliah" id="f_kode_mata_kuliah">
                <input type="hidden" name="nama_mata_kuliah" id="f_nama_mata_kuliah">
                <input type="hidden" name="nama_kelas"       id="f_nama_kelas">
                <input type="hidden" name="sks_siakad"       id="f_sks_siakad">
                <input type="hidden" name="id_periode"       id="f_id_periode">
                <input type="hidden" name="periode_label"    id="f_periode_label">
                <input type="hidden" name="program_studi"    id="f_program_studi">
                <input type="hidden" name="id_program_studi" id="f_id_program_studi">
                <input type="hidden" name="jenjang"          id="f_jenjang">
                <input type="hidden" name="id_kurikulum"     id="f_id_kurikulum">
                <input type="hidden" name="daya_tampung"     id="f_daya_tampung">
                <input type="hidden" name="is_mbkm"          id="f_is_mbkm">

                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalKlaimLabel">
                            <i class="fas fa-hand-pointer mr-2"></i>Klaim Kelas Mengajar
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        {{-- ── Error validasi ─────────────────────────────────────────── --}}
                        @if($errors->any())
                            <div class="alert alert-danger alert-sm py-2 mb-3">
                                <strong><i class="fas fa-exclamation-circle mr-1"></i>Perbaiki data berikut:</strong>
                                <ul class="mb-0 mt-1 pl-3">
                                    @foreach($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- ── Info Kelas (read-only) ─────────────────────────────────── --}}
                        <div class="card bg-light mb-3">
                            <div class="card-body py-2 px-3">
                                <div class="row">
                                    <div class="col-md-5">
                                        <small class="text-muted">Mata Kuliah</small>
                                        <p class="mb-1 font-weight-bold" id="info_nama_mk">—</p>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Kode MK</small>
                                        <p class="mb-1 font-weight-bold" id="info_kode_mk">—</p>
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-muted">Kelas</small>
                                        <p class="mb-1 font-weight-bold" id="info_kelas">—</p>
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-muted">SKS SIAKAD</small>
                                        <p class="mb-1 font-weight-bold text-primary" id="info_sks_siakad">—</p>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Program Studi</small>
                                        <p class="mb-0" id="info_prodi">—</p>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Semester</small>
                                        <p class="mb-0" id="info_periode">—</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SKS Pengusul (editable, max = sks SIAKAD) --}}
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label font-weight-bold">
                                SKS Sesuai SK Mengajar <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <div class="input-group" style="max-width:200px">
                                    <input type="number" name="sks_pengusul" id="f_sks_pengusul"
                                           class="form-control @error('sks_pengusul') is-invalid @enderror"
                                           min="1" max="20" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">SKS</span>
                                    </div>
                                    @error('sks_pengusul')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted" id="info_sks_hint">
                                    Maks. <strong id="info_sks_max">&mdash;</strong> SKS sesuai data SIAKAD.
                                    Boleh dikurangi, <em>tidak boleh ditambah</em>.
                                </small>
                            </div>
                        </div>

                        {{-- Upload SK Mengajar (opsional jika periode sudah punya SK) --}}
                        <div class="form-group row mb-0">
                            <label class="col-sm-4 col-form-label font-weight-bold">
                                SK Mengajar
                                <span class="text-danger" id="lbl_sk_required">*</span>
                            </label>
                            <div class="col-sm-8">
                                {{-- Info SK existing jika periode ini sudah punya SK --}}
                                <div id="sk_existing_info" class="alert alert-success py-1 px-2 mb-2 d-none small">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    SK tersedia: <strong id="sk_existing_name"></strong>
                                    &mdash; upload baru untuk mengganti, atau biarkan untuk menggunakan ini.
                                </div>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('sk_mengajar') is-invalid @enderror"
                                           id="sk_mengajar" name="sk_mengajar"
                                           accept=".pdf,.jpg,.jpeg,.png,.docx">
                                    <label class="custom-file-label" for="sk_mengajar">
                                        Pilih file SK...
                                    </label>
                                    @error('sk_mengajar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Format: PDF, JPG, PNG, DOCX &mdash; Maks. 5 MB</small>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitKlaim">
                            <i class="fas fa-check mr-1"></i>Simpan Klaim
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// [A] SK Per Periode: state yang dikelola di JS
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

// Map: id_periode → { id, original_name, size_readable, uploaded_at} (dari DB, permanen)
const skPerPeriodeServer = @json($skPerPeriode);

// Route template untuk download SK (diisi id nanti)
const skDownloadBaseUrl = '{{ url('matkul-pengajar/sk') }}';

function getSkForPeriode(idPeriode) {
    return skPerPeriodeServer[idPeriode] || null;
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// [B] Modal Upload SK per Periode
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

let uploadSkPeriodeTarget = null; // id_periode yang sedang di-upload

function bukaBtnUploadSk(btn) {
    uploadSkPeriodeTarget = btn.dataset.periode;
    const label = btn.dataset.periodeLabel || uploadSkPeriodeTarget;
    document.getElementById('uploadSk_periodeLabel').textContent     = label;
    document.getElementById('uploadSk_periodeLabelInfo').textContent = label;

    // Reset file input
    document.getElementById('uploadSkFile').value = '';
    document.querySelector('label[for="uploadSkFile"]').textContent = 'Pilih file SK...';

    // Tampilkan info SK existing jika ada
    const existing = getSkForPeriode(uploadSkPeriodeTarget);
    const infoDiv   = document.getElementById('uploadSk_existingInfo');
    if (existing) {
        document.getElementById('uploadSk_existingName').textContent = existing.original_name;
        infoDiv.classList.remove('d-none');
    } else {
        infoDiv.classList.add('d-none');
    }

    $('#modalUploadSk').modal('show');
}

document.getElementById('uploadSkFile').addEventListener('change', function() {
    const label = document.querySelector('label[for="uploadSkFile"]');
    label.textContent = this.files.length > 0 ? this.files[0].name : 'Pilih file SK...';
});

document.getElementById('btnDoUploadSk').addEventListener('click', function() {
    const file = document.getElementById('uploadSkFile').files[0];
    if (!file) {
        alert('Pilih file terlebih dahulu.');
        return;
    }

    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Mengupload...';

    const formData = new FormData();
    formData.append('sk_file', file);
    formData.append('id_periode', uploadSkPeriodeTarget);
    formData.append('periode_label', document.getElementById('uploadSk_periodeLabelInfo').textContent || '');
    formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content
        || '{{ csrf_token() }}');

    fetch('{{ route('matkul-pengajar.upload-sk') }}', {
        method: 'POST',
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload mr-1"></i>Upload SK';

        if (data.success) {
            // Update state lokal
            skPerPeriodeServer[data.id_periode] = {
                id:            data.id,
                original_name: data.original_name,
                size_readable: data.size_readable,
                uploaded_at:   data.uploaded_at,
            };

            // Update badge + tambah link Lihat SK di accordion header
            updateSkBadge(data.id_periode, data.original_name, data.id);

            $('#modalUploadSk').modal('hide');

            // Toast notifikasi sukses
            const toast = document.createElement('div');
            toast.className = 'alert alert-success alert-dismissible fade show';
            toast.style.cssText = 'position:fixed;top:70px;right:20px;z-index:9999;min-width:300px';
            toast.innerHTML = '<i class="fas fa-check-circle mr-2"></i>'
                + 'SK berhasil disimpan: <strong>' + data.original_name + '</strong>'
                + ' <small class="text-muted">(' + data.size_readable + ')</small>'
                + '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 5000);
        } else {
            alert('Gagal upload: ' + (data.message || 'Error tidak diketahui'));
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload mr-1"></i>Upload SK';
        alert('Terjadi kesalahan saat upload. Coba lagi.');
    });
});

function updateSkBadge(idPeriode, namaFile, skId) {
    // Update badge status
    const badges = document.querySelectorAll('.sk-badge-periode[data-periode="' + idPeriode + '"]');
    badges.forEach(badge => {
        badge.className = 'badge badge-light border text-success mr-1 sk-badge-periode';
        badge.setAttribute('title', namaFile);
        badge.innerHTML = '<i class="fas fa-paperclip mr-1"></i>SK Tersedia';
    });

    // Tambah / update link Lihat SK
    const existingLinks = document.querySelectorAll('.sk-link-periode[data-periode="' + idPeriode + '"]');
    if (existingLinks.length > 0) {
        existingLinks.forEach(link => {
            link.href = skDownloadBaseUrl + '/' + skId;
            link.setAttribute('title', 'Lihat/download: ' + namaFile);
        });
    } else if (skId) {
        // Buat link baru setelah badge
        badges.forEach(badge => {
            const link = document.createElement('a');
            link.href       = skDownloadBaseUrl + '/' + skId;
            link.target     = '_blank';
            link.className  = 'badge badge-light border text-primary mr-2 sk-link-periode';
            link.dataset.periode = idPeriode;
            link.title      = 'Lihat/download: ' + namaFile;
            link.setAttribute('onclick', 'event.stopPropagation()');
            link.innerHTML  = '<i class="fas fa-eye mr-1"></i>Lihat SK';
            badge.insertAdjacentElement('afterend', link);
        });
    }

    // Ubah teks tombol Upload SK → Ganti SK
    const uploadBtns = document.querySelectorAll('.btn-upload-sk[data-periode="' + idPeriode + '"]');
    uploadBtns.forEach(btn => {
        btn.dataset.skExisting = namaFile;
        btn.innerHTML = '<i class="fas fa-upload mr-1"></i>Ganti SK';
    });
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// [C] Modal Klaim
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

function isiModalKlaim(d) {
    document.getElementById('f_kelas_ids').value         = d.kelasIds  || d['kelas_ids']        || '';
    document.getElementById('f_kelas_detail_json').value = d.kelasDetail || d['kelas_detail_json'] || '';
    document.getElementById('f_kode_mata_kuliah').value  = d.kode      || d['kode_mata_kuliah'] || '';
    document.getElementById('f_nama_mata_kuliah').value  = d.nama      || d['nama_mata_kuliah'] || '';
    document.getElementById('f_nama_kelas').value        = d.kelas     || d['nama_kelas']       || '';
    document.getElementById('f_sks_siakad').value        = d.sks       || d['sks_siakad']       || '';
    document.getElementById('f_id_periode').value        = d.periode   || d['id_periode']       || '';
    document.getElementById('f_periode_label').value     = d.periodeLabel || d['periode_label'] || '';
    document.getElementById('f_program_studi').value     = d.prodi     || d['program_studi']    || '';
    document.getElementById('f_id_program_studi').value  = d.prodiId   || d['id_program_studi'] || '';
    document.getElementById('f_jenjang').value           = d.jenjang   || d['jenjang']          || '';
    document.getElementById('f_id_kurikulum').value      = d.kurikulum || d['id_kurikulum']     || '';
    document.getElementById('f_daya_tampung').value      = d.dayaTampung || d['daya_tampung']   || '';
    document.getElementById('f_is_mbkm').value           = d.isMbkm    || d['is_mbkm']          || '0';

    const sks         = parseInt(d.sks || d['sks_siakad'] || 20);
    const idPeriode   = d.periode || d['id_periode'] || '';
    const skPengusul  = document.getElementById('f_sks_pengusul');

    document.getElementById('info_nama_mk').textContent    = d.nama    || d['nama_mata_kuliah'] || '—';
    document.getElementById('info_kode_mk').textContent    = d.kode    || d['kode_mata_kuliah'] || '—';
    document.getElementById('info_kelas').textContent      = d.kelas   || d['nama_kelas']       || '—';
    document.getElementById('info_prodi').textContent      = d.prodi   || d['program_studi']    || '—';
    document.getElementById('info_periode').textContent    = d.periodeLabel || d['periode_label'] || '—';
    document.getElementById('info_sks_siakad').textContent = sks + ' SKS';

    // Set max SKS pengusul = sks SIAKAD (tidak boleh melebihi)
    skPengusul.max = sks;
    document.getElementById('info_sks_max').textContent = sks;
    if (!skPengusul.value || parseInt(skPengusul.value) > sks) {
        skPengusul.value = sks;
    }

    // Cek apakah SK sudah tersedia untuk periode ini
    const skExisting   = getSkForPeriode(idPeriode);
    const skInfoDiv    = document.getElementById('sk_existing_info');
    const skInput      = document.getElementById('sk_mengajar');
    const lblRequired  = document.getElementById('lbl_sk_required');

    if (skExisting) {
        document.getElementById('sk_existing_name').textContent = skExisting.original_name;
        skInfoDiv.classList.remove('d-none');
        // SK sudah ada → upload tidak wajib
        skInput.removeAttribute('required');
        lblRequired.classList.add('d-none');
    } else {
        skInfoDiv.classList.add('d-none');
        // Belum ada SK → upload wajib
        skInput.setAttribute('required', 'required');
        lblRequired.classList.remove('d-none');
    }
}

document.querySelectorAll('.btn-klaim').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('sk_mengajar').value = '';
        document.querySelector('label[for="sk_mengajar"]').textContent = 'Pilih file SK...';
        document.getElementById('f_sks_pengusul').value = '';

        isiModalKlaim(this.dataset);
        $('#modalKlaim').modal('show');
    });
});

// ── Auto-buka modal jika ada error validasi ──────────────────────────────────
@if($errors->any() || session('open_modal_klaim'))
    const oldData = {
        'kelas_ids':        '{{ old('kelas_ids') }}',
        'kode_mata_kuliah': '{{ old('kode_mata_kuliah') }}',
        'nama_mata_kuliah': '{{ old('nama_mata_kuliah') }}',
        'nama_kelas':       '{{ old('nama_kelas') }}',
        'sks_siakad':       '{{ old('sks_siakad') }}',
        'id_periode':       '{{ old('id_periode') }}',
        'periode_label':    '{{ old('periode_label') }}',
        'program_studi':    '{{ old('program_studi') }}',
        'id_program_studi': '{{ old('id_program_studi') }}',
        'jenjang':          '{{ old('jenjang') }}',
        'id_kurikulum':     '{{ old('id_kurikulum') }}',
        'daya_tampung':     '{{ old('daya_tampung') }}',
        'is_mbkm':          '{{ old('is_mbkm', '0') }}',
    };
    document.getElementById('f_sks_pengusul').value = '{{ old('sks_pengusul') }}';
    isiModalKlaim(oldData);

    document.addEventListener('DOMContentLoaded', function() {
        $('#modalKlaim').modal('show');
    });
    if (document.readyState !== 'loading') {
        $('#modalKlaim').modal('show');
    }
@endif

// ── Update label custom file input ───────────────────────────────────────────
document.getElementById('sk_mengajar').addEventListener('change', function() {
    const label = document.querySelector('label[for="sk_mengajar"]');
    label.textContent = this.files.length > 0 ? this.files[0].name : 'Pilih file SK...';
});

// ── Loading state saat submit ────────────────────────────────────────────────
document.getElementById('formKlaim').addEventListener('submit', function() {
    const btn = document.getElementById('btnSubmitKlaim');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...';
});
</script>
@endpush
