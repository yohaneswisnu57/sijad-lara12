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
        <a href="{{ route('kelas-mengajar.create') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Pengajuan Manual
        </a>
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
</script>
@endpush
