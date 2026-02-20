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
                <th class="text-center">Sumber</th>
                @if($showBadge ?? false) <th class="text-center">Status</th> @endif
                <th>SK Mengajar</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kelas as $i => $item)
            <tr>
                <td class="text-muted small">{{ $i + 1 }}</td>
                <td><code>{{ $item->kode_mata_kuliah }}</code></td>
                <td>
                    {{ $item->nama_mata_kuliah }}
                    @if($item->is_mbkm)
                        <span class="badge badge-warning ml-1">MBKM</span>
                    @endif
                </td>
                <td class="text-center">
                    <span class="badge badge-secondary">{{ $item->nama_kelas }}</span>
                </td>
                <td class="text-center font-weight-bold">{{ $item->sks }}</td>
                <td>
                    <span class="text-nowrap">{{ $item->periode_label ?: $item->id_periode }}</span>
                    <br><small class="text-muted">{{ $item->id_periode }}</small>
                </td>
                <td><small>{{ $item->program_studi ?: '-' }}</small></td>
                <td class="text-center">
                    @if($item->source === 'siakad')
                        <span class="badge badge-info">SIAKAD</span>
                    @else
                        <span class="badge badge-secondary">Manual</span>
                    @endif
                </td>
                @if($showBadge ?? false)
                <td class="text-center">
                    @php $badge = $item->statusBadge(); @endphp
                    <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                </td>
                @endif
                <td>
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
                <td class="text-center">
                    @if($item->catatan_admin)
                        <span class="text-danger small" title="{{ $item->catatan_admin }}">
                            <i class="fas fa-info-circle"></i> {{ Str::limit($item->catatan_admin, 30) }}
                        </span>
                    @endif
                    {{-- Batalkan hanya untuk aktif (siakad) atau pending --}}
                    @if(in_array($item->status, ['aktif', 'pending']))
                    <form action="{{ route('kelas-mengajar.destroy', $item) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-outline-danger"
                                onclick="return confirm('Batalkan klaim kelas ini?')">
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
