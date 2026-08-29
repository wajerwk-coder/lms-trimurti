@extends('layouts.guru')

@section('title', 'Edit Tugas — ' . $assignment->title)
@section('page-title', 'Edit Tugas')
@section('page-subtitle', 'Perbarui informasi tugas: ' . $assignment->title)

@section('page-actions')
    <a href="{{ route('guru.assignments.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <div class="d-flex align-items-start gap-2">
        <i class="fas fa-exclamation-triangle mt-1 flex-shrink-0"></i>
        <div>
            <strong>{{ $errors->count() }} kesalahan ditemukan:</strong>
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
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('guru.assignments.update', $assignment->id) }}"
      method="POST" enctype="multipart/form-data" id="assignmentForm">
    @csrf @method('PUT')

    <div class="row g-4">
        {{-- Kolom Kiri --}}
        <div class="col-lg-8">

            {{-- Informasi Dasar --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Informasi Dasar
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Judul Tugas <span class="text-danger">*</span></label>
                            <input type="text" name="title"
                                   class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $assignment->title) }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="subject_id"
                                    class="form-select @error('subject_id') is-invalid @enderror" required>
                                <option value="">— Pilih Mata Pelajaran —</option>
                                @foreach($classSubjects as $cs)
                                    <option value="{{ $cs->subject_id }}"
                                            {{ old('subject_id', $assignment->subject_id) == $cs->subject_id ? 'selected' : '' }}>
                                        {{ $cs->subject_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kelas</label>
                            <select name="class_id" class="form-select @error('class_id') is-invalid @enderror">
                                <option value="">— Semua Kelas —</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}"
                                            {{ old('class_id', $assignment->kelas_id) == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Batas Waktu <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="deadline"
                                   class="form-control @error('deadline') is-invalid @enderror"
                                   value="{{ old('deadline', $assignment->due_date?->format('Y-m-d\TH:i')) }}"
                                   required>
                            @error('deadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nilai Maksimal <span class="text-danger">*</span></label>
                            <input type="number" name="max_score"
                                   class="form-control @error('max_score') is-invalid @enderror"
                                   value="{{ old('max_score', $assignment->max_score) }}"
                                   min="1" max="1000" required>
                            @error('max_score')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Deskripsi & Instruksi --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-clipboard-list me-2 text-success"></i>Instruksi Tugas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror"
                                  required>{{ old('description', $assignment->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Instruksi Detail</label>
                        {{-- Toolbar --}}
                        <div class="d-flex gap-1 mb-2">
                            @foreach([['bold','B','bold'],['italic','I','italic'],['underline','U','underline'],['bullet','•','insertBullet'],['numbered','1.','insertNumber']] as [$k,$lbl,$fn])
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1"
                                    onclick="{{ $fn === 'insertBullet' || $fn === 'insertNumber' ? $fn.'()' : 'formatText(\''.$fn.'\')' }}"
                                    title="{{ $k }}">
                                <small><b>{{ $lbl }}</b></small>
                            </button>
                            @endforeach
                        </div>
                        <textarea name="instructions" id="instructions" rows="5"
                                  class="form-control @error('instructions') is-invalid @enderror">{{ old('instructions', $assignment->instructions) }}</textarea>
                        @error('instructions')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Lampiran --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-paperclip me-2 text-warning"></i>Lampiran
                    </h6>
                </div>
                <div class="card-body">
                    @if($assignment->file_url || $assignment->file)
                    <div class="d-flex align-items-center gap-2 p-2 bg-light rounded-2 mb-3 small">
                        <i class="fas fa-file text-muted"></i>
                        <span class="text-muted">File saat ini:</span>
                        <span class="text-primary">{{ $assignment->file_url ?? $assignment->file }}</span>
                    </div>
                    @endif
                    <label class="form-label small fw-semibold">Ganti File <span class="text-muted fw-normal">(opsional)</span></label>
                    <input type="file" name="file"
                           class="form-control @error('file') is-invalid @enderror"
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.zip,.rar">
                    <div class="form-text">Format: PDF, DOC, DOCX, PPT, PPTX, TXT, ZIP, RAR. Maks 20 MB. Kosongkan jika tidak ingin mengganti.</div>
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

        </div>

        {{-- Kolom Kanan --}}
        <div class="col-lg-4">

            {{-- Pengaturan --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-cog me-2 text-secondary"></i>Pengaturan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="allow_late" value="1"
                               id="allow_late"
                               {{ old('allow_late', $assignment->allow_late) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="allow_late">
                            Izinkan pengumpulan terlambat
                        </label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_published" value="1"
                               id="is_published"
                               {{ old('is_published', $assignment->is_published) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="is_published">
                            Publikasikan tugas
                        </label>
                    </div>
                </div>
            </div>

            {{-- Info Tugas --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-chart-bar me-2 text-info"></i>Statistik
                    </h6>
                </div>
                <div class="card-body small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Submission</span>
                        <span class="fw-semibold">{{ $assignment->submissions?->count() ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Sudah Dinilai</span>
                        <span class="fw-semibold text-success">
                            {{ $assignment->submissions?->whereNotNull('score')->count() ?? 0 }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Dibuat</span>
                        <span class="fw-semibold">{{ $assignment->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Tombol --}}
            <div class="d-flex flex-column gap-2">
                <button type="submit" class="btn btn-success" id="submitBtn">
                    <i class="fas fa-save me-1"></i>Simpan Perubahan
                </button>
                <a href="{{ route('guru.assignments.show', $assignment->id) }}" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-eye me-1"></i>Lihat Tugas
                </a>
                <a href="{{ route('guru.assignments.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </div>
    </div>
</form>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // File size validation
    const fileInput = document.querySelector('input[name="file"]');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            if (this.files[0] && this.files[0].size > 10 * 1024 * 1024) {
                alert('Ukuran file terlalu besar. Maksimal 10MB.');
                this.value = '';
            }
        });
    }

    // Loading on submit
    document.getElementById('assignmentForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
    });

    // Restore on back
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            const btn = document.getElementById('submitBtn');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Perubahan';
        }
    });
});

function formatText(type) {
    const ta = document.getElementById('instructions');
    const s  = ta.selectionStart, e = ta.selectionEnd;
    const sel = ta.value.substring(s, e);
    const wrap = { bold: '**', italic: '_', underline: '__' }[type] || '';
    const rep  = wrap + sel + wrap;
    ta.value = ta.value.substring(0, s) + rep + ta.value.substring(e);
    ta.focus(); ta.setSelectionRange(s + rep.length, s + rep.length);
}
function insertBullet()  { _ins('• '); }
function insertNumber()  { _ins('1. '); }
function _ins(txt) {
    const ta = document.getElementById('instructions');
    const s  = ta.selectionStart;
    ta.value = ta.value.substring(0, s) + txt + ta.value.substring(s);
    ta.focus(); ta.setSelectionRange(s + txt.length, s + txt.length);
}
</script>
@endpush
@endsection