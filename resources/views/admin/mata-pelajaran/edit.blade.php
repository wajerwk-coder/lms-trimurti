@extends('layouts.admin')

@section('title', 'Edit Mata Pelajaran')
@section('page-title', 'Edit Mata Pelajaran')
@section('page-subtitle', 'Perbarui data mata pelajaran: ' . $mataPelajaran->name)

@section('page-actions')
    <a href="{{ route('admin.mata-pelajaran.index') }}" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-warning text-white d-flex align-items-center">
        <i class="fas fa-edit me-2"></i>
        <h5 class="mb-0">Edit Mata Pelajaran: {{ $mataPelajaran->name }}</h5>
    </div>

    <div class="card-body p-4">
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>{{ $errors->count() }} kesalahan:</strong>
                <ul class="mb-0 mt-2 ps-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
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

        <form action="{{ route('admin.mata-pelajaran.update', $mataPelajaran->id) }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">
                        <i class="fas fa-book me-1 text-primary"></i>
                        Nama Mata Pelajaran <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="name" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $mataPelajaran->name) }}"
                           placeholder="Contoh: Matematika" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="code" class="form-label">
                        <i class="fas fa-tag me-1 text-primary"></i>
                        Kode <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="code" name="code"
                           class="form-control @error('code') is-invalid @enderror"
                           value="{{ old('code', $mataPelajaran->code) }}"
                           placeholder="Contoh: MTK" maxlength="20" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="type" class="form-label">
                    <i class="fas fa-list me-1 text-primary"></i>
                    Jenis Mata Pelajaran <span class="text-danger">*</span>
                </label>
                <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                    <option value="">Pilih Jenis</option>
                    <option value="teori"     {{ old('type', $mataPelajaran->type) == 'teori'     ? 'selected' : '' }}>Teori</option>
                    <option value="praktikum" {{ old('type', $mataPelajaran->type) == 'praktikum' ? 'selected' : '' }}>Praktikum</option>
                    <option value="campuran"  {{ old('type', $mataPelajaran->type) == 'campuran'  ? 'selected' : '' }}>Campuran</option>
                </select>
                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="sks" class="form-label">
                    <i class="fas fa-clock me-1 text-primary"></i>
                    SKS <span class="text-danger">*</span>
                </label>
                <input type="number"
                       id="sks" name="sks"
                       class="form-control @error('sks') is-invalid @enderror"
                       value="{{ old('sks', $mataPelajaran->sks) }}" min="1" max="10" required>
                @error('sks')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">
                    <i class="fas fa-align-left me-1 text-primary"></i>
                    Deskripsi
                </label>
                <textarea id="description" name="description" rows="4"
                          class="form-control @error('description') is-invalid @enderror"
                          placeholder="Deskripsi mata pelajaran (opsional)">{{ old('description', $mataPelajaran->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input type="checkbox" id="is_active" name="is_active"
                           class="form-check-input @error('is_active') is-invalid @enderror"
                           value="1" {{ old('is_active', $mataPelajaran->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Aktif</label>
                    @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.mata-pelajaran.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.mata-pelajaran.show', $mataPelajaran->id) }}" class="btn btn-outline-info">
                        <i class="fas fa-eye me-1"></i> Lihat
                    </a>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i> Update
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const codeInput   = document.getElementById('code');
    const originalCode = codeInput.value;

    codeInput.addEventListener('change', function () {
        if (this.value !== originalCode) {
            if (!confirm('Mengubah kode mata pelajaran dapat mempengaruhi data yang terkait. Lanjutkan?')) {
                this.value = originalCode;
            }
        }
    });
});
</script>
@endpush
@endsection