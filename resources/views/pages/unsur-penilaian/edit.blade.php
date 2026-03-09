@extends('partials.layouts.app-layout')

@section('content')

    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-right">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">SIJAD</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('unsur-penilaian.tree') }}">Unsur Penilaian</a></li>
                        <li class="breadcrumb-item active">Edit Data</li>
                    </ol>
                </div>
                <h4 class="page-title">Edit Unsur Penilaian</h4>
            </div><!--end page-title-box-->
        </div><!--end col-->
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mt-0 header-title">Form Edit Unsur Penilaian</h4>
                    <p class="text-muted mb-3">Silakan update data berikut.</p>

                    <form action="{{ route('unsur-penilaian.update', $unsurPenilaian->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group row">
                            <label for="kode_nomor" class="col-sm-2 col-form-label text-right">Kode Nomor</label>
                            <div class="col-sm-10">
                                <input class="form-control" type="text" value="{{ old('kode_nomor', $unsurPenilaian->kode_nomor) }}" id="kode_nomor" name="kode_nomor" placeholder="Contoh: A.1, I.A, dsb" required>
                                @error('kode_nomor')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="nama_unsur" class="col-sm-2 col-form-label text-right">Nama Unsur</label>
                            <div class="col-sm-10">
                                <input class="form-control" type="text" value="{{ old('nama_unsur', $unsurPenilaian->nama_unsur) }}" id="nama_unsur" name="nama_unsur" placeholder="Masukkan nama unsur penilaian" required>
                                @error('nama_unsur')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label text-right">Induk (Parent)</label>
                            <div class="col-sm-10">
                                <select class="form-control" name="parent_id">
                                    <option value="">-- Pilih Induk (Opsional) --</option>
                                    @foreach($parents as $parent)
                                        <option value="{{ $parent->id }}" {{ old('parent_id', $unsurPenilaian->parent_id) == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->kode_nomor }} - {{ $parent->nama_unsur }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Kosongkan jika ini adalah unsur tingkat paling atas (Top Level).</small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label text-right">Opsi Tambahan</label>
                            <div class="col-sm-10">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_header" name="is_header" value="1" {{ old('is_header', $unsurPenilaian->is_header) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_header">Set sebagai Header (Induk)</label>
                                </div>
                                <small class="form-text text-muted">Centang jika unsur ini akan memiliki sub-unsur di bawahnya. Hapus centang jika ini adalah detail yang dinilai.</small>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-sm-10 offset-sm-2">
                                <button type="submit" class="btn btn-primary waves-effect waves-light">
                                    <i class="fas fa-save mr-1"></i> Update Data
                                </button>
                                <a href="{{ route('unsur-penilaian.tree') }}" class="btn btn-secondary waves-effect m-l-5">
                                    <i class="fas fa-times mr-1"></i> Batal
                                </a>
                            </div>
                        </div>
                    </form>
                </div> <!-- end card-body -->
            </div> <!-- end card -->
        </div> <!-- end col -->
    </div> <!-- end row -->

@endsection
