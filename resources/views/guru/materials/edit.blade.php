@extends('layouts.guru')

@section('title', 'Edit Materi — ' . $material->title)
@section('page-title', 'Edit Materi Pembelajaran')
@section('page-subtitle', 'Perbarui informasi dan konten materi.')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('guru.materials.show', $material->id) }}" class="btn btn-outline-info btn-sm">
            <i class="fas fa-eye me-1"></i>Lihat
        </a>
        <a href="{{ route('guru.materials.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>
@endsection

@section('content')

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>{{ $errors->count() }} kesalahan:</strong>
        <ul class="mb-0 mt-1 ps-3 small">
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

<form action="{{ route('guru.materials.update', $material->id) }}"
      method="POST" enctype="multipart/form-data" id="materialForm">
    @csrf @method('PUT')

    <div class="row g-4">

        {{-- Kiri: Konten --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-book me-2"></i>Informasi Materi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Judul Materi <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="title"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $material->title) }}"
                               placeholder="Judul materi" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Konten / Deskripsi</label>
                        <textarea name="content" rows="5"
                                  class="form-control @error('content') is-invalid @enderror"
                                  placeholder="Deskripsi atau konten materi (opsional)">{{ old('content', $material->content) }}</textarea>
                        @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">URL Video</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-video text-danger"></i></span>
                            <input type="url" name="video_url"
                                   class="form-control @error('video_url') is-invalid @enderror"
                                   value="{{ old('video_url', $material->video_url) }}"
                                   placeholder="https://youtube.com/watch?v=...">
                        </div>
                        @error('video_url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ganti File Materi</label>

                        @if($material->file_url)
                            <div class="d-flex align-items-center gap-2 p-2 bg-light rounded-3 mb-2">
                                <i class="fas fa-file-alt text-success"></i>
                                <span class="small fw-medium flex-grow-1">{{ $material->file_url }}</span>
                                <a href="{{ route('guru.materials.download', $material->id) }}"
                                   class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-download me-1"></i>Unduh
                                </a>
                            </div>
                        @endif

                        <input type="file" name="file"
                               class="form-control @error('file') is-invalid @enderror"
                               id="fileInput"
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.zip,.rar">
                        <div class="form-text">Format: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT, ZIP, RAR. Maks 40 MB. Kosongkan jika tidak ingin mengubah file.</div>
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div id="fileInfo" class="mt-2 d-none">
                            <div class="alert alert-success small py-2 mb-0">
                                <i class="fas fa-check-circle me-1"></i>
                                <span id="fileInfoText"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kanan: Pengaturan --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-cog me-2"></i>Pengaturan</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Mata Pelajaran <span class="text-danger">*</span>
                        </label>
                        <select name="subject_id"
                                class="form-select @error('subject_id') is-invalid @enderror" required>
                            <option value="">— Pilih Mata Pelajaran —</option>
                            @foreach($classSubjects as $cs)
                                <option value="{{ $cs->subject_id }}"
                                    {{ old('subject_id', $material->subject_id) == $cs->subject_id ? 'selected' : '' }}>
                                    {{ $cs->subject_name }}
                                    @if(!empty($cs->class_name)) ({{ $cs->class_name }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <hr class="my-3">

                    {{-- Status publikasi --}}
                    @if($material->published_at)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-success">Diterbitkan</span>
                            <small class="text-muted">{{ $material->published_at->format('d M Y H:i') }}</small>
                        </div>
                    @else
                        <div class="mb-1"><span class="badge bg-secondary">Draft</span></div>
                    @endif

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox"
                               name="is_published" value="1" id="is_published"
                               {{ old('is_published', $material->published_at ? 1 : 0) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_published">
                            Publikasikan
                        </label>
                    </div>
                    <small class="text-muted">Hilangkan centang untuk menyembunyikan dari siswa.</small>

                    <hr class="my-3">

                    <div class="text-muted small">
                        <div class="mb-1">
                            <i class="fas fa-clock me-1"></i>
                            Dibuat: {{ $material->created_at->format('d M Y H:i') }}
                        </div>
                        @if($material->updated_at->gt($material->created_at))
                        <div>
                            <i class="fas fa-edit me-1"></i>
                            Diperbarui: {{ $material->updated_at->format('d M Y H:i') }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-warning fw-semibold text-dark" id="submitBtn">
                    <i class="fas fa-save me-1"></i>Simpan Perubahan
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
    const fileInput  = document.getElementById('fileInput');
    const fileInfo   = document.getElementById('fileInfo');
    const fileText   = document.getElementById('fileInfoText');
    const form       = document.getElementById('materialForm');
    const submitBtn  = document.getElementById('submitBtn');

    fileInput.addEventListener('change', function () {
        const f = this.files[0];
        if (!f) { fileInfo.classList.add('d-none'); return; }
        if (f.size > 40 * 1024 * 1024) {
            alert('Ukuran file melebihi 40 MB.');
            this.value = '';
            fileInfo.classList.add('d-none');
            return;
        }
        fileText.textContent = f.name + ' (' + (f.size / 1024 / 1024).toFixed(2) + ' MB)';
        fileInfo.classList.remove('d-none');
    });

    form.addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
    });

    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Perubahan';
        }
    });
});
</script>
@endpush

@endsection

