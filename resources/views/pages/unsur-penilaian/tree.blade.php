@extends('partials.layouts.app-layout')

@section('title', 'Struktur Hierarki Unsur Penilaian')

@section('content')

    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-right">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">SIJAD</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('unsur-penilaian.index') }}">Master Data</a></li>
                        <li class="breadcrumb-item active">Tree View</li>
                    </ol>
                </div>
                <h4 class="page-title">Struktur Hierarki Unsur Penilaian</h4>
            </div><!--end page-title-box-->
        </div><!--end col-->
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h4 class="mt-0 header-title">Tree View Unsur Penilaian</h4>
                            <p class="text-muted font-13 mb-0">Menampilkan struktur hierarki induk dan sub-unsur penilaian.</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('unsur-penilaian.index') }}" class="btn btn-secondary btn-sm mr-2"><i class="fas fa-list mr-2"></i>Tampilan List</a>
                            <a href="{{ route('unsur-penilaian.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-2"></i>Tambah Data Baru</a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="thead-light">
                            <tr>
                                <th width="25%">Kode Nomor</th>
                                <th>Nama Unsur</th>
                                <th width="10%" class="text-center">Jenis</th>
                                <th width="15%">Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($rootUnsurPenilaians as $item)
                                @include('pages.unsur-penilaian.tree_row', ['item' => $item, 'level' => 0])
                            
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                                            Belum ada data unsur penilaian.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div> <!-- end col -->
    </div> <!-- end row -->

@endsection

@push('css')
<style>
    /* Styling tambahan untuk garis hierarki opsional */
    .table td {
        vertical-align: middle;
    }
</style>
@endpush
