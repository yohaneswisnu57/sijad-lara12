@extends('partials.layouts.app-layout')

@section('title', 'Kelas Mengajar Saya')

@section('content')

    {{-- ── Page Title ─────────────────────────────────────────────────────── --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-right">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">SIJAD</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Transaksi</a></li>
                        <li class="breadcrumb-item active">Kelas Mengajar</li>
                    </ol>
                </div>
                <h4 class="page-title">Kelas Mengajar Saya</h4>
            </div>
        </div>
    </div>

    {{-- ── Tombol Tambah Manual ─────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <small class="text-muted">Data kelas dari SIAKAD Cloud · Klaim untuk memasukkan ke riwayat kegiatan</small>
        <button type="button" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#modalPengajuanManual">
            <i class="fas fa-plus mr-1"></i> Pengajuan Manual
        </button>
    </div>
    @if($apiError)
        <div class="alert alert-danger py-2">
            <i class="fas fa-wifi mr-1"></i> <strong>Gagal terhubung ke SIAKAD:</strong> {{ $apiError }}
            <p class="mb-0 mt-1 small">Data lokal tetap ditampilkan. Coba refresh atau hubungi administrator.</p>
        </div>
    @endif

    {{-- ── Filter Periode ──────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('kelas-mengajar.index') }}" class="mb-4">
        <div class="card shadow-sm">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <label class="mb-0 font-weight-bold text-muted small">Filter Periode/Semester:</label>
                    </div>
                    <div class="col-md-3">
                        <select name="periode" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">-- Semua Periode --</option>
                            @foreach($periodeList as $kode => $label)
                                <option value="{{ $kode }}" {{ $periode == $kode ? 'selected' : '' }}>
                                    {{ $kode }} — {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if($periode)
                        <div class="col-auto">
                            <span class="badge badge-primary px-3 py-2">
                                Menampilkan: {{ $periodeList[$periode] ?? $periode }}
                            </span>
                            <a href="{{ route('kelas-mengajar.index') }}" class="btn btn-sm btn-light ml-1">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </form>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- BAGIAN 1: KELAS DARI SIAKAD YANG BELUM DIKLAIM                      --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <i class="fas fa-cloud-download-alt text-info mr-2"></i>
                <strong>Kelas dari SIAKAD — Belum Diklaim</strong>
                @if(!$apiError)
                    <span class="badge badge-info ml-2">{{ $kelasBelumDiklaim->count() }} kelas</span>
                @endif
            </div>
            <small class="text-muted">Klik "Klaim" untuk memasukkan ke riwayat mengajar Anda</small>
        </div>
        <div class="card-body p-0">
            @if($apiError)
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-plug fa-2x mb-2 d-block"></i>
                    Tidak dapat memuat data SIAKAD saat ini.
                </div>
            @elseif($kelasBelumDiklaim->isEmpty())
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-check-double fa-2x mb-2 d-block text-success"></i>
                    @if($periode)
                        Semua kelas periode <strong>{{ $periodeList[$periode] ?? $periode }}</strong> sudah diklaim.
                    @else
                        Semua kelas dari SIAKAD sudah diklaim, atau belum ada data.
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Kode MK</th>
                                <th>Nama Mata Kuliah</th>
                                <th class="text-center">Kelas</th>
                                <th class="text-center">SKS</th>
                                <th>Periode</th>
                                <th>Program Studi</th>
                                <th class="text-center">Kuota</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kelasBelumDiklaim as $i => $kelas)
                            <tr>
                                <td class="text-muted small">{{ $i + 1 }}</td>
                                <td><code>{{ $kelas->kodeMatKul }}</code></td>
                                <td>
                                    {{ $kelas->namaMatKul }}
                                    @if($kelas->isMbkm)
                                        <span class="badge badge-warning ml-1">MBKM</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary">{{ $kelas->namaKelas }}</span>
                                </td>
                                <td class="text-center font-weight-bold">{{ $kelas->sks }}</td>
                                <td>
                                    <span class="text-nowrap">{{ $kelas->periodeLabel }}</span>
                                    <br><small class="text-muted">{{ $kelas->idPeriode }}</small>
                                </td>
                                <td><small>{{ $kelas->programStudi }}</small></td>
                                <td class="text-center">{{ $kelas->dayaTampung }}</td>
                                <td class="text-center text-nowrap">
                                    {{-- Form klaim: kirim semua data kelas sebagai hidden fields --}}
                                    <form action="{{ route('kelas-mengajar.klaim') }}" method="POST"
                                          class="d-inline form-klaim">
                                        @csrf
                                        <input type="hidden" name="kelas_id_siakad"  value="{{ $kelas->id }}">
                                        <input type="hidden" name="kode_mata_kuliah" value="{{ $kelas->kodeMatKul }}">
                                        <input type="hidden" name="nama_mata_kuliah" value="{{ $kelas->namaMatKul }}">
                                        <input type="hidden" name="nama_kelas"       value="{{ $kelas->namaKelas }}">
                                        <input type="hidden" name="sks"              value="{{ $kelas->sks }}">
                                        <input type="hidden" name="id_periode"       value="{{ $kelas->idPeriode }}">
                                        <input type="hidden" name="periode_label"    value="{{ $kelas->periodeLabel }}">
                                        <input type="hidden" name="program_studi"    value="{{ $kelas->programStudi }}">
                                        <input type="hidden" name="jenjang"          value="{{ $kelas->jenjang }}">
                                        <input type="hidden" name="id_kurikulum"     value="{{ $kelas->idKurikulum }}">
                                        <input type="hidden" name="id_program_studi" value="{{ $kelas->idProgramStudi }}">
                                        <input type="hidden" name="daya_tampung"     value="{{ $kelas->dayaTampung }}">
                                        <input type="hidden" name="is_mbkm"          value="{{ $kelas->isMbkm ? 1 : 0 }}">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-check mr-1"></i>Klaim
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- BAGIAN 2: KELAS YANG SUDAH DIKLAIM / AKTIF                          --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    @if($kelasAktif->isNotEmpty())
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white d-flex align-items-center justify-content-between">
            <div>
                <i class="fas fa-check-circle mr-2"></i>
                <strong>Sudah Diklaim / Aktif</strong>
                <span class="badge badge-light text-success ml-2">{{ $kelasAktif->count() }}</span>
            </div>
        </div>
        <div class="card-body p-0">
            @include('pages.kelas-mengajar.partials.table-kelas', [
                'kelas' => $kelasAktif,
                'showBadge' => true,
            ])
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- BAGIAN 3: PENGAJUAN MANUAL PENDING                                  --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    @if($kelasPending->isNotEmpty())
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning d-flex align-items-center justify-content-between">
            <div>
                <i class="fas fa-hourglass-half mr-2"></i>
                <strong>Pengajuan Manual — Menunggu Persetujuan</strong>
                <span class="badge badge-light text-warning ml-2">{{ $kelasPending->count() }}</span>
            </div>
        </div>
        <div class="card-body p-0">
            @include('pages.kelas-mengajar.partials.table-kelas', [
                'kelas' => $kelasPending,
                'showBadge' => true,
            ])
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- BAGIAN 4: YANG DITOLAK                                              --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    @if($kelasDitolak->isNotEmpty())
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-danger text-white d-flex align-items-center">
            <i class="fas fa-times-circle mr-2"></i>
            <strong>Ditolak</strong>
            <span class="badge badge-light text-danger ml-2">{{ $kelasDitolak->count() }}</span>
        </div>
        <div class="card-body p-0">
            @include('pages.kelas-mengajar.partials.table-kelas', [
                'kelas' => $kelasDitolak,
                'showBadge' => true,
            ])
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: PENGAJUAN KELAS MANUAL                                        --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="modalPengajuanManual" tabindex="-1" role="dialog"
         aria-labelledby="modalPengajuanManualLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalPengajuanManualLabel">
                        <i class="fas fa-edit mr-1"></i> Pengajuan Kelas Manual
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form action="{{ route('kelas-mengajar.store') }}" method="POST" enctype="multipart/form-data" id="formPengajuanManual">
                    @csrf

                    <div class="modal-body">

                        {{-- Alert error jika ada validasi gagal --}}
                        @if($errors->any())
                            <div class="alert alert-danger py-2">
                                <strong><i class="fas fa-exclamation-circle mr-1"></i>Ada kesalahan input:</strong>
                                <ul class="mb-0 mt-1 pl-3">
                                    @foreach($errors->all() as $e)
                                        <li class="small">{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="alert alert-info py-2 mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Pengajuan ini memerlukan upload <strong>SK Mengajar</strong> dan akan diverifikasi oleh admin.
                        </div>

                        {{-- Info Mata Kuliah --}}
                        <h6 class="text-muted mb-3 border-bottom pb-2">
                            <i class="fas fa-book mr-1"></i> Informasi Mata Kuliah
                        </h6>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kode Mata Kuliah <span class="text-danger">*</span></label>
                                    <input type="text" name="kode_mata_kuliah"
                                           class="form-control @error('kode_mata_kuliah') is-invalid @enderror"
                                           value="{{ old('kode_mata_kuliah') }}" placeholder="Contoh: FTH205" required>
                                    @error('kode_mata_kuliah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Nama Mata Kuliah <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_mata_kuliah"
                                           class="form-control @error('nama_mata_kuliah') is-invalid @enderror"
                                           value="{{ old('nama_mata_kuliah') }}" placeholder="Nama lengkap mata kuliah" required>
                                    @error('nama_mata_kuliah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Kelas <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_kelas"
                                           class="form-control @error('nama_kelas') is-invalid @enderror"
                                           value="{{ old('nama_kelas') }}" placeholder="A / B / C" maxlength="10" required>
                                    @error('nama_kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>SKS <span class="text-danger">*</span></label>
                                    <input type="number" name="sks"
                                           class="form-control @error('sks') is-invalid @enderror"
                                           value="{{ old('sks') }}" min="1" max="20" required>
                                    @error('sks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Periode / Semester <span class="text-danger">*</span></label>
                                    <select name="id_periode" class="form-control @error('id_periode') is-invalid @enderror" required>
                                        <option value="">-- Pilih Periode --</option>
                                        @foreach($periodeList as $kode => $label)
                                            <option value="{{ $kode }}" {{ old('id_periode') == $kode ? 'selected' : '' }}>
                                                {{ $kode }} — {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_periode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Program Studi</label>
                            <input type="text" name="program_studi" class="form-control"
                                   value="{{ old('program_studi') }}" placeholder="(Opsional)">
                        </div>

                        {{-- Upload SK Mengajar --}}
                        <h6 class="text-muted mb-3 border-bottom pb-2 mt-3">
                            <i class="fas fa-file-upload mr-1"></i> SK Mengajar <span class="text-danger">*</span>
                        </h6>

                        <div class="form-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('sk_mengajar') is-invalid @enderror"
                                       id="sk_mengajar_modal" name="sk_mengajar"
                                       accept=".pdf,.jpg,.jpeg,.png,.docx" required>
                                <label class="custom-file-label" for="sk_mengajar_modal">Pilih file SK Mengajar...</label>
                                @error('sk_mengajar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <small class="text-muted">Format: PDF, JPG, PNG, DOCX &middot; Maks. 5MB &middot; File disimpan secara aman</small>
                        </div>

                        {{-- Catatan --}}
                        <div class="form-group">
                            <label>Catatan / Keterangan</label>
                            <textarea name="catatan" class="form-control" rows="3"
                                placeholder="(Opsional) Jelaskan alasan pengajuan manual, misalnya kelas belum terdaftar di SIAKAD">{{ old('catatan') }}</textarea>
                        </div>

                    </div>{{-- /.modal-body --}}

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane mr-1"></i> Ajukan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
    {{-- /.modal --}}

@endsection

@push('scripts')
<script>
// Konfirmasi sebelum klaim
document.querySelectorAll('.form-klaim').forEach(form => {
    form.addEventListener('submit', function(e) {
        const nama = this.querySelector('[name="nama_mata_kuliah"]').value;
        const kelas = this.querySelector('[name="nama_kelas"]').value;
        if (!confirm(`Klaim kelas "${nama} (${kelas})"?\n\nKelas ini akan masuk ke riwayat kegiatan mengajar Anda.`)) {
            e.preventDefault();
        }
    });
});

// Update nama file pada custom file input (modal)
document.getElementById('sk_mengajar_modal')?.addEventListener('change', function() {
    const fileName = this.files[0] ? this.files[0].name : 'Pilih file SK Mengajar...';
    this.nextElementSibling.textContent = fileName;
});

// Buka modal otomatis jika ada error validasi (setelah redirect back)
@if($errors->any())
$(document).ready(function() {
    $('#modalPengajuanManual').modal('show');
});
@endif
</script>
@endpush
