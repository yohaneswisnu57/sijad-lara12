<tr>
    <td style="padding-left: {{ $level * 25 + 15 }}px; font-weight: {{ $item->is_header ? 'bold' : 'normal' }};">
        <i class="fas fa-{{ $item->is_header ? 'folder-open text-warning' : 'file-alt text-primary' }} mr-2"></i>
        {{ $item->kode_nomor }}
    </td>
    <td>
        <span style="padding-left: {{ $level * 10 }}px;">{{ $item->nama_unsur }}</span>
    </td>
    <td class="text-center">
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

@if($item->childrenRecursive && $item->childrenRecursive->count() > 0)
    @foreach($item->childrenRecursive as $child)
        @include('pages.unsur-penilaian.tree_row', ['item' => $child, 'level' => $level + 1])
    @endforeach
@endif
