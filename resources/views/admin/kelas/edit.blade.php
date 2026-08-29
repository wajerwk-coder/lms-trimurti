@extends('layouts.admin')

@section('title', 'Edit Kelas — ' . $kelas->name)
@section('page-title', 'Edit Kelas')
@section('page-subtitle', 'Perbarui data kelas: ' . $kelas->name)

@section('page-actions')
    <a href="{{ route('admin.kelas.show', $kelas->id) }}" class="btn btn-outline-info btn-sm me-1">
        <i class="fas fa-eye me-1"></i>Detail
    </a>
    <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@push('css')
<style>\
.preview-banner {
    background: linear-gradient(135deg, #1e3a8a 0%, #4f46e5 50%, #7c3aed 100%);
    border-radius: 14px; padding: 1.5rem; position: relative; overflow: hidden;
}
.preview-banner::before {
    content:''; position:absolute; top:-40px; right:-40px;
    width:130px; height:130px; border-radius:50%; background:rgba(255,255,255,.06);
}
.grade-btn { cursor:pointer; transition:all .2s; }
.grade-btn.selected { background:#4f46e5 !important; border-color:#4f46e5 !important; color:#fff !important; }
</style>
@endpush

@section('content')

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <div class="d-flex gap-2">
        <i class="fas fa-exclamation-circle mt-1 flex-shrink-0"></i>
        <div>
            <strong>{{ $errors->count() }} kesalahan:</strong>
            <ul class="mb-0 mt-1 ps-3 small">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.kelas.update', $kelas->id) }}" method="POST" id="kelasForm" novalidate>
@csrf @method('PUT')

<div class="row g-4">

    {{-- ═══ KIRI: Form ═══ --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="rounded-2 p-2 bg-warning bg-opacity-10 lh-1">
                        <i class="fas fa-edit text-warning"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 fw-semibold">Informasi Kelas</h6>
                        <small class="text-muted">Nama, tingkat, jurusan, dan tahun ajaran</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- Nama --}}
                    <div class="col-12">
                        <label class="form-label small fw-semibold">
                            Nama Kelas <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-tag text-muted"></i>
                            </span>
                            <input type="text" name="name" id="nameInput"
                                   class="form-control border-start-0 @error('name') is-invalid @enderror"
                                   value="{{ old('name', $kelas->name) }}"
                                   placeholder="Contoh: XII Keperawatan A" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Tingkat --}}
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">
                            Tingkat <span class="text-danger">*</span>
                        </label>
                        <input type="hidden" name="grade" id="gradeInput"
                               value="{{ old('grade', $kelas->grade) }}" required>
                        <div class="d-flex gap-2">
                            @foreach(['X','XI','XII'] as $g)
                            <button type="button"
                                    class="btn btn-outline-primary flex-fill fw-semibold grade-btn {{ old('grade', $kelas->grade) == $g ? 'selected' : '' }}"
                                    data-grade="{{ $g }}"
                                    onclick="selectGrade('{{ $g }}')">
                                {{ $g }}
                            </button>
                            @endforeach
                        </div>
                        @error('grade')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Jurusan --}}
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">
                            Jurusan <span class="text-danger">*</span>
                        </label>
                        <select name="major_id" id="majorSelect"
                                class="form-select @error('major_id') is-invalid @enderror" required>
                            <option value="">— Pilih Jurusan —</option>
                            @foreach($jurusans as $j)
                                <option value="{{ $j->id }}"
                                        data-name="{{ $j->name }}"
                                        {{ old('major_id', $kelas->jurusan_id ?? $kelas->major_id) == $j->id ? 'selected' : '' }}>
                                    {{ $j->name }}{{ $j->code ? ' ('.$j->code.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('major_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Tahun Ajaran --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">
                            Tahun Ajaran <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-calendar-alt text-muted"></i>
                            </span>
                            <input type="text" name="academic_year" id="yearInput"
                                   class="form-control border-start-0 @error('academic_year') is-invalid @enderror"
                                   value="{{ old('academic_year', $kelas->academic_year) }}"
                                   placeholder="2024/2025" required>
                            @error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="status" id="statusSelect" class="form-select">
                            <option value="active"   {{ old('status', $kelas->status) == 'active'   ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status', $kelas->status) == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>
    </div>{{-- /col-lg-8 --}}

    {{-- ═══ KANAN: Preview & Aksi ═══ --}}
    <div class="col-lg-4">

        {{-- Live Preview --}}
        <div class="preview-banner mb-4">
            <div class="position-relative" style="z-index:1;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-3 bg-white bg-opacity-20 d-flex align-items-center
                                justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;">
                        <i class="fas fa-school text-white fa-lg"></i>
                    </div>
                    <div class="overflow-hidden flex-grow-1">
                        <div id="previewName" class="fw-bold text-white fs-6 text-truncate">{{ $kelas->name }}</div>
                        <div id="previewMajor" class="text-white opacity-75 small">{{ $kelas->jurusan?->name ?? '—' }}</div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span id="previewGrade" class="badge rounded-pill" style="background:rgba(255,255,255,.2);">
                        Kelas {{ $kelas->grade ?? '—' }}
                    </span>
                    <span id="previewYear" class="badge rounded-pill" style="background:rgba(255,255,255,.2);">
                        <i class="fas fa-calendar me-1"></i>{{ $kelas->academic_year ?? '—' }}
                    </span>
                    <span id="previewStatus" class="badge rounded-pill" style="background:rgba(255,255,255,.2);">
                        <i class="fas fa-circle me-1" style="font-size:.55rem;"></i>
                        {{ ($kelas->status ?? 'active') === 'active' ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Info Kelas --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2 text-primary"></i>Info Kelas</h6>
            </div>
            <div class="card-body py-3">
                <div class="row g-3 text-center">
                    <div class="col-6">
                        <div class="h3 fw-bold text-primary mb-0">{{ $siswaCount }}</div>
                        <small class="text-muted">Siswa</small>
                    </div>
                    <div class="col-6">
                        <div class="h3 fw-bold text-success mb-0">{{ $kelas->grade ?? '—' }}</div>
                        <small class="text-muted">Tingkat</small>
                    </div>
                </div>
                @if($kelas->created_at)
                <hr class="my-2">
                <small class="text-muted">
                    <i class="fas fa-clock me-1"></i>Dibuat: {{ $kelas->created_at->format('d M Y') }}
                </small>
                @endif
            </div>
        </div>

        {{-- Tombol --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-column gap-2">
                <button type="submit" class="btn btn-warning fw-semibold text-dark" id="submitBtn">
                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                </button>
                <a href="{{ route('admin.kelas.show', $kelas->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </div>

    </div>{{-- /col-lg-4 --}}

</div>{{-- /row --}}
</form>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    window.selectGrade = function (val) {
        document.getElementById('gradeInput').value = val;
        document.querySelectorAll('.grade-btn').forEach(function (btn) {
            btn.classList.toggle('selected', btn.dataset.grade === val);
        });
        updatePreview();
    };

    const nameEl   = document.getElementById('nameInput');
    const majorEl  = document.getElementById('majorSelect');
    const yearEl   = document.getElementById('yearInput');
    const statusEl = document.getElementById('statusSelect');
    const pName    = document.getElementById('previewName');
    const pMajor   = document.getElementById('previewMajor');
    const pGrade   = document.getElementById('previewGrade');
    const pYear    = document.getElementById('previewYear');
    const pStatus  = document.getElementById('previewStatus');

    window.updatePreview = function () {
        const grade  = document.getElementById('gradeInput').value;
        const major  = majorEl.options[majorEl.selectedIndex]?.dataset?.name ?? '';
        pName.textContent  = nameEl.value.trim()  || 'Nama Kelas';
        pMajor.textContent = major || '—';
        pGrade.textContent = grade ? 'Kelas ' + grade : '—';
        pYear.innerHTML    = '<i class="fas fa-calendar me-1"></i>' + (yearEl.value.trim() || '—');
        pStatus.innerHTML  = '<i class="fas fa-circle me-1" style="font-size:.55rem;"></i>' +
                             (statusEl.value === 'active' ? 'Aktif' : 'Nonaktif');
    };

    nameEl.addEventListener('input', updatePreview);
    majorEl.addEventListener('change', updatePreview);
    yearEl.addEventListener('input', updatePreview);
    statusEl.addEventListener('change', updatePreview);

    document.getElementById('kelasForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    });

    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        const btn = document.getElementById('submitBtn');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-2"></i>Simpan Perubahan';
    });

});
</script>
@endpush

@endsection
