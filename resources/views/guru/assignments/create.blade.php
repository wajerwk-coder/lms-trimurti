@extends('layouts.guru')

@section('title', 'Buat Tugas Baru')
@section('page-title', 'Buat Tugas Baru')
@section('page-subtitle', 'Tambahkan tugas baru untuk siswa.')

@section('page-actions')
    <a href="{{ route('guru.assignments.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Terdapat kesalahan:</strong>
        <ul class="mb-0 mt-1 small">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
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

<form action="{{ route('guru.assignments.store') }}" method="POST"
      enctype="multipart/form-data" id="assignmentForm">
    @csrf

    <div class="row g-4">

        {{-- Kiri: Konten utama --}}
        <div class="col-lg-8">

            {{-- Informasi Dasar --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Informasi Tugas</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">
                            Judul Tugas <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title"
                               value="{{ old('title') }}"
                               placeholder="Masukkan judul tugas" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">
                            Deskripsi <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3"
                                  placeholder="Deskripsi singkat tentang tugas" required>{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="instructions" class="form-label fw-semibold">Instruksi Detail</label>
                        <textarea class="form-control @error('instructions') is-invalid @enderror"
                                  id="instructions" name="instructions" rows="5"
                                  placeholder="Instruksi lengkap untuk siswa (opsional)">{{ old('instructions') }}</textarea>
                        @error('instructions')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Lampiran --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-paperclip me-2 text-primary"></i>Lampiran</h6>
                </div>
                <div class="card-body">
                    <label for="file" class="form-label fw-semibold">File Lampiran</label>
                    <input type="file" class="form-control @error('file') is-invalid @enderror"
                           id="file" name="file"
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.zip,.rar">
                    <div class="form-text">Format: PDF, DOC, DOCX, PPT, PPTX, TXT, ZIP, RAR. Maks 20MB.</div>
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Kanan: Pengaturan --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-cog me-2"></i>Pengaturan</h6>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label for="class_id" class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                        <select class="form-select @error('class_id') is-invalid @enderror"
                                id="class_id" name="class_id" required>
                            <option value="">— Pilih Kelas —</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="subject_id" class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select class="form-select @error('subject_id') is-invalid @enderror"
                                id="subject_id" name="subject_id" required>
                            <option value="">— Pilih Mata Pelajaran —</option>
                            @foreach($classSubjects ?? [] as $subj)
                                <option value="{{ $subj->subject_id }}"
                                        {{ old('subject_id') == $subj->subject_id ? 'selected' : '' }}>
                                    {{ $subj->subject_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="deadline" class="form-label fw-semibold">Batas Waktu <span class="text-danger">*</span></label>
                        <input type="datetime-local"
                               class="form-control @error('deadline') is-invalid @enderror"
                               id="deadline" name="deadline"
                               value="{{ old('deadline') }}" required>
                        @error('deadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="max_score" class="form-label fw-semibold">Nilai Maksimal <span class="text-danger">*</span></label>
                        <input type="number"
                               class="form-control @error('max_score') is-invalid @enderror"
                               id="max_score" name="max_score"
                               value="{{ old('max_score', 100) }}"
                               min="1" max="1000" required>
                        @error('max_score')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <hr class="my-3">

                    <div class="mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="allow_late" value="1"
                                   id="allow_late" {{ old('allow_late') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="allow_late">
                                Izinkan Terlambat
                            </label>
                        </div>
                        <small class="text-muted">Siswa boleh mengumpulkan setelah deadline.</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_published" value="1"
                                   id="is_published" {{ old('is_published', 1) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_published">
                                Publikasikan Sekarang
                            </label>
                        </div>
                        <small class="text-muted">Langsung terlihat oleh siswa.</small>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save me-1"></i>Simpan Tugas
                </button>
                <a href="{{ route('guru.assignments.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </div>
    </div>
</form>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Set default deadline to tomorrow
    const deadlineInput = document.getElementById('deadline');
    if (!deadlineInput.value) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        deadlineInput.value = tomorrow.toISOString().slice(0, 16);
    }

    // File size validation
    document.getElementById('file').addEventListener('change', function () {
        if (this.files[0] && this.files[0].size > 20 * 1024 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 20MB.');
            this.value = '';
        }
    });

    // Loading state on submit
    document.getElementById('assignmentForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
    });
});
</script>
@endpush

@endsection