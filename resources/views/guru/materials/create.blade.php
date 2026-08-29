@extends('layouts.guru')

@section('title', 'Tambah Materi')
@section('page-title', 'Tambah Materi Pembelajaran')
@section('page-subtitle', 'Upload materi baru untuk siswa.')

@section('page-actions')
    <a href="{{ route('guru.materials.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>{{ $errors->count() }} kesalahan:</strong>
        <ul class="mb-0 mt-1 small ps-3">
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

<form action="{{ route('guru.materials.store') }}" method="POST" enctype="multipart/form-data" id="materialForm">
    @csrf
    <div class="row g-4">

        {{-- Kiri: Konten --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-book me-2"></i>Informasi Materi</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">
                            Judul Materi <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title') }}"
                               placeholder="Masukkan judul materi" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label fw-semibold">Konten / Deskripsi</label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content" name="content" rows="5"
                                  placeholder="Deskripsi atau konten materi (opsional)">{{ old('content') }}</textarea>
                        @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="video_url" class="form-label fw-semibold">URL Video</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-video text-danger"></i></span>
                            <input type="url" class="form-control @error('video_url') is-invalid @enderror"
                                   id="video_url" name="video_url" value="{{ old('video_url') }}"
                                   placeholder="https://youtube.com/watch?v=...">
                        </div>
                        <div class="form-text">Opsional — link YouTube, Google Drive, dll.</div>
                        @error('video_url')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="file" class="form-label fw-semibold">File Materi</label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror"
                               id="file" name="file"
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.zip,.rar">
                        <div class="form-text">Format: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT, ZIP, RAR. Maks 40 MB.</div>
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div id="filePreview" class="mt-2 d-none">
                            <div class="d-flex align-items-center gap-2 p-2 bg-light rounded-3">
                                <i class="fas fa-file-alt text-primary"></i>
                                <span id="fileName" class="small fw-medium"></span>
                                <span id="fileSize" class="small text-muted ms-auto"></span>
                            </div>
                        </div>
                    </div>
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
                        <label for="subject_id" class="form-label fw-semibold">
                            Mata Pelajaran <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('subject_id') is-invalid @enderror"
                                id="subject_id" name="subject_id" required>
                            <option value="">— Pilih Mata Pelajaran —</option>
                            @foreach($classSubjects ?? [] as $cs)
                                <option value="{{ $cs->subject_id }}"
                                        {{ old('subject_id') == $cs->subject_id ? 'selected' : '' }}>
                                    {{ $cs->subject_name }}
                                    @if(!empty($cs->class_name)) ({{ $cs->class_name }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    @if(isset($classes) && $classes->count())
                    <div class="mb-3">
                        <label for="class_id" class="form-label fw-semibold">Kelas</label>
                        <select class="form-select" id="class_id" name="class_id">
                            <option value="">— Semua Kelas —</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <hr class="my-3">

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_published" value="1"
                               id="is_published" {{ old('is_published') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_published">
                            Publikasikan Sekarang
                        </label>
                    </div>
                    <small class="text-muted">Centang agar materi langsung terlihat oleh siswa.</small>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save me-1"></i>Simpan Materi
                </button>
                <a href="{{ route('guru.materials.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </div>
    </div>
</form>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // File preview
    const fileInput = document.getElementById('file');
    const preview   = document.getElementById('filePreview');
    const nameEl    = document.getElementById('fileName');
    const sizeEl    = document.getElementById('fileSize');

    fileInput.addEventListener('change', function () {
        if (this.files[0]) {
            const file = this.files[0];
            if (file.size > 40 * 1024 * 1024) {
                alert('Ukuran file terlalu besar. Maksimal 40MB.');
                this.value = ''; preview.classList.add('d-none'); return;
            }
            nameEl.textContent = file.name;
            sizeEl.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            preview.classList.remove('d-none');
        } else {
            preview.classList.add('d-none');
        }
    });

    // Loading state
    document.getElementById('materialForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
    });
});
</script>
@endpush

@endsection
