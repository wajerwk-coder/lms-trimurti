@extends('layouts.admin')

@section('title', 'Edit Kriteria Penilaian')
@section('page-title', 'Edit Kriteria Penilaian')
@section('page-subtitle', 'Perbarui data kriteria penilaian.')

@section('page-actions')
    <a href="{{ route('admin.kriteria-penilaian.index') }}" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
@endsection

@section('content')
<div>
    <div class="row">
        <div class="col-12 mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.kriteria-penilaian.index') }}">Kriteria Penilaian</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card">
                <div class="card-header bg-warning text-white d-flex align-items-center">
                    <i class="fas fa-edit me-2"></i>
                    <h5 class="mb-0">Edit Kriteria Penilaian</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.kriteria-penilaian.update', $kriteriaPenilaian) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Kriteria <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                    value="{{ old('name', $kriteriaPenilaian->name) }}"
                                    class="form-control @error('name') is-invalid @enderror" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select name="kategori" class="form-select @error('kategori') is-invalid @enderror" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach($kategoriList as $val => $label)
                                        <option value="{{ $val }}"
                                            {{ old('kategori', $kriteriaPenilaian->kategori) === $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Bobot (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="weight" step="1" min="1" max="100"
                                        value="{{ old('weight', (int)$kriteriaPenilaian->weight) }}"
                                        class="form-control @error('weight') is-invalid @enderror" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('weight') <div class="text-danger small">{{ $message }}</div> @enderror
                                <small class="text-muted">Contoh: 20 untuk 20%</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mata Praktik <span class="text-danger">*</span></label>
                                <select name="mata_praktik" class="form-select @error('mata_praktik') is-invalid @enderror" required>
                                    <option value="">Pilih Mata Pelajaran</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->name }}"
                                            {{ old('mata_praktik', $kriteriaPenilaian->mata_praktik) == $subject->name ? 'selected' : '' }}>
                                            {{ $subject->name }} ({{ $subject->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('mata_praktik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tingkat Kelas <span class="text-danger">*</span></label>
                                <select name="tingkat_kelas" class="form-select @error('tingkat_kelas') is-invalid @enderror" required>
                                    <option value="">Pilih Tingkat</option>
                                    @foreach($tingkatKelasList as $val => $label)
                                        <option value="{{ $val }}"
                                            {{ old('tingkat_kelas', $kriteriaPenilaian->tingkat_kelas) === $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tingkat_kelas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="form-select">
                                    <option value="1" {{ old('is_active', $kriteriaPenilaian->is_active ? 1 : 0) == 1 ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ old('is_active', $kriteriaPenilaian->is_active ? 1 : 0) == 0 ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" rows="3"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Deskripsi singkat kriteria">{{ old('description', $kriteriaPenilaian->description) }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0">SOP Checklist <span class="text-danger">*</span></label>
                                <button type="button" id="addChecklist" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus me-1"></i>Tambah Item
                                </button>
                            </div>
                            <div id="checklistContainer" class="mt-2">
                                @php
                                    $currentChecklist = old('sop_checklist', $kriteriaPenilaian->sop_checklist ?? ['']);
                                    if (!is_array($currentChecklist)) {
                                        $currentChecklist = json_decode($currentChecklist, true) ?? [''];
                                    }
                                    if (empty($currentChecklist)) $currentChecklist = [''];
                                @endphp
                                @foreach($currentChecklist as $idx => $val)
                                    <div class="input-group mb-2 checklist-item">
                                        <span class="input-group-text">{{ $idx + 1 }}</span>
                                        <input type="text" name="sop_checklist[]" value="{{ $val }}"
                                            class="form-control"
                                            placeholder="Contoh: Melakukan cuci tangan sebelum tindakan" required>
                                        <button type="button" class="btn btn-outline-danger removeChecklist">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            @error('sop_checklist') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.kriteria-penilaian.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Kembali
                            </a>
                            <div>
                                <a href="{{ route('admin.kriteria-penilaian.show', $kriteriaPenilaian) }}" class="btn btn-info me-2">
                                    <i class="fas fa-eye me-1"></i>Lihat Detail
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-1"></i>Update
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let counter = document.querySelectorAll('.checklist-item').length;

    document.getElementById('addChecklist').addEventListener('click', function () {
        counter++;
        const container = document.getElementById('checklistContainer');
        const div = document.createElement('div');
        div.className = 'input-group mb-2 checklist-item';
        div.innerHTML = `
            <span class="input-group-text">${counter}</span>
            <input type="text" name="sop_checklist[]" class="form-control"
                placeholder="Item checklist" required>
            <button type="button" class="btn btn-outline-danger removeChecklist">
                <i class="fas fa-trash"></i>
            </button>`;
        container.appendChild(div);
        div.querySelector('.removeChecklist').addEventListener('click', removeItem);
        renumberItems();
    });

    document.querySelectorAll('.removeChecklist').forEach(btn => {
        btn.addEventListener('click', removeItem);
    });

    function removeItem() {
        const items = document.querySelectorAll('.checklist-item');
        if (items.length > 1) {
            this.closest('.checklist-item').remove();
            renumberItems();
        }
    }

    function renumberItems() {
        document.querySelectorAll('.checklist-item').forEach((item, i) => {
            item.querySelector('.input-group-text').textContent = i + 1;
        });
        counter = document.querySelectorAll('.checklist-item').length;
    }
});
</script>
@endpush
@endsection
