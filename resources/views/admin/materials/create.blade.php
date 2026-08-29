@extends('layouts.admin')

@section('title', 'Tambah Materi')
@section('page-title', 'Tambah Materi Pembelajaran')
@section('page-subtitle', 'Unggah materi baru untuk siswa.')

@section('page-actions')
    <a href="{{ route('admin.materials.index') }}" class="btn btn-outline-secondary btn-sm">
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

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('admin.materials.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-book me-2"></i>Informasi Materi</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Judul Materi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title') }}"
                               placeholder="Masukkan judul materi" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label fw-bold">Konten</label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content" name="content" rows="5"
                                  placeholder="Konten atau deskripsi materi (opsional)">{{ old('content') }}</textarea>
                        @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="video_url" class="form-label fw-bold">URL Video</label>
                        <input type="url" class="form-control @error('video_url') is-invalid @enderror"
                               id="video_url" name="video_url" value="{{ old('video_url') }}"
                               placeholder="https://youtube.com/...">
                        @error('video_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label fw-bold">File Materi</label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror"
                               id="file" name="file"
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.zip,.rar">
                        <div class="form-text">Format: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT, ZIP, RAR. Maks 40 MB.</div>
                        <div id="fileInfo" class="form-text text-success d-none"></div>
                        <div id="fileSizeError" class="text-danger small d-none">Ukuran file melebihi 40 MB.</div>
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-cog me-2"></i>Pengaturan</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="guru_id" class="form-label fw-bold">Guru <span class="text-danger">*</span></label>
                        <select class="form-select @error('guru_id') is-invalid @enderror"
                                id="guru_id" name="guru_id" required>
                            <option value="">— Pilih Guru —</option>
                            @foreach($teachers as $guru)
                                <option value="{{ $guru->id }}" {{ old('guru_id') == $guru->id ? 'selected' : '' }}>{{ $guru->name }}</option>
                            @endforeach
                        </select>
                        @error('guru_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="subject_id" class="form-label fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select class="form-select @error('subject_id') is-invalid @enderror"
                                id="subject_id" name="subject_id" required>
                            <option value="">— Pilih Mapel —</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="kelas_id" class="form-label fw-bold">Kelas</label>
                        <select class="form-select @error('kelas_id') is-invalid @enderror"
                                id="kelas_id" name="kelas_id">
                            <option value="">— Semua Kelas —</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->name }}</option>
                            @endforeach
                        </select>
                        @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <hr class="my-2">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="publish_now" value="1" id="publish_now"
                               {{ old('publish_now') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="publish_now">Publikasikan Sekarang</label>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Materi
                </button>
                <a href="{{ route('admin.materials.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </div>
    </div>
</form>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput   = document.getElementById('file');
    const fileInfo    = document.getElementById('fileInfo');
    const fileSizeErr = document.getElementById('fileSizeError');
    const form        = document.querySelector('form');
    const submitBtn   = form.querySelector('button[type="submit"]');
    const MAX_BYTES   = 40 * 1024 * 1024; // 40 MB

    fileInput.addEventListener('change', function () {
        const f = this.files[0];
        if (!f) {
            fileInfo.classList.add('d-none');
            fileSizeErr.classList.add('d-none');
            return;
        }
        if (f.size > MAX_BYTES) {
            fileSizeErr.textContent = `Ukuran file ${(f.size / 1048576).toFixed(1)} MB melebihi batas 40 MB.`;
            fileSizeErr.classList.remove('d-none');
            fileInfo.classList.add('d-none');
            this.value = '';
        } else {
            fileInfo.textContent = `${f.name}  (${(f.size / 1048576).toFixed(2)} MB)`;
            fileInfo.classList.remove('d-none');
            fileSizeErr.classList.add('d-none');
        }
    });

    form.addEventListener('submit', function (e) {
        if (!fileSizeErr.classList.contains('d-none')) { e.preventDefault(); return; }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
    });

    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Materi';
    });
});
</script>
@endpush

@endsection