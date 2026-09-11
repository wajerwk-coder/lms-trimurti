@extends('layouts.guru')

@section('title', 'Buat Tugas Baru')
@section('page-title', 'Buat Tugas Baru')
@section('page-subtitle', 'Tambahkan tugas baru untuk siswa.')

@section('page-actions')
    <a href="{{ route('guru.assignments.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@push('css')
<style>
.drop-zone {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
    background: #fafafa;
}
.drop-zone:hover, .drop-zone.drag-over { border-color: #6366f1; background: #eef2ff; }
.drop-zone.has-file { border-color: #10b981; background: #f0fdf4; }
.toggle-switch .form-check-input { width: 2.5rem; height: 1.35rem; cursor: pointer; }
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

<form action="{{ route('guru.assignments.store') }}" method="POST"
      enctype="multipart/form-data" id="assignmentForm">
    @csrf

    <div class="row g-4">

        {{-- ══ KIRI: Konten Tugas ══ --}}
        <div class="col-lg-8">

            {{-- Informasi Dasar --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-2 p-2 bg-primary bg-opacity-10 lh-1">
                            <i class="fas fa-tasks text-primary"></i>
                        </span>
                        <div>
                            <h6 class="mb-0 fw-semibold">Informasi Tugas</h6>
                            <small class="text-muted">Judul, deskripsi, dan instruksi lengkap</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    {{-- Judul --}}
                    <div class="mb-4">
                        <label for="title" class="form-label fw-semibold small">
                            Judul Tugas <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control form-control-lg @error('title') is-invalid @enderror"
                               id="title" name="title"
                               value="{{ old('title') }}"
                               placeholder="Contoh: Latihan Soal Anatomi Bab 3"
                               required autofocus>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold small">
                            Deskripsi <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3"
                                  placeholder="Ringkasan singkat tentang tugas ini..." required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Instruksi --}}
                    <div class="mb-2">
                        <label for="instructions" class="form-label fw-semibold small">
                            Instruksi Detail
                            <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <textarea class="form-control @error('instructions') is-invalid @enderror"
                                  id="instructions" name="instructions" rows="5"
                                  placeholder="Tulis langkah-langkah pengerjaan, ketentuan format file, cara pengumpulan, dll.">{{ old('instructions') }}</textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Instruksi detail membantu siswa mengerjakan tugas dengan benar.
                        </div>
                        @error('instructions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- Lampiran --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-2 p-2 bg-success bg-opacity-10 lh-1">
                            <i class="fas fa-paperclip text-success"></i>
                        </span>
                        <div>
                            <h6 class="mb-0 fw-semibold">File Lampiran</h6>
                            <small class="text-muted">PDF, DOC, PPT, TXT, ZIP -- maks 20 MB</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="drop-zone" id="dropZone" onclick="document.getElementById('file').click()">
                        <div id="dropPlaceholder">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted opacity-50 mb-3"></i>
                            <p class="fw-semibold mb-1 text-muted">Klik atau seret file ke sini</p>
                            <p class="small text-muted mb-0">PDF - DOC - DOCX - PPT - PPTX - TXT - ZIP - RAR</p>
                            <p class="small text-muted">Ukuran maksimal <strong>20 MB</strong></p>
                        </div>
                        <div id="dropPreview" class="d-none">
                            <div class="d-flex align-items-center justify-content-center gap-3">
                                <span class="rounded-3 bg-success bg-opacity-10 p-2">
                                    <i class="fas fa-file-alt text-success fa-lg"></i>
                                </span>
                                <div class="text-start">
                                    <div class="fw-semibold" id="previewName">--</div>
                                    <small class="text-muted" id="previewSize">--</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="clearFile"
                                        onclick="event.stopPropagation(); clearFileInput()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <input type="file" id="file" name="file" class="d-none @error('file') is-invalid @enderror"
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.zip,.rar">
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

            {{-- Pengaturan Tugas --}}
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

                    {{-- Kelas --}}
                    <div class="mb-3">
                        <label for="class_id" class="form-label fw-semibold small">
                            Kelas <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('class_id') is-invalid @enderror"
                                id="class_id" name="class_id" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}"
                                        {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Mata Pelajaran --}}
                    <div class="mb-3">
                        <label for="subject_id" class="form-label fw-semibold small">
                            Mata Pelajaran <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('subject_id') is-invalid @enderror"
                                id="subject_id" name="subject_id" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @forelse($classSubjects ?? [] as $subj)
                                <option value="{{ $subj->subject_id }}"
                                        {{ old('subject_id') == $subj->subject_id ? 'selected' : '' }}>
                                    {{ $subj->subject_name }}
                                </option>
                            @empty
                                <option value="" disabled>Belum ada mata pelajaran</option>
                            @endforelse
                        </select>
                        @error('subject_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Batas Waktu --}}
                    <div class="mb-3">
                        <label for="deadline" class="form-label fw-semibold small">
                            Batas Waktu <span class="text-danger">*</span>
                        </label>
                        <input type="datetime-local"
                               class="form-control @error('deadline') is-invalid @enderror"
                               id="deadline" name="deadline"
                               value="{{ old('deadline') }}" required>
                        @error('deadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text" id="deadlineHint"></div>
                    </div>

                    {{-- Nilai Maksimal --}}
                    <div class="mb-3">
                        <label for="max_score" class="form-label fw-semibold small">
                            Nilai Maksimal <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number"
                                   class="form-control @error('max_score') is-invalid @enderror"
                                   id="max_score" name="max_score"
                                   value="{{ old('max_score', 100) }}"
                                   min="1" max="1000" required>
                            <span class="input-group-text text-muted small">poin</span>
                        </div>
                        @error('max_score')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-3">

                    {{-- Izinkan Terlambat --}}
                    <div class="d-flex justify-content-between align-items-center toggle-switch mb-3">
                        <div>
                            <div class="fw-semibold small">Izinkan Terlambat</div>
                            <small class="text-muted">Siswa boleh kumpul setelah deadline</small>
                        </div>
                        <div class="form-check form-switch mb-0 ms-3">
                            <input class="form-check-input" type="checkbox" name="allow_late" value="1"
                                   id="allow_late" {{ old('allow_late') ? 'checked' : '' }}>
                        </div>
                    </div>

                    {{-- Publikasikan --}}
                    <div class="d-flex justify-content-between align-items-center toggle-switch">
                        <div>
                            <div class="fw-semibold small">Publikasikan Sekarang</div>
                            <small class="text-muted">Langsung terlihat oleh siswa</small>
                        </div>
                        <div class="form-check form-switch mb-0 ms-3">
                            <input class="form-check-input" type="checkbox" name="is_published" value="1"
                                   id="is_published" {{ old('is_published', 1) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div id="publishHint" class="mt-2"></div>

                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body d-flex flex-column gap-2">
                    <button type="submit" class="btn btn-primary fw-semibold" id="submitBtn">
                        <i class="fas fa-save me-2"></i>Simpan Tugas
                    </button>
                    <a href="{{ route('guru.assignments.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Batal
                    </a>
                </div>
            </div>

            {{-- Tips --}}
            <div class="card border-0 shadow-sm border-start border-4 border-warning">
                <div class="card-body py-3">
                    <h6 class="text-warning fw-semibold small mb-2">
                        <i class="fas fa-lightbulb me-1"></i>Tips Membuat Tugas
                    </h6>
                    <ul class="small text-muted mb-0 ps-3">
                        <li class="mb-1">Judul yang spesifik memudahkan siswa menemukan tugas</li>
                        <li class="mb-1">Tulis instruksi secara jelas dan terstruktur</li>
                        <li class="mb-1">Beri tenggat waktu yang realistis (minimal 1 hari)</li>
                        <li>Lampirkan file soal jika diperlukan</li>
                    </ul>
                </div>
            </div>

        </div>

    </div>
</form>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Set default deadline ke besok pukul 23:59 ─────────────────────
    const deadlineInput = document.getElementById('deadline');
    if (!deadlineInput.value) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setHours(23, 59, 0, 0);
        const pad = n => String(n).padStart(2, '0');
        deadlineInput.value = `${tomorrow.getFullYear()}-${pad(tomorrow.getMonth()+1)}-${pad(tomorrow.getDate())}T${pad(tomorrow.getHours())}:${pad(tomorrow.getMinutes())}`;
    }
    updateDeadlineHint();

    deadlineInput.addEventListener('change', updateDeadlineHint);

    function updateDeadlineHint() {
        const hint = document.getElementById('deadlineHint');
        if (!deadlineInput.value) { hint.textContent = ''; return; }
        const d = new Date(deadlineInput.value);
        const now = new Date();
        const diffH = Math.round((d - now) / 3600000);
        if (diffH < 0) {
            hint.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Waktu sudah lewat</span>';
        } else if (diffH < 24) {
            hint.innerHTML = `<span class="text-warning"><i class="fas fa-clock me-1"></i>Sekitar ${diffH} jam lagi</span>`;
        } else {
            const diffD = Math.floor(diffH / 24);
            hint.innerHTML = `<span class="text-success"><i class="fas fa-calendar me-1"></i>${diffD} hari lagi</span>`;
        }
    }

    // ── Drop zone ──────────────────────────────────────────────────────
    const fileInput   = document.getElementById('file');
    const dropZone    = document.getElementById('dropZone');
    const placeholder = document.getElementById('dropPlaceholder');
    const preview     = document.getElementById('dropPreview');
    const nameEl      = document.getElementById('previewName');
    const sizeEl      = document.getElementById('previewSize');

    function showFile(file) {
        if (!file) return;
        if (file.size > 20 * 1024 * 1024) {
            alert('Ukuran file melebihi batas 20 MB. Silakan pilih file yang lebih kecil.');
            clearFileInput();
            return;
        }
        nameEl.textContent = file.name;
        sizeEl.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        placeholder.classList.add('d-none');
        preview.classList.remove('d-none');
        dropZone.classList.add('has-file');
    }

    fileInput.addEventListener('change', function () { showFile(this.files[0] || null); });

    ['dragenter','dragover'].forEach(ev => dropZone.addEventListener(ev, e => {
        e.preventDefault(); dropZone.classList.add('drag-over');
    }));
    ['dragleave','drop'].forEach(ev => dropZone.addEventListener(ev, e => {
        e.preventDefault(); dropZone.classList.remove('drag-over');
    }));
    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        const files = e.dataTransfer?.files;
        if (files && files[0]) {
            const dt = new DataTransfer();
            dt.items.add(files[0]);
            fileInput.files = dt.files;
            showFile(files[0]);
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

    function updatePublishHint() {
        if (publishToggle.checked) {
            publishHint.innerHTML = '<span class="badge bg-success bg-opacity-15 text-success"><i class="fas fa-eye me-1"></i>Akan langsung dipublikasikan</span>';
        } else {
            publishHint.innerHTML = '<span class="badge bg-secondary bg-opacity-15 text-secondary"><i class="fas fa-eye-slash me-1"></i>Disimpan sebagai draft</span>';
        }
    }
    publishToggle.addEventListener('change', updatePublishHint);
    updatePublishHint();

    // ── Loading state ─────────────────────────────────────────────────
    document.getElementById('assignmentForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    });

    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        const btn = document.getElementById('submitBtn');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-2"></i>Simpan Tugas';
    });

});
</script>
@endpush

@endsection
