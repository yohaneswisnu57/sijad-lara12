@extends('partials.layouts.app-layout')

@section('title', 'Unsur Penilaian')

@push('css')
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')

    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-right">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">SIJAD</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Master Data</a></li>
                        <li class="breadcrumb-item active">Unsur Penilaian</li>
                    </ol>
                </div>
                <h4 class="page-title">Unsur Penilaian</h4>
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
                            <h4 class="mt-0 header-title">Daftar Unsur Penilaian</h4>
                            <p class="text-muted font-13 mb-0">Manajemen data unsur penilaian untuk perhitungan angka kredit.</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('unsur-penilaian.tree') }}" class="btn btn-secondary btn-sm mr-2"><i class="fas fa-network-wired mr-2"></i>Lihat Tree View</a>
                            <a href="{{ route('unsur-penilaian.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-2"></i>Tambah Data Baru</a>
                        </div>
                    </div>



                    <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Kode</th>
                            <th>Nama Unsur</th>
                            <th width="20%">Induk</th>
                            <th width="10%">Jenis</th> <!-- Header/Detail -->
                            <th width="15%">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($unsurPenilaians as $index => $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->kode_nomor }}</td>
                                <td>{{ $item->nama_unsur }}</td>
                                <td>{{ $item->parent ? $item->parent->nama_unsur : '-' }}</td>
                                <td>
                                    @if($item->is_header)
                                        <span class="badge badge-soft-primary">Header</span>
                                    @else
                                        <span class="badge badge-soft-success">Detail</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('unsur-penilaian.edit', $item->id) }}" class="btn btn-sm btn-info mr-1"><i class="fas fa-edit"></i></a>
                                        
                                        <form action="{{ route('unsur-penilaian.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
        </div> <!-- end col -->
    </div> <!-- end row -->

@endsection

@push('scripts')
    <!-- Required datatable js -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <!-- Responsive examples -->
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#datatable').DataTable();
        });
    </script>
@endpush
