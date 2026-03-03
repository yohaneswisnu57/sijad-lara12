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
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap:8px">
        <small class="text-muted">Data kelas dari SIAKAD Cloud · Klaim untuk memasukkan ke riwayat kegiatan</small>
        <button type="button" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#modalPengajuanManual">
            <i class="fas fa-plus mr-1"></i> Pengajuan Manual
        </button>
    </div>

    {{-- ── Filter Periode ───────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('kelas-mengajar.index') }}" class="mb-4">
        <div class="card shadow-sm">
            <div class="card-body py-2">
                <div class="d-flex align-items-center flex-wrap" style="gap:8px">
                    <label class="mb-0 font-weight-bold text-muted small">Filter Periode/Semester:</label>
                    <select name="periode" class="form-control form-control-sm"
                            style="flex:1;min-width:160px;max-width:280px"
                            onchange="this.form.submit()">
                        <option value="">-- Semua Periode --</option>
                        @foreach($periodeList as $kode => $label)
                            <option value="{{ $kode }}" {{ $periode == $kode ? 'selected' : '' }}>
                                {{ $kode }} — {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @if($periode)
                        <span class="badge badge-primary px-3 py-2">
                            Menampilkan: {{ $periodeList[$periode] ?? $periode }}
                        </span>
                        <a href="{{ route('kelas-mengajar.index') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </form>


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
