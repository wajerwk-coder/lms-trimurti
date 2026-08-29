@extends('layouts.admin')

@section('title', 'Tambah Kelas')
@section('page-title', 'Tambah Kelas')
@section('page-subtitle', 'Tambahkan kelas baru ke dalam sistem.')

@section('page-actions')
    <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@push('css')
<style>
.preview-banner {
    background: linear-gradient(135deg, #1e3a8a 0%, #4f46e5 50%, #7c3aed 100%);
    border-radius: 14px; padding: 1.5rem; position: relative; overflow: hidden;
}
.preview-banner::before {
    content:''; position:absolute; top:-40px; right:-40px;
    width:130px; height:130px; border-radius:50%; background:rgba(255,255,255,.06);
}
.grade-btn { cursor:pointer; transition:all .2s; }
.grade-btn.selected {
    background: #4f46e5 !important;
    border-color: #4f46e5 !important;
    color: #fff !important;
}
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

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.kelas.store') }}" method="POST" id="kelasForm" novalidate>
@csrf

<div class="row g-4">

    {{-- ═══ KIRI: Form ═══ --}}
    <div class="col-lg-8">

        {{-- Informasi Kelas --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="rounded-2 p-2 bg-primary bg-opacity-10 lh-1">
                        <i class="fas fa-school text-primary"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 fw-semibold">Informasi Kelas</h6>
                        <small class="text-muted">Nama, tingkat, jurusan, dan tahun ajaran</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- Nama Kelas --}}
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
                                   value="{{ old('name') }}"
                                   placeholder="Contoh: XII Keperawatan A"
                                   autocomplete="off" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-text">Nama unik kelas, seperti: X Keperawatan 1, XI Farmasi A</div>
                    </div>

                    {{-- Tingkat --}}
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">
                            Tingkat <span class="text-danger">*</span>
                        </label>
                        {{-- Hidden input yang menyimpan nilai --}}
                        <input type="hidden" name="grade" id="gradeInput" value="{{ old('grade') }}" required>
                        <div class="d-flex gap-2">
                            @foreach(['X','XI','XII'] as $g)
                            <button type="button"
                                    class="btn btn-outline-primary flex-fill fw-semibold grade-btn {{ old('grade') == $g ? 'selected' : '' }}"
                                    data-grade="{{ $g }}"
                                    onclick="selectGrade('{{ $g }}')">
                                {{ $g }}
                            </button>
                            @endforeach
                        </div>
                        @error('grade')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
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
                                        {{ old('major_id') == $j->id ? 'selected' : '' }}>
                                    {{ $j->name }}{{ $j->code ? ' ('.$j->code.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('major_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($jurusans->isEmpty())
                            <div class="form-text text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Belum ada jurusan. <a href="{{ route('admin.jurusan.create') }}">Tambah jurusan dulu</a>.
                            </div>
                        @endif
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
                                   value="{{ old('academic_year', date('Y').'/'.(date('Y')+1)) }}"
                                   placeholder="2024/2025" required>
                            @error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active"   {{ old('status','active') == 'active'   ? 'selected':'' }}>Aktif</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected':'' }}>Nonaktif</option>
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
                        <div id="previewName" class="fw-bold text-white fs-6 text-truncate">Nama Kelas</div>
                        <div id="previewMajor" class="text-white opacity-75 small"></div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span id="previewGrade" class="badge rounded-pill" style="background:rgba(255,255,255,.2);">
                        Tingkat —
                    </span>
                    <span id="previewYear" class="badge rounded-pill" style="background:rgba(255,255,255,.2);">
                        <i class="fas fa-calendar me-1"></i>—
                    </span>
                    <span id="previewStatus" class="badge rounded-pill" style="background:rgba(255,255,255,.2);">
                        <i class="fas fa-circle me-1" style="font-size:.55rem;"></i>Aktif
                    </span>
                </div>
            </div>
        </div>

        {{-- Panduan --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-info-circle me-2 text-primary"></i>Panduan Penamaan
                </h6>
            </div>
            <div class="card-body py-3">
                <ul class="list-unstyled mb-0 small">
                    @foreach([
                        'X Keperawatan 1',
                        'XI Farmasi A',
                        'XII TLM B',
                        'X Keperawatan 2',
                    ] as $contoh)
                    <li class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas fa-check-circle text-success flex-shrink-0"></i>
                        <code class="small">{{ $contoh }}</code>
                    </li>
                    @endforeach
                </ul>
                <hr class="my-2">
                <p class="text-muted small mb-0">
                    <i class="fas fa-lightbulb text-warning me-1"></i>
                    Gunakan format: <strong>Tingkat Jurusan Nomor/Huruf</strong>
                </p>
            </div>
        </div>

        {{-- Tombol --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-column gap-2">
                <button type="submit" class="btn btn-primary fw-semibold" id="submitBtn">
                    <i class="fas fa-save me-2"></i>Simpan Kelas
                </button>
                <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline-secondary">
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

    /* ── Auto-generate nama dari grade + jurusan ── */
    function autoName() {
        const grade = document.getElementById('gradeInput').value;
        const major = majorEl.options[majorEl.selectedIndex]?.dataset?.name ?? '';
        if (grade && major && !nameEl.value.trim()) {
            nameEl.value = grade + ' ' + major + ' 1';
            updatePreview();
        }
    }

    /* ── Pilih grade (tingkat) ───────────────────── */
    window.selectGrade = function (val) {
        document.getElementById('gradeInput').value = val;
        document.querySelectorAll('.grade-btn').forEach(function (btn) {
            btn.classList.toggle('selected', btn.dataset.grade === val);
        });
        autoName();
        updatePreview();
    };

    /* Restore state on back button / old() */
    const savedGrade = document.getElementById('gradeInput').value;
    if (savedGrade) selectGrade(savedGrade);

    /* ── Live Preview ───────────────────────────── */
    const nameEl   = document.getElementById('nameInput');
    const majorEl  = document.getElementById('majorSelect');
    const yearEl   = document.getElementById('yearInput');
    const statusEl = document.querySelector('select[name="status"]');

    const pName   = document.getElementById('previewName');
    const pMajor  = document.getElementById('previewMajor');
    const pGrade  = document.getElementById('previewGrade');
    const pYear   = document.getElementById('previewYear');
    const pStatus = document.getElementById('previewStatus');

    window.updatePreview = function () {
        const name   = nameEl.value.trim();
        const grade  = document.getElementById('gradeInput').value;
        const major  = majorEl.options[majorEl.selectedIndex]?.dataset?.name ?? '';
        const year   = yearEl.value.trim();
        const status = statusEl.value;

        pName.textContent  = name  || 'Nama Kelas';
        pMajor.textContent = major || '—';
        pGrade.textContent = grade ? 'Kelas ' + grade : 'Tingkat —';
        pYear.innerHTML    = '<i class="fas fa-calendar me-1"></i>' + (year || '—');
        pStatus.innerHTML  = '<i class="fas fa-circle me-1" style="font-size:.55rem;"></i>' +
                             (status === 'active' ? 'Aktif' : 'Nonaktif');
    };

    nameEl.addEventListener('input', updatePreview);
    majorEl.addEventListener('change', function () { autoName(); updatePreview(); });
    yearEl.addEventListener('input', updatePreview);
    statusEl.addEventListener('change', updatePreview);
    updatePreview();

    /* ── Submit: validasi grade + spinner ───────── */
    document.getElementById('kelasForm').addEventListener('submit', function (e) {
        const grade = document.getElementById('gradeInput').value;
        if (!grade) {
            e.preventDefault();
            // Highlight grade buttons
            document.querySelectorAll('.grade-btn').forEach(function (btn) {
                btn.classList.add('btn-outline-danger');
                btn.classList.remove('btn-outline-primary');
            });
            document.getElementById('gradeInput').closest('.col-md-4')
                .insertAdjacentHTML('beforeend',
                    '<div class="text-danger small mt-1" id="gradeError">Tingkat kelas wajib dipilih.</div>');
            return;
        }
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    });

    // Clear grade error saat grade dipilih
    const origSelectGrade = window.selectGrade;
    window.selectGrade = function (val) {
        document.querySelectorAll('.grade-btn').forEach(function (btn) {
            btn.classList.remove('btn-outline-danger');
            btn.classList.add('btn-outline-primary');
        });
        const errEl = document.getElementById('gradeError');
        if (errEl) errEl.remove();
        origSelectGrade(val);
    };

    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        const btn = document.getElementById('submitBtn');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-2"></i>Simpan Kelas';
    });

});
</script>
@endpush

@endsection