@extends('layouts.guru')

@section('title', 'Edit Praktikum — ' . $praktikum->title)
@section('page-title', 'Edit Praktikum')
@section('page-subtitle', 'Perbarui informasi praktikum: ' . $praktikum->title)

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('guru.praktikum.show', $praktikum) }}" class="btn btn-outline-info btn-sm">
            <i class="fas fa-eye me-1"></i>Detail
        </a>
        <a href="{{ route('guru.praktikum.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>
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

<form action="{{ route('guru.praktikum.update', $praktikum) }}"
      method="POST" id="editForm" novalidate>
    @csrf @method('PUT')

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
                               id="title" name="title"
                               value="{{ old('title', $praktikum->title) }}"
                               required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">
                            Deskripsi <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description"
                                  rows="4" required>{{ old('description', $praktikum->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-0">
                        <label for="instructions" class="form-label fw-semibold">Instruksi Detail</label>
                        <textarea class="form-control @error('instructions') is-invalid @enderror"
                                  id="instructions" name="instructions"
                                  rows="6"
                                  placeholder="Opsional">{{ old('instructions', $praktikum->instructions) }}</textarea>
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

                    <div class="mb-3">
                        <label for="subject_id" class="form-label fw-semibold">
                            Mata Pelajaran <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('subject_id') is-invalid @enderror"
                                id="subject_id" name="subject_id" required>
                            <option value="">— Pilih Mata Pelajaran —</option>
                            @foreach($classSubjects as $cs)
                                <option value="{{ $cs->subject_id }}"
                                    {{ old('subject_id', $praktikum->subject_id) == $cs->subject_id ? 'selected' : '' }}>
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

                    <div class="mb-3">
                        <label for="kelas_id" class="form-label fw-semibold">Kelas</label>
                        <select class="form-select @error('kelas_id') is-invalid @enderror"
                                id="kelas_id" name="kelas_id">
                            <option value="">— Semua Kelas —</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}"
                                    {{ old('kelas_id', $praktikum->kelas_id) == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="due_date" class="form-label fw-semibold">
                            Batas Waktu <span class="text-danger">*</span>
                        </label>
                        <input type="datetime-local"
                               class="form-control @error('due_date') is-invalid @enderror"
                               id="due_date" name="due_date"
                               value="{{ old('due_date', $praktikum->due_date?->format('Y-m-d\TH:i')) }}"
                               required>
                        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <hr class="my-3">

                    {{-- Status publikasi --}}
                    @if($praktikum->is_published)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-success">Dipublikasikan</span>
                            @if($praktikum->published_at)
                                <small class="text-muted">{{ $praktikum->published_at->format('d M Y H:i') }}</small>
                            @endif
                        </div>
                    @else
                        <div class="mb-2"><span class="badge bg-secondary">Draft</span></div>
                    @endif

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox"
                               name="publish_now" value="1" id="publish_now"
                               {{ old('publish_now', $praktikum->is_published) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="publish_now">
                            Publikasikan
                        </label>
                    </div>
                    <small class="text-muted">Hilangkan centang untuk menyembunyikan.</small>

                    <hr class="my-3">

                    <div class="text-muted small">
                        <i class="fas fa-clock me-1"></i>
                        Dibuat: {{ $praktikum->created_at->format('d M Y') }}
                        @if($praktikum->updated_at->gt($praktikum->created_at))
                            <br><i class="fas fa-edit me-1"></i>
                            Diperbarui: {{ $praktikum->updated_at->format('d M Y H:i') }}
                        @endif
                    </div>

                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-warning fw-semibold text-dark" id="submitBtn">
                    <i class="fas fa-save me-1"></i>Simpan Perubahan
                </button>
                <a href="{{ route('guru.praktikum.show', $praktikum) }}" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-eye me-1"></i>Lihat Detail
                </a>
                <a href="{{ route('guru.praktikum.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </div>
    </div>
</form>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('editForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
    });
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            const btn = document.getElementById('submitBtn');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Perubahan';
        }
    });
});
</script>
@endpush

@endsection
