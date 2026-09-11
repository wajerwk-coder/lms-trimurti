@extends('layouts.admin')

@section('title', 'Tambah Periode Pembelajaran')
@section('page-title', 'Tambah Periode Pembelajaran')
@section('page-subtitle', 'Buat tahun ajaran dan semester baru.')

@section('page-actions')
    <a href="{{ route('admin.academic-periods.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

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

<form action="{{ route('admin.academic-periods.store') }}" method="POST" id="periodForm" novalidate>
@csrf

<div class="row g-4">

    {{-- ═══ KIRI: Form ═══ --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="rounded-2 p-2 bg-primary bg-opacity-10 lh-1">
                        <i class="fas fa-calendar-alt text-primary"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 fw-semibold">Informasi Periode</h6>
                        <small class="text-muted">Tahun ajaran, semester, dan rentang tanggal</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- Tahun Ajaran --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">
                            Tahun Ajaran <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-calendar text-muted"></i>
                            </span>
                            <input type="text" name="academic_year" id="yearInput"
                                   class="form-control border-start-0 @error('academic_year') is-invalid @enderror"
                                   value="{{ old('academic_year', $suggestedYear) }}"
                                   placeholder="2025/2026"
                                   pattern="\d{4}/\d{4}"
                                   required>
                            @error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-text">Format: YYYY/YYYY, contoh: 2025/2026</div>
                    </div>

                    {{-- Semester --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">
                            Semester <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-2">
                            <label class="flex-fill">
                                <input type="radio" name="semester" value="ganjil" class="btn-check"
                                       id="semGanjil"
                                       {{ old('semester', 'ganjil') === 'ganjil' ? 'checked' : '' }}>
                                <span class="btn btn-outline-info w-100" for="semGanjil"
                                      onclick="document.getElementById('semGanjil').checked=true; updatePreview();">
                                    <i class="fas fa-sun me-1"></i>Ganjil (1)
                                </span>
                            </label>
                            <label class="flex-fill">
                                <input type="radio" name="semester" value="genap" class="btn-check"
                                       id="semGenap"
                                       {{ old('semester') === 'genap' ? 'checked' : '' }}>
                                <span class="btn btn-outline-warning w-100" for="semGenap"
                                      onclick="document.getElementById('semGenap').checked=true; updatePreview();">
                                    <i class="fas fa-moon me-1"></i>Genap (2)
                                </span>
                            </label>
                        </div>
                        @error('semester')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Nama (auto-generate) --}}
                    <div class="col-12">
                        <label class="form-label small fw-semibold">
                            Nama Periode
                            <span class="text-muted fw-normal">(auto-generate jika dikosongkan)</span>
                        </label>
                        <input type="text" name="name" id="nameInput"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="Contoh: Semester Ganjil 2025/2026"
                               maxlength="100">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Tanggal Mulai & Selesai --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="startDateInput"
                               class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date') }}">
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="endDateInput"
                               class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date') }}">
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Keterangan --}}
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Keterangan</label>
                        <textarea name="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Catatan tambahan tentang periode ini (opsional)"
                                  maxlength="500">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Jadikan Aktif --}}
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   name="is_active" value="1" id="isActiveCheck"
                                   {{ old('is_active') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="isActiveCheck">
                                Jadikan periode ini sebagai periode aktif
                            </label>
                        </div>
                        <div class="form-text text-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Mengaktifkan periode ini akan menonaktifkan periode lain yang sedang aktif.
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ═══ KANAN: Preview & Aksi ═══ --}}
    <div class="col-lg-4">

        {{-- Preview --}}
        <div class="card border-0 shadow-sm mb-4 overflow-hidden">
            <div class="card-body p-0">
                <div id="previewBanner"
                     style="background: linear-gradient(135deg,#0f766e,#0d9488); padding:1.5rem; border-radius:0;">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-3 bg-white bg-opacity-20 d-flex align-items-center
                                    justify-content-center flex-shrink-0" style="width:48px;height:48px;">
                            <i class="fas fa-calendar-alt text-white"></i>
                        </div>
                        <div>
                            <div id="previewName" class="fw-bold text-white">Nama Periode</div>
                            <div id="previewYear" class="text-white opacity-75 small">Tahun Ajaran</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <span id="previewSem" class="badge rounded-pill"
                              style="background:rgba(255,255,255,.2);">Semester —</span>
                        <span id="previewStatus" class="badge rounded-pill"
                              style="background:rgba(255,255,255,.2);">
                            <i class="fas fa-circle me-1" style="font-size:.55rem;"></i>Nonaktif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-info-circle me-2 text-primary"></i>Informasi
                </h6>
            </div>
            <div class="card-body small">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex gap-2 mb-2">
                        <i class="fas fa-check text-success mt-1 flex-shrink-0"></i>
                        Semester <strong>Ganjil</strong> biasanya Juli – Desember
                    </li>
                    <li class="d-flex gap-2 mb-2">
                        <i class="fas fa-check text-success mt-1 flex-shrink-0"></i>
                        Semester <strong>Genap</strong> biasanya Januari – Juni
                    </li>
                    <li class="d-flex gap-2">
                        <i class="fas fa-info-circle text-info mt-1 flex-shrink-0"></i>
                        Hanya satu periode yang bisa <strong>aktif</strong> pada satu waktu
                    </li>
                </ul>
            </div>
        </div>

        {{-- Tombol --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-column gap-2">
                <button type="submit" class="btn btn-primary fw-semibold" id="submitBtn">
                    <i class="fas fa-save me-2"></i>Simpan Periode
                </button>
                <a href="{{ route('admin.academic-periods.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </div>

    </div>

</div>
</form>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const yearEl      = document.getElementById('yearInput');
    const nameEl      = document.getElementById('nameInput');
    const activeCheck = document.getElementById('isActiveCheck');

    function getSemester() {
        return document.querySelector('input[name="semester"]:checked')?.value ?? '';
    }

    function updatePreview() {
        const year = yearEl.value.trim();
        const sem  = getSemester();
        const semLabels = { ganjil: 'Semester Ganjil', genap: 'Semester Genap' };
        const semLabel  = semLabels[sem] ?? '—';
        const autoName  = (sem && year) ? semLabel + ' ' + year : 'Nama Periode';

        document.getElementById('previewName').textContent = nameEl.value.trim() || autoName;
        document.getElementById('previewYear').textContent = year || 'Tahun Ajaran';
        document.getElementById('previewSem').textContent  = semLabel;
        document.getElementById('previewStatus').innerHTML =
            '<i class="fas fa-circle me-1" style="font-size:.55rem;"></i>' +
            (activeCheck.checked ? 'Aktif' : 'Nonaktif');
    }

    window.updatePreview = updatePreview;

    yearEl.addEventListener('input', updatePreview);
    nameEl.addEventListener('input', updatePreview);
    activeCheck.addEventListener('change', updatePreview);
    document.querySelectorAll('input[name="semester"]').forEach(function(r) {
        r.addEventListener('change', updatePreview);
    });
    updatePreview();

    document.getElementById('periodForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    });

    window.addEventListener('pageshow', function(e) {
        if (!e.persisted) return;
        const btn = document.getElementById('submitBtn');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-2"></i>Simpan Periode';
    });
});
</script>
@endpush

@endsection
