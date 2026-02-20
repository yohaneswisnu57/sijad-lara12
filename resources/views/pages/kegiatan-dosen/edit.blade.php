@extends('partials.layouts.app-layout')

@section('title', 'Edit Kegiatan Dosen')

@section('content')

    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-right">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">SIJAD</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('kegiatan-dosen.index') }}">Kegiatan Dosen</a></li>
                        <li class="breadcrumb-item active">Edit Kegiatan</li>
                    </ol>
                </div>
                <h4 class="page-title">Edit Kegiatan Dosen</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mt-0 header-title mb-1">Form Edit Kegiatan</h4>
                    <p class="text-muted font-13 mb-4">Perbarui data kegiatan di bawah ini.</p>

                    <form action="{{ route('kegiatan-dosen.update', $kegiatanDosen->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- ============================================================ --}}
                        {{-- BAGIAN 1: UNSUR TERPILIH (Read-only info + ganti opsi)      --}}
                        {{-- ============================================================ --}}
                        <div class="card border mb-4">
                            <div class="card-header bg-primary text-white py-2">
                                <i class="fas fa-sitemap mr-2"></i> <strong>Unsur Penilaian</strong>
                            </div>
                            <div class="card-body">
                                {{-- Info unsur saat ini --}}
                                <div class="alert alert-info py-2 mb-3">
                                    <strong>Unsur saat ini:</strong>
                                    @if($kegiatanDosen->unsur)
                                        {{ $kegiatanDosen->unsur->kode_nomor }} — {{ $kegiatanDosen->unsur->nama_unsur }}
                                        @if($kegiatanDosen->unsur->parent)
                                            <small class="ml-2 text-muted">(Parent: {{ $kegiatanDosen->unsur->parent->nama_unsur }})</small>
                                        @endif
                                    @else
                                        <span class="text-muted">Tidak ditemukan</span>
                                    @endif
                                </div>

                                <p class="text-muted mb-2">Pilih ulang jika ingin mengubah butir kegiatan:</p>

                                {{-- Level 1 --}}
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label text-right font-weight-bold">
                                        <i class="fas fa-folder text-warning mr-1"></i> Unsur Utama
                                    </label>
                                    <div class="col-sm-9">
                                        <select class="form-control" id="select_root" onchange="loadChildren(this.value, 'select_level2', 'level2_group')">
                                            <option value="">-- Pilih Unsur Utama --</option>
                                            @foreach($rootUnsurs as $root)
                                                <option value="{{ $root->id }}">
                                                    {{ $root->kode_nomor }} - {{ $root->nama_unsur }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Level 2 --}}
                                <div class="form-group row d-none" id="level2_group">
                                    <label class="col-sm-3 col-form-label text-right font-weight-bold">
                                        <i class="fas fa-folder-open text-info mr-1"></i> Sub Unsur
                                    </label>
                                    <div class="col-sm-9">
                                        <select class="form-control" id="select_level2" onchange="loadChildren(this.value, 'select_level3', 'level3_group'); setUnsurId(this.value, this)">
                                            <option value="">-- Pilih Sub Unsur --</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Level 3 --}}
                                <div class="form-group row d-none" id="level3_group">
                                    <label class="col-sm-3 col-form-label text-right font-weight-bold">
                                        <i class="fas fa-file-alt text-primary mr-1"></i> Butir Kegiatan
                                    </label>
                                    <div class="col-sm-9">
                                        <select class="form-control" id="select_level3" onchange="setUnsurId(this.value, this)">
                                            <option value="">-- Pilih Butir Kegiatan --</option>
                                        </select>
                                    </div>
                                </div>

                                <input type="hidden" name="unsur_id" id="unsur_id" value="{{ $kegiatanDosen->unsur_id }}">

                                <div id="unsur_preview">
                                    <div class="alert alert-success py-2 mb-0">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <strong>Butir Dipilih:</strong> <span id="unsur_preview_text">
                                            {{ $kegiatanDosen->unsur ? $kegiatanDosen->unsur->kode_nomor . ' - ' . $kegiatanDosen->unsur->nama_unsur : '' }}
                                        </span>
                                    </div>
                                </div>
                                @error('unsur_id')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- ============================================================ --}}
                        {{-- BAGIAN 2: DETAIL KEGIATAN                                    --}}
                        {{-- ============================================================ --}}
                        <div class="card border mb-4">
                            <div class="card-header bg-secondary text-white py-2">
                                <i class="fas fa-clipboard-list mr-2"></i> <strong>Detail Kegiatan</strong>
                            </div>
                            <div class="card-body">

                                <div class="form-group row">
                                    <label for="uraian_kegiatan" class="col-sm-3 col-form-label text-right">Uraian Kegiatan</label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control" id="uraian_kegiatan" name="uraian_kegiatan" rows="3" required>{{ old('uraian_kegiatan', $kegiatanDosen->uraian_kegiatan) }}</textarea>
                                        @error('uraian_kegiatan')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="periode_semester" class="col-sm-3 col-form-label text-right">Periode Semester</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="periode_semester" name="periode_semester"
                                            value="{{ old('periode_semester', $kegiatanDosen->periode_semester) }}" required>
                                        @error('periode_semester')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="satuan_hasil" class="col-sm-3 col-form-label text-right">Satuan Hasil</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="satuan_hasil" name="satuan_hasil"
                                            value="{{ old('satuan_hasil', $kegiatanDosen->satuan_hasil) }}">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label text-right">Volume & Angka Kredit</label>
                                    <div class="col-sm-9">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="small text-muted">Volume</label>
                                                <input type="number" step="0.01" min="0" class="form-control"
                                                    id="volume" name="volume"
                                                    value="{{ old('volume', $kegiatanDosen->volume) }}" required oninput="hitungAK()">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small text-muted">AK per Satuan</label>
                                                <input type="number" step="0.001" min="0" class="form-control"
                                                    id="angka_kredit_murni" name="angka_kredit_murni"
                                                    value="{{ old('angka_kredit_murni', $kegiatanDosen->angka_kredit_murni) }}" required oninput="hitungAK()">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small text-muted">AK Pengusul (otomatis)</label>
                                                <input type="text" class="form-control bg-light font-weight-bold"
                                                    id="preview_ak" readonly value="{{ number_format($kegiatanDosen->ak_hasil_pengusul, 3) }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-primary waves-effect waves-light">
                                    <i class="fas fa-save mr-1"></i> Update Kegiatan
                                </button>
                                <a href="{{ route('kegiatan-dosen.index') }}" class="btn btn-secondary ml-2">
                                    <i class="fas fa-times mr-1"></i> Batal
                                </a>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    const unsurHierarchy = @json($rootUnsurs);

    function loadChildren(parentId, targetSelectId, targetGroupId) {
        const targetSelect = document.getElementById(targetSelectId);
        const targetGroup  = document.getElementById(targetGroupId);
        targetSelect.innerHTML = '<option value="">-- Pilih --</option>';
        targetGroup.classList.add('d-none');

        if (targetSelectId === 'select_level2') {
            document.getElementById('select_level3').innerHTML = '<option value="">-- Pilih --</option>';
            document.getElementById('level3_group').classList.add('d-none');
        }
        if (!parentId) return;

        const children = findChildren(unsurHierarchy, parseInt(parentId));
        if (children && children.length > 0) {
            children.forEach(child => {
                const opt = document.createElement('option');
                opt.value = child.id;
                opt.textContent = child.kode_nomor + ' - ' + child.nama_unsur;
                opt.dataset.isHeader = child.is_header;
                opt.dataset.hasChildren = (child.children_recursive && child.children_recursive.length > 0) ? '1' : '0';
                targetSelect.appendChild(opt);
            });
            targetGroup.classList.remove('d-none');
        } else {
            setUnsurIdDirect(parentId);
        }
    }

    function setUnsurId(value, selectEl) {
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        if (!value) return;
        const hasChildren = selectedOption.dataset.hasChildren === '1';
        const isHeader = selectedOption.dataset.isHeader === '1';
        if (!hasChildren && !isHeader) {
            setUnsurIdDirect(value, selectedOption.textContent.trim());
        } else {
            document.getElementById('unsur_id').value = '';
        }
    }

    function setUnsurIdDirect(id, label) {
        document.getElementById('unsur_id').value = id;
        if (label) {
            document.getElementById('unsur_preview_text').textContent = label;
            document.getElementById('unsur_preview').classList.remove('d-none');
        }
    }

    function findChildren(nodes, parentId) {
        for (const node of nodes) {
            if (node.id === parentId) return node.children_recursive || [];
            if (node.children_recursive && node.children_recursive.length > 0) {
                const found = findChildren(node.children_recursive, parentId);
                if (found !== null) return found;
            }
        }
        return null;
    }

    function hitungAK() {
        const v = parseFloat(document.getElementById('volume').value) || 0;
        const a = parseFloat(document.getElementById('angka_kredit_murni').value) || 0;
        document.getElementById('preview_ak').value = (v * a).toFixed(3);
    }
</script>
@endpush
