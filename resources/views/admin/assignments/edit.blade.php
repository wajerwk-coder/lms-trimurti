@extends('layouts.admin')

@section('title', 'Edit Tugas')
@section('page-title', 'Edit Tugas')
@section('page-subtitle', 'Perbarui informasi tugas.')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.assignments.show', $assignment) }}" class="btn btn-outline-info btn-sm">
            <i class="fas fa-eye me-1"></i>Lihat
        </a>
        <a href="{{ route('admin.assignments.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>
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

<form action="{{ route('admin.assignments.update', $assignment) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-tasks me-2"></i>Informasi Tugas</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Judul <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title', $assignment->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3" required>{{ old('description', $assignment->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="instructions" class="form-label fw-bold">Instruksi</label>
                        <textarea class="form-control @error('instructions') is-invalid @enderror"
                                  id="instructions" name="instructions" rows="4">{{ old('instructions', $assignment->instructions) }}</textarea>
                        @error('instructions')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="attachment" class="form-label fw-bold">Ganti File Lampiran</label>
                        @if($assignment->file_url)
                            <div class="d-flex align-items-center mb-2 p-2 bg-light rounded-3">
                                <i class="fas fa-file-alt text-primary me-2"></i>
                                <span class="small">{{ $assignment->file_url }}</span>
                            </div>
                        @endif
                        <input type="file" class="form-control @error('attachment') is-invalid @enderror"
                               id="attachment" name="attachment" accept=".pdf,.doc,.docx,.ppt,.pptx">
                        <div class="form-text">Kosongkan jika tidak ingin mengubah file.</div>
                        @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-cog me-2"></i>Pengaturan</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="guru_id" class="form-label fw-bold">Guru <span class="text-danger">*</span></label>
                        <select class="form-select @error('guru_id') is-invalid @enderror"
                                id="guru_id" name="guru_id" required>
                            <option value="">— Pilih Guru —</option>
                            @foreach($gurus as $guru)
                                <option value="{{ $guru->id }}" {{ old('guru_id', $assignment->guru_id) == $guru->id ? 'selected' : '' }}>{{ $guru->name }}</option>
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
                                <option value="{{ $subject->id }}" {{ old('subject_id', $assignment->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
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
                                <option value="{{ $k->id }}" {{ old('kelas_id', $assignment->kelas_id) == $k->id ? 'selected' : '' }}>{{ $k->name }}</option>
                            @endforeach
                        </select>
                        @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="due_date" class="form-label fw-bold">Batas Waktu <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('due_date') is-invalid @enderror"
                               id="due_date" name="due_date"
                               value="{{ old('due_date', optional($assignment->due_date)->format('Y-m-d\TH:i')) }}" required>
                        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="max_score" class="form-label fw-bold">Nilai Maksimal <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('max_score') is-invalid @enderror"
                               id="max_score" name="max_score"
                               value="{{ old('max_score', $assignment->max_score) }}" min="0" max="100" required>
                        @error('max_score')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <hr class="my-2">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="allow_late" value="1" id="allow_late"
                               {{ old('allow_late', $assignment->allow_late) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="allow_late">Izinkan Terlambat</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="publish_now" value="1" id="publish_now"
                               {{ old('publish_now', $assignment->is_published) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="publish_now">
                            {{ $assignment->is_published ? 'Sudah Dipublikasikan' : 'Publikasikan' }}
                        </label>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-warning fw-semibold text-dark">
                    <i class="fas fa-save me-1"></i>Simpan Perubahan
                </button>
                <a href="{{ route('admin.assignments.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </div>
    </div>
</form>

@endsection