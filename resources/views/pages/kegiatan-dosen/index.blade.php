@extends('partials.layouts.app-layout')

@section('title', 'Kegiatan Dosen')

@push('css')
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')

    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-right">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">SIJAD</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Transaksi</a></li>
                        <li class="breadcrumb-item active">Kegiatan Dosen</li>
                    </ol>
                </div>
                <h4 class="page-title">Kegiatan Dosen</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h4 class="mt-0 header-title">Daftar Kegiatan Saya</h4>
                            <p class="text-muted font-13 mb-0">Data kegiatan berdasarkan unsur penilaian yang dipilih.</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('kegiatan-dosen.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> Tambah Kegiatan
                            </a>
                        </div>
                    </div>

                    {{-- Tampilkan per grup unsur root --}}
                    @forelse ($grouped as $namaUnsurRoot => $kegiatans)
                        <div class="mb-4">
                            {{-- Header Grup --}}
                            <div class="bg-light p-2 px-3 rounded mb-2 d-flex align-items-center">
                                <i class="fas fa-folder-open text-warning mr-2"></i>
                                <strong class="text-dark">{{ $namaUnsurRoot }}</strong>
                                <span class="badge badge-primary ml-2">{{ $kegiatans->count() }} kegiatan</span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover dt-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="20%">Unsur / Butir Kegiatan</th>
                                            <th>Uraian Kegiatan</th>
                                            <th width="10%">Periode</th>
                                            <th width="8%">Satuan</th>
                                            <th width="7%">Volume</th>
                                            <th width="8%">AK Murni</th>
                                            <th width="8%">AK Pengusul</th>
                                            <th width="10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($kegiatans as $k => $kegiatan)
                                            <tr>
                                                <td>{{ $k + 1 }}</td>
                                                <td>
                                                    @if($kegiatan->unsur)
                                                        @if($kegiatan->unsur->parent)
                                                            <small class="text-muted d-block">
                                                                <i class="fas fa-level-up-alt fa-rotate-90 mr-1"></i>
                                                                {{ $kegiatan->unsur->parent->kode_nomor }}
                                                            </small>
                                                        @endif
                                                        <strong>{{ $kegiatan->unsur->kode_nomor }}</strong>
                                                        <small class="d-block text-muted">{{ $kegiatan->unsur->nama_unsur }}</small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>{{ $kegiatan->uraian_kegiatan }}</td>
                                                <td>{{ $kegiatan->periode_semester }}</td>
                                                <td>{{ $kegiatan->satuan_hasil ?? '-' }}</td>
                                                <td class="text-right">{{ number_format($kegiatan->volume, 2) }}</td>
                                                <td class="text-right">{{ number_format($kegiatan->angka_kredit_murni, 3) }}</td>
                                                <td class="text-right font-weight-bold">{{ number_format($kegiatan->ak_hasil_pengusul, 3) }}</td>
                                                <td>
                                                    <a href="{{ route('kegiatan-dosen.edit', $kegiatan->id) }}" class="btn btn-xs btn-info mr-1"><i class="fas fa-edit"></i></a>
                                                    <form action="{{ route('kegiatan-dosen.destroy', $kegiatan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kegiatan ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash-alt"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-primary">
                                            <td colspan="7" class="text-right font-weight-bold">Total AK Pengusul:</td>
                                            <td class="text-right font-weight-bold">{{ number_format($kegiatans->sum('ak_hasil_pengusul'), 3) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3 d-block"></i>
                            <p class="text-muted">Belum ada data kegiatan. Klik <strong>Tambah Kegiatan</strong> untuk memulai.</p>
                        </div>
                    @endforelse

                </div>
            </div>
        </div>
    </div>

@endsection
