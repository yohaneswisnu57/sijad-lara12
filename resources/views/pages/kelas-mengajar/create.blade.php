@extends('partials.layouts.app-layout')

@section('title', 'Pengajuan Kelas Manual')

@section('content')

    {{-- ── Page Title ─────────────────────────────────────────────────────── --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-right">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">SIJAD</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Transaksi</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('kelas-mengajar.index') }}">Kelas Mengajar</a></li>
                        <li class="breadcrumb-item active">Pengajuan Manual</li>
                    </ol>
                </div>
                <h4 class="page-title">Pengajuan Kelas Manual</h4>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">

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

            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <i class="fas fa-info-circle text-info mr-1"></i>
                    Pengajuan ini memerlukan upload <strong>SK Mengajar</strong> dan akan diverifikasi oleh admin.
                </div>
                <div class="card-body">
                    <form action="{{ route('kelas-mengajar.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- ── Info Mata Kuliah ──────────────────────────────── --}}
                        <h6 class="text-muted mb-3 border-bottom pb-2">
                            <i class="fas fa-book mr-1"></i> Informasi Mata Kuliah
                        </h6>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kode Mata Kuliah <span class="text-danger">*</span></label>
                                    <input type="text" name="kode_mata_kuliah" class="form-control @error('kode_mata_kuliah') is-invalid @enderror"
                                           value="{{ old('kode_mata_kuliah') }}" placeholder="Contoh: FTH205" required>
                                    @error('kode_mata_kuliah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Nama Mata Kuliah <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_mata_kuliah" class="form-control @error('nama_mata_kuliah') is-invalid @enderror"
                                           value="{{ old('nama_mata_kuliah') }}" placeholder="Nama lengkap mata kuliah" required>
                                    @error('nama_mata_kuliah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Kelas <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_kelas" class="form-control @error('nama_kelas') is-invalid @enderror"
                                           value="{{ old('nama_kelas') }}" placeholder="A / B / C" maxlength="10" required>
                                    @error('nama_kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>SKS <span class="text-danger">*</span></label>
                                    <input type="number" name="sks" class="form-control @error('sks') is-invalid @enderror"
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

                        {{-- ── Upload SK Mengajar ───────────────────────────── --}}
                        <h6 class="text-muted mb-3 border-bottom pb-2 mt-4">
                            <i class="fas fa-file-upload mr-1"></i> SK Mengajar <span class="text-danger">*</span>
                        </h6>

                        <div class="form-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('sk_mengajar') is-invalid @enderror"
                                       id="sk_mengajar" name="sk_mengajar"
                                       accept=".pdf,.jpg,.jpeg,.png,.docx" required>
                                <label class="custom-file-label" for="sk_mengajar">Pilih file SK Mengajar...</label>
                                @error('sk_mengajar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <small class="text-muted">Format: PDF, JPG, PNG, DOCX · Maks. 5MB · File disimpan secara aman</small>
                        </div>

                        {{-- ── Catatan ──────────────────────────────────────── --}}
                        <div class="form-group">
                            <label>Catatan / Keterangan</label>
                            <textarea name="catatan" class="form-control" rows="3"
                                placeholder="(Opsional) Jelaskan alasan pengajuan manual, misalnya kelas belum terdaftar di SIAKAD">{{ old('catatan') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <a href="{{ route('kelas-mengajar.index') }}" class="btn btn-light mr-2">
                                <i class="fas fa-times mr-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane mr-1"></i> Ajukan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
<script>
// Update nama file pada custom file input
document.getElementById('sk_mengajar').addEventListener('change', function() {
    const fileName = this.files[0] ? this.files[0].name : 'Pilih file SK Mengajar...';
    this.nextElementSibling.textContent = fileName;
});
</script>
@endpush
