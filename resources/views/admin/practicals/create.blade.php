@extends('layouts.admin')

@section('title', 'Tambah Praktikum')
@section('page-title', 'Tambah Praktikum')
@section('page-subtitle', 'Buat praktikum baru untuk siswa.')

@section('page-actions')
    <a href="{{ route('admin.practicals.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')

{{-- Flash Message --}}
@if($errors->any())
    <div id="flashMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>{{ $errors->count() }} kesalahan dalam form:</strong>
        <ul class="mb-0 mt-1 ps-3 small">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
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

<form action="{{ route('admin.practicals.store') }}" method="POST">
    @csrf

    <div class="row g-4">
        {{-- Main Fields --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-flask me-2"></i>Informasi Praktikum</h6>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Judul Praktikum <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control rounded-3 @error('title') is-invalid @enderror"
                               id="title" name="title"
                               value="{{ old('title') }}"
                               placeholder="Masukkan judul praktikum"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control rounded-3 @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="4"
                                  placeholder="Deskripsi singkat praktikum"
                                  required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="instructions" class="form-label fw-bold">Instruksi</label>
                        <textarea class="form-control rounded-3 @error('instructions') is-invalid @enderror"
                                  id="instructions" name="instructions" rows="4"
                                  placeholder="Instruksi detail untuk siswa (opsional)">{{ old('instructions') }}</textarea>
                        @error('instructions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- Sidebar Fields --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-cog me-2"></i>Pengaturan</h6>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label for="guru_id" class="form-label fw-bold">Guru <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 @error('guru_id') is-invalid @enderror"
                                id="guru_id" name="guru_id" required>
                            <option value="">— Pilih Guru —</option>
                            @foreach($gurus as $guru)
                                <option value="{{ $guru->id }}" {{ old('guru_id') == $guru->id ? 'selected' : '' }}>
                                    {{ $guru->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('guru_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="subject_id" class="form-label fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 @error('subject_id') is-invalid @enderror"
                                id="subject_id" name="subject_id" required>
                            <option value="">— Pilih Mata Pelajaran —</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="kelas_id" class="form-label fw-bold">Kelas</label>
                        <select class="form-select rounded-3 @error('kelas_id') is-invalid @enderror"
                                id="kelas_id" name="kelas_id">
                            <option value="">— Semua Kelas —</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="due_date" class="form-label fw-bold">Batas Waktu <span class="text-danger">*</span></label>
                        <input type="datetime-local"
                               class="form-control rounded-3 @error('due_date') is-invalid @enderror"
                               id="due_date" name="due_date"
                               value="{{ old('due_date') }}"
                               required>
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   id="publish_now" name="publish_now" value="1"
                                   {{ old('publish_now') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="publish_now">
                                Publikasikan Sekarang
                            </label>
                        </div>
                        <small class="text-muted">Centang agar langsung terlihat oleh siswa.</small>
                    </div>

                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Praktikum
                </button>
                <a href="{{ route('admin.practicals.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </div>
    </div>
</form>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dueDateInput = document.getElementById('due_date');
    if (!dueDateInput.value) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        dueDateInput.value = tomorrow.toISOString().slice(0, 16);
    }

    const flash = document.getElementById('flashMessage');
    if (flash) setTimeout(() => { flash.classList.remove('show'); }, 5000);
});
</script>
@endpush

@endsection