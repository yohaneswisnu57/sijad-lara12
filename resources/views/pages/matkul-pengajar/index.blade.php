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

    {{-- ── Error: dosen tidak ditemukan di SIAKAD ─────────────────────────── --}}
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

    {{-- ── Info bar: NIP → SIAKAD ID + Statistik ─────────────────────────── --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card shadow-sm text-center py-3">
                <div class="text-muted small mb-1">Total Kelas Diampu</div>
                <div class="h2 font-weight-bold text-primary mb-0">{{ $totalKelas }}</div>
                <div class="text-muted small">kelas</div>
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
                <div class="h4 font-weight-bold text-dark mb-0">{{ $nip }}</div>
                <div class="text-muted small">
                    <form action="{{ route('matkul-pengajar.refresh') }}" method="POST" class="d-inline">
                        @csrf
                        @if($periode) <input type="hidden" name="periode" value="{{ $periode }}"> @endif
                        <button type="submit" class="btn btn-xs btn-outline-secondary mt-1"
                                title="Paksa reload dari SIAKAD (hapus cache)">
                            <i class="fas fa-sync-alt mr-1"></i>Refresh Data
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Filter Periode ──────────────────────────────────────────────────── --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('matkul-pengajar.index') }}" class="d-flex align-items-center flex-wrap gap-2">
                <label class="mb-0 font-weight-bold text-muted small mr-2">Filter Semester:</label>
                <select name="periode" class="form-control form-control-sm mr-2" style="width:auto; min-width:200px"
                        onchange="this.form.submit()">
                    <option value="">— Semua Semester —</option>
                    @foreach($periodeFromApi as $kode => $label)
                        <option value="{{ $kode }}" {{ $periode == $kode ? 'selected' : '' }}>
                            {{ $label }} ({{ $kode }})
                        </option>
                    @endforeach
                </select>
                @if($periode)
                    <a href="{{ route('matkul-pengajar.index') }}" class="btn btn-sm btn-light">
                        <i class="fas fa-times mr-1"></i>Reset
                    </a>
                    <span class="badge badge-primary ml-2 px-3 py-2">
                        {{ $periodeFromApi[$periode] ?? $periode }}
                    </span>
                @endif
            </form>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- KELAS PER PERIODE (Accordion)                                       --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}

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
            @foreach($grouped as $idPeriode => $kelasPeriode)
            @php
                $periodeLabel = \App\DTOs\KelasDTO::formatPeriode($idPeriode);
                $totalSksPeriode = $kelasPeriode->sum('sks');
                $collapseId = 'collapse-' . $idPeriode;
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
                        <span>
                            <span class="badge badge-primary mr-2">{{ $kelasPeriode->count() }} kelas</span>
                            <span class="badge badge-success mr-2">{{ $totalSksPeriode }} SKS</span>
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
                                    <th width="10%">Kode MK</th>
                                    <th>Nama Mata Kuliah</th>
                                    <th width="7%" class="text-center">Kelas</th>
                                    <th width="5%" class="text-center">SKS</th>
                                    <th width="15%">Program Studi</th>
                                    <th width="5%" class="text-center">Jenjang</th>
                                    <th width="7%" class="text-center">Kuota</th>
                                    <th width="5%" class="text-center">MBKM</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kelasPeriode as $i => $kelas)
                                <tr>
                                    <td class="text-muted small align-middle">{{ $i + 1 }}</td>
                                    <td class="align-middle">
                                        <code>{{ $kelas->kodeMatKul }}</code>
                                    </td>
                                    <td class="align-middle font-weight-bold">
                                        {{ $kelas->namaMatKul }}
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-secondary">{{ $kelas->namaKelas }}</span>
                                    </td>
                                    <td class="text-center align-middle font-weight-bold text-primary">
                                        {{ $kelas->sks }}
                                    </td>
                                    <td class="align-middle">
                                        <small>{{ $kelas->programStudi ?: '—' }}</small>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-light border">{{ $kelas->jenjang ?: '—' }}</span>
                                    </td>
                                    <td class="text-center align-middle">{{ $kelas->dayaTampung ?: '—' }}</td>
                                    <td class="text-center align-middle">
                                        @if($kelas->isMbkm)
                                            <span class="badge badge-warning">MBKM</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="4" class="text-right text-muted small font-weight-bold">
                                        Total SKS Semester ini:
                                    </td>
                                    <td class="text-center font-weight-bold text-success">{{ $totalSksPeriode }}</td>
                                    <td colspan="4"></td>
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

@endsection
