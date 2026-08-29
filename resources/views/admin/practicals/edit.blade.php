@extends('layouts.admin')

@section('title', 'Edit Praktikum')
@section('page-title', 'Edit Praktikum')
@section('page-subtitle', 'Perbarui informasi praktikum.')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.practicals.show', $practical) }}" class="btn btn-outline-info btn-sm">
            <i class="fas fa-eye me-1"></i>Lihat
        </a>
        <a href="{{ route('admin.practicals.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>
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
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('admin.practicals.update', $practical) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">
        {{-- Main Fields --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-flask me-2"></i>Informasi Praktikum</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Judul Praktikum <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control rounded-3 @error('title') is-invalid @enderror"
                               id="title" name="title"
                               value="{{ old('title', $practical->title) }}"
                               placeholder="Masukkan judul praktikum" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control rounded-3 @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="4"
                                  required>{{ old('description', $practical->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="instructions" class="form-label fw-bold">Instruksi</label>
                        <textarea class="form-control rounded-3 @error('instructions') is-invalid @enderror"
                                  id="instructions" name="instructions" rows="4">{{ old('instructions', $practical->instructions) }}</textarea>
                        @error('instructions')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-cog me-2"></i>Pengaturan</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="guru_id" class="form-label fw-bold">Guru <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 @error('guru_id') is-invalid @enderror"
                                id="guru_id" name="guru_id" required>
                            <option value="">— Pilih Guru —</option>
                            @foreach($gurus as $guru)
                                <option value="{{ $guru->id }}" {{ old('guru_id', $practical->guru_id) == $guru->id ? 'selected' : '' }}>
                                    {{ $guru->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('guru_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="subject_id" class="form-label fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 @error('subject_id') is-invalid @enderror"
                                id="subject_id" name="subject_id" required>
                            <option value="">— Pilih Mata Pelajaran —</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id', $practical->subject_id) == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="kelas_id" class="form-label fw-bold">Kelas</label>
                        <select class="form-select rounded-3 @error('kelas_id') is-invalid @enderror"
                                id="kelas_id" name="kelas_id">
                            <option value="">— Semua Kelas —</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}" {{ old('kelas_id', $practical->kelas_id) == $k->id ? 'selected' : '' }}>
                                    {{ $k->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="due_date" class="form-label fw-bold">Batas Waktu <span class="text-danger">*</span></label>
                        <input type="datetime-local"
                               class="form-control rounded-3 @error('due_date') is-invalid @enderror"
                               id="due_date" name="due_date"
                               value="{{ old('due_date', optional($practical->due_date)->format('Y-m-d\TH:i')) }}"
                               required>
                        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        {{-- Status publikasi --}}
                        @if($practical->is_published)
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-success">Dipublikasikan</span>
                                @if($practical->published_at)
                                    <small class="text-muted">{{ $practical->published_at->format('d M Y H:i') }}</small>
                                @endif
                            </div>
                        @else
                            <div class="mb-1"><span class="badge bg-secondary">Draft</span></div>
                        @endif
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   id="publish_now" name="publish_now" value="1"
                                   {{ old('publish_now', $practical->is_published) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="publish_now">
                                Publikasikan
                            </label>
                        </div>
                        <small class="text-muted">Hilangkan centang untuk menyembunyikan dari siswa.</small>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-warning text-dark fw-semibold">
                    <i class="fas fa-save me-1"></i>Simpan Perubahan
                </button>
                <a href="{{ route('admin.practicals.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </div>
    </div>
</form>

@endsection