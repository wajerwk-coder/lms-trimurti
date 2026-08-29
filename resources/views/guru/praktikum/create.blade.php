@extends('layouts.guru')

@section('title', 'Buat Praktikum Baru')
@section('page-title', 'Buat Praktikum Baru')
@section('page-subtitle', 'Tambahkan sesi praktikum untuk siswa.')

@section('page-actions')
    <a href="{{ route('guru.praktikum.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>{{ $errors->count() }} kesalahan ditemukan:</strong>
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

<form action="{{ route('guru.praktikum.store') }}" method="POST" id="praktikumForm" novalidate>
    @csrf
    <div class="row g-4">

        {{-- Kiri: Konten --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-flask me-2"></i>Informasi Praktikum</h6>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">
                            Judul Praktikum <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title') }}"
                               placeholder="Contoh: Praktikum Anatomi Dasar" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">
                            Deskripsi <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="4"
                                  placeholder="Deskripsi singkat tentang praktikum ini" required>{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-0">
                        <label for="instructions" class="form-label fw-semibold">
                            Instruksi Detail
                        </label>
                        <textarea class="form-control @error('instructions') is-invalid @enderror"
                                  id="instructions" name="instructions" rows="6"
                                  placeholder="Langkah-langkah dan instruksi detail untuk siswa (opsional)">{{ old('instructions') }}</textarea>
                        <div class="form-text">Opsional — instruksi langkah per langkah yang akan diikuti siswa.</div>
                        @error('instructions')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

                    {{-- Mata Pelajaran --}}
                    <div class="mb-3">
                        <label for="subject_id" class="form-label fw-semibold">
                            Mata Pelajaran <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('subject_id') is-invalid @enderror"
                                id="subject_id" name="subject_id" required>
                            <option value="">— Pilih Mata Pelajaran —</option>
                            @foreach($classSubjects as $cs)
                                <option value="{{ $cs->subject_id }}"
                                    {{ old('subject_id') == $cs->subject_id ? 'selected' : '' }}>
                                    {{ $cs->subject_name }}
                                    @if(!empty($cs->class_name))
                                        ({{ $cs->class_name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($classSubjects->isEmpty())
                            <div class="form-text text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Belum ada mata pelajaran yang diajar. Hubungi admin.
                            </div>
                        @endif
                    </div>

                    {{-- Kelas --}}
                    <div class="mb-3">
                        <label for="kelas_id" class="form-label fw-semibold">Kelas</label>
                        <select class="form-select @error('kelas_id') is-invalid @enderror"
                                id="kelas_id" name="kelas_id">
                            <option value="">— Semua Kelas —</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}"
                                    {{ old('kelas_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Batas Waktu --}}
                    <div class="mb-3">
                        <label for="due_date" class="form-label fw-semibold">
                            Batas Waktu <span class="text-danger">*</span>
                        </label>
                        <input type="datetime-local"
                               class="form-control @error('due_date') is-invalid @enderror"
                               id="due_date" name="due_date"
                               value="{{ old('due_date') }}" required>
                        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <hr class="my-3">

                    {{-- Publikasi --}}
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox"
                               name="publish_now" value="1" id="publish_now"
                               {{ old('publish_now') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="publish_now">
                            Publikasikan Sekarang
                        </label>
                    </div>
                    <small class="text-muted">Langsung terlihat oleh siswa.</small>

                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-warning fw-semibold text-dark" id="submitBtn">
                    <i class="fas fa-save me-1"></i>Simpan Praktikum
                </button>
                <a href="{{ route('guru.praktikum.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </div>
    </div>
</form>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Set default due_date ke besok jika belum diisi
    const dd = document.getElementById('due_date');
    if (!dd.value) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        dd.value = tomorrow.toISOString().slice(0, 16);
    }

    // Submit spinner
    document.getElementById('praktikumForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
    });

    // Restore button on back
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            const btn = document.getElementById('submitBtn');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Praktikum';
        }
    });
});
</script>
@endpush

@endsection
