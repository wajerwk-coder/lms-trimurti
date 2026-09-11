@extends('layouts.guru')

@section('title', 'Tambah Materi')
@section('page-title', 'Tambah Materi Pembelajaran')
@section('page-subtitle', 'Upload materi baru untuk siswa.')

@section('page-actions')
    <a href="{{ route('guru.materials.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@push('css')
<style>
.drop-zone {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
    background: #fafafa;
}
.drop-zone:hover, .drop-zone.drag-over {
    border-color: #6366f1;
    background: #eef2ff;
}
.drop-zone.has-file {
    border-color: #10b981;
    background: #f0fdf4;
}
.file-icon {
    width: 48px; height: 48px;
    border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
}
.preview-badge {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .4rem .9rem;
    border-radius: 8px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    font-size: .85rem;
}
.toggle-switch .form-check-input {
    width: 2.5rem; height: 1.35rem; cursor: pointer;
}
</style>
@endpush

@section('content')

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3" role="alert">
        <div class="d-flex gap-2">
            <i class="fas fa-exclamation-circle mt-1 flex-shrink-0"></i>
            <div>
                <strong>{{ $errors->count() }} kesalahan ditemukan:</strong>
                <ul class="mb-0 mt-1 small ps-3">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('guru.materials.store') }}" method="POST" enctype="multipart/form-data" id="materialForm">
    @csrf

    <div class="row g-4">

        {{-- ══ KIRI: Konten Materi ══ --}}
        <div class="col-lg-8">

            {{-- Informasi dasar --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-2 p-2 bg-primary bg-opacity-10 lh-1">
                            <i class="fas fa-book text-primary"></i>
                        </span>
                        <div>
                            <h6 class="mb-0 fw-semibold">Informasi Materi</h6>
                            <small class="text-muted">Judul, deskripsi, dan konten utama</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    {{-- Judul --}}
                    <div class="mb-4">
                        <label for="title" class="form-label fw-semibold small">
                            Judul Materi <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control form-control-lg @error('title') is-invalid @enderror"
                               id="title" name="title"
                               value="{{ old('title') }}"
                               placeholder="Contoh: Konsep Dasar Keperawatan Bab 1"
                               required autofocus>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Deskripsi / Konten --}}
                    <div class="mb-4">
                        <label for="content" class="form-label fw-semibold small">
                            Deskripsi / Konten
                            <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content" name="content" rows="6"
                                  placeholder="Tuliskan deskripsi singkat, tujuan pembelajaran, atau konten teks materi...">{{ old('content') }}</textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Deskripsi membantu siswa memahami isi materi sebelum mengunduh.
                        </div>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- URL Video --}}
                    <div class="mb-2">
                        <label for="video_url" class="form-label fw-semibold small">
                            Link Video
                            <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-video text-danger"></i>
                            </span>
                            <input type="url"
                                   class="form-control border-start-0 @error('video_url') is-invalid @enderror"
                                   id="video_url" name="video_url"
                                   value="{{ old('video_url') }}"
                                   placeholder="https://youtube.com/watch?v=...">
                            @error('video_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text">YouTube, Google Drive, atau platform video lain.</div>
                    </div>

                </div>
            </div>

            {{-- Upload File --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-2 p-2 bg-success bg-opacity-10 lh-1">
                            <i class="fas fa-paperclip text-success"></i>
                        </span>
                        <div>
                            <h6 class="mb-0 fw-semibold">File Lampiran</h6>
                            <small class="text-muted">PDF, DOC, PPT, XLS, ZIP — maks 40 MB</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    {{-- Drop zone --}}
                    <div class="drop-zone" id="dropZone" onclick="document.getElementById('file').click()">
                        <div id="dropPlaceholder">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted opacity-50 mb-3"></i>
                            <p class="fw-semibold mb-1 text-muted">Klik atau seret file ke sini</p>
                            <p class="small text-muted mb-0">PDF · DOC · DOCX · PPT · PPTX · XLS · XLSX · TXT · ZIP · RAR</p>
                            <p class="small text-muted">Ukuran maksimal <strong>40 MB</strong></p>
                        </div>
                        <div id="dropPreview" class="d-none">
                            <div class="d-flex align-items-center justify-content-center gap-3">
                                <span class="file-icon bg-success bg-opacity-10">
                                    <i class="fas fa-file-alt text-success"></i>
                                </span>
                                <div class="text-start">
                                    <div class="fw-semibold" id="previewName">—</div>
                                    <small class="text-muted" id="previewSize">—</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="clearFile"
                                        onclick="event.stopPropagation(); clearFileInput()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <input type="file" id="file" name="file" class="d-none @error('file') is-invalid @enderror"
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.zip,.rar">
                    @error('file')
                        <div class="text-danger small mt-2">
                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                        </div>
                    @enderror

                </div>
            </div>

        </div>

        {{-- ══ KANAN: Pengaturan ══ --}}
        <div class="col-lg-4">

            {{-- Mata Pelajaran & Kelas --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-2 p-2 bg-warning bg-opacity-10 lh-1">
                            <i class="fas fa-cog text-warning"></i>
                        </span>
                        <h6 class="mb-0 fw-semibold">Pengaturan</h6>
                    </div>
                </div>
                <div class="card-body">

                    {{-- Mata Pelajaran --}}
                    <div class="mb-3">
                        <label for="subject_id" class="form-label fw-semibold small">
                            Mata Pelajaran <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('subject_id') is-invalid @enderror"
                                id="subject_id" name="subject_id" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @forelse($classSubjects ?? [] as $cs)
                                <option value="{{ $cs->subject_id }}"
                                        {{ old('subject_id') == $cs->subject_id ? 'selected' : '' }}>
                                    {{ $cs->subject_name }}
                                    @if(!empty($cs->class_name)) · {{ $cs->class_name }} @endif
                                </option>
                            @empty
                                <option value="" disabled>Belum ada mata pelajaran</option>
                            @endforelse
                        </select>
                        @error('subject_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if(($classSubjects ?? collect())->isEmpty())
                            <div class="form-text text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Belum ada mata pelajaran yang ditugaskan. Hubungi admin.
                            </div>
                        @endif
                    </div>

                    {{-- Kelas --}}
                    @if(isset($classes) && $classes->count())
                    <div class="mb-3">
                        <label for="kelas_id" class="form-label fw-semibold small">Kelas Tujuan</label>
                        <select class="form-select" id="kelas_id" name="kelas_id">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}"
                                        {{ old('kelas_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Kosongkan agar materi terlihat oleh semua kelas.
                        </div>
                    </div>
                    @endif

                    <hr class="my-3">

                    {{-- Status Publikasi --}}
                    <div class="d-flex justify-content-between align-items-center toggle-switch">
                        <div>
                            <div class="fw-semibold small">Publikasikan Sekarang</div>
                            <small class="text-muted">Materi langsung terlihat oleh siswa</small>
                        </div>
                        <div class="form-check form-switch mb-0 ms-3">
                            <input class="form-check-input" type="checkbox"
                                   name="is_published" value="1"
                                   id="is_published"
                                   {{ old('is_published', '1') ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div id="publishHint" class="mt-2">
                        <span class="preview-badge text-success">
                            <i class="fas fa-eye"></i> Akan langsung dipublikasikan
                        </span>
                    </div>

                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex flex-column gap-2">
                    <button type="submit" class="btn btn-primary fw-semibold" id="submitBtn">
                        <i class="fas fa-save me-2"></i>Simpan Materi
                    </button>
                    <a href="{{ route('guru.materials.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Batal
                    </a>
                </div>
            </div>

            {{-- Tips --}}
            <div class="card border-0 shadow-sm mt-4 border-start border-4 border-info">
                <div class="card-body py-3">
                    <h6 class="text-info fw-semibold small mb-2">
                        <i class="fas fa-lightbulb me-1"></i>Tips Upload Materi
                    </h6>
                    <ul class="small text-muted mb-0 ps-3">
                        <li class="mb-1">Gunakan judul yang deskriptif dan jelas</li>
                        <li class="mb-1">PDF lebih disarankan untuk kompatibilitas</li>
                        <li class="mb-1">Tambahkan link video untuk materi yang butuh penjelasan visual</li>
                        <li>Pilih kelas tujuan agar materi lebih terorganisir</li>
                    </ul>
                </div>
            </div>

        </div>

    </div>
</form>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── File upload & drop zone ────────────────────────────────────────
    const fileInput   = document.getElementById('file');
    const dropZone    = document.getElementById('dropZone');
    const placeholder = document.getElementById('dropPlaceholder');
    const preview     = document.getElementById('dropPreview');
    const nameEl      = document.getElementById('previewName');
    const sizeEl      = document.getElementById('previewSize');

    function showFile(file) {
        if (!file) return;
        if (file.size > 40 * 1024 * 1024) {
            alert('Ukuran file melebihi batas 40 MB. Silakan pilih file yang lebih kecil.');
            clearFileInput();
            return;
        }
        nameEl.textContent = file.name;
        sizeEl.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        placeholder.classList.add('d-none');
        preview.classList.remove('d-none');
        dropZone.classList.add('has-file');
    }

    fileInput.addEventListener('change', function () {
        showFile(this.files[0] || null);
    });

    // Drag & drop
    ['dragenter','dragover'].forEach(ev => {
        dropZone.addEventListener(ev, function (e) {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });
    });
    ['dragleave','drop'].forEach(ev => {
        dropZone.addEventListener(ev, function (e) {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
        });
    });
    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        const dt = e.dataTransfer;
        if (dt.files && dt.files[0]) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(dt.files[0]);
            fileInput.files = dataTransfer.files;
            showFile(dt.files[0]);
        }
    });

    window.clearFileInput = function () {
        fileInput.value = '';
        placeholder.classList.remove('d-none');
        preview.classList.add('d-none');
        dropZone.classList.remove('has-file');
    };

    // ── Toggle publikasi hint ─────────────────────────────────────────
    const publishToggle = document.getElementById('is_published');
    const publishHint   = document.getElementById('publishHint');

    function updateHint() {
        if (publishToggle.checked) {
            publishHint.innerHTML = '<span class="preview-badge text-success"><i class="fas fa-eye me-1"></i>Akan langsung dipublikasikan</span>';
        } else {
            publishHint.innerHTML = '<span class="preview-badge text-secondary" style="background:#f9fafb;border-color:#e5e7eb"><i class="fas fa-eye-slash me-1"></i>Disimpan sebagai draft</span>';
        }
    }

    publishToggle.addEventListener('change', updateHint);
    updateHint();

    // ── Loading state saat submit ─────────────────────────────────────
    document.getElementById('materialForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    });

    // Restore jika back button
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        const btn = document.getElementById('submitBtn');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-2"></i>Simpan Materi';
    });

});
</script>
@endpush

@endsection
