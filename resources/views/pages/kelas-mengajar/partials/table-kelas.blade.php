<div class="table-responsive">
    <table class="table table-sm table-hover mb-0">
        <thead class="thead-light">
            <tr>
                <th>#</th>
                <th>Kode MK</th>
                {{-- Nama MK mencakup info periode & prodi di mobile --}}
                <th>Nama Mata Kuliah</th>
                <th class="text-center">Kelas</th>
                <th class="text-center">SKS</th>
                {{-- Sembunyikan di mobile: periode & prodi muncul sebagai sub-text di kolom Nama --}}
                <th class="d-none d-md-table-cell">Periode</th>
                <th class="d-none d-lg-table-cell">Program Studi</th>
                <th class="text-center d-none d-sm-table-cell">Sumber</th>
                @if($showBadge ?? false) <th class="text-center">Status</th> @endif
                <th class="d-none d-md-table-cell">SK Mengajar</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kelas as $i => $item)
            <tr>
                <td class="text-muted small align-middle">{{ $i + 1 }}</td>
                <td class="align-middle"><code style="font-size:0.78rem">{{ $item->kode_mata_kuliah }}</code></td>
                <td class="align-middle">
                    <span class="font-weight-bold">{{ $item->nama_mata_kuliah }}</span>
                    @if($item->is_mbkm)
                        <span class="badge badge-warning ml-1">MBKM</span>
                    @endif
                    {{-- Sub-text mobile: periode & prodi tampil di bawah nama MK --}}
                    <div class="d-md-none text-muted small">
                        {{ $item->periode_label ?: $item->id_periode }}
                    </div>
                    <div class="d-lg-none d-md-block d-none text-muted small">
                        {{ $item->program_studi ?: '' }}
                    </div>
                    {{-- SK download di mobile (di bawah nama MK) --}}
                    <div class="d-md-none mt-1">
                        @if($item->hasSK())
                            <a href="{{ route('kelas-mengajar.sk', $item) }}" target="_blank"
                               class="btn btn-xs btn-outline-secondary" title="{{ $item->sk_mengajar_original_name }}">
                                <i class="fas fa-file-download mr-1"></i>
                                <span class="small">SK</span>
                            </a>
                        @endif
                    </div>
                </td>
                <td class="text-center align-middle">
                    <span class="badge badge-secondary">{{ $item->nama_kelas }}</span>
                </td>
                <td class="text-center align-middle font-weight-bold">{{ $item->sks }}</td>
                <td class="align-middle d-none d-md-table-cell">
                    <span class="text-nowrap">{{ $item->periode_label ?: $item->id_periode }}</span>
                    <br><small class="text-muted">{{ $item->id_periode }}</small>
                </td>
                <td class="align-middle d-none d-lg-table-cell"><small>{{ $item->program_studi ?: '-' }}</small></td>
                <td class="text-center align-middle d-none d-sm-table-cell">
                    @if($item->source === 'siakad')
                        <span class="badge badge-info">SIAKAD</span>
                    @else
                        <span class="badge badge-secondary">Manual</span>
                    @endif
                </td>
                @if($showBadge ?? false)
                <td class="text-center align-middle">
                    @php $badge = $item->statusBadge(); @endphp
                    <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                </td>
                @endif
                <td class="align-middle d-none d-md-table-cell">
                    @if($item->hasSK())
                        <a href="{{ route('kelas-mengajar.sk', $item) }}" target="_blank"
                           class="btn btn-xs btn-outline-secondary" title="{{ $item->sk_mengajar_original_name }}">
                            <i class="fas fa-file-download mr-1"></i>
                            <span class="small">{{ Str::limit($item->sk_mengajar_original_name, 20) }}</span>
                        </a>
                    @else
                        <span class="text-muted small">—</span>
                    @endif
                </td>
                <td class="text-center align-middle">
                    @if($item->catatan_admin)
                        <span class="text-danger small d-block mb-1" title="{{ $item->catatan_admin }}">
                            <i class="fas fa-info-circle"></i>
                            <span class="d-none d-sm-inline">{{ Str::limit($item->catatan_admin, 25) }}</span>
                        </span>
                    @endif
                    @if(in_array($item->status, ['aktif', 'pending']))
                    <form action="{{ route('kelas-mengajar.destroy', $item) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-outline-danger"
                                onclick="return confirm('Batalkan klaim kelas ini?')"
                                title="Batalkan klaim">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
