@extends('layouts.admin')

@section('title', 'Tambah Mata Pelajaran')
@section('page-title', 'Tambah Mata Pelajaran')
@section('page-subtitle', 'Tambahkan mata pelajaran baru ke sistem.')

@section('page-actions')
    <a href="{{ route('admin.mata-pelajaran.index') }}" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white d-flex align-items-center">
        <i class="fas fa-plus me-2"></i>
        <h5 class="mb-0">Tambah Mata Pelajaran</h5>
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

        <form action="{{ route('admin.mata-pelajaran.store') }}" method="POST" novalidate>
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">
                        <i class="fas fa-book me-1 text-primary"></i>
                        Nama Mata Pelajaran <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="name" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
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
                           value="{{ old('code') }}"
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
                    <option value="teori"     {{ old('type') == 'teori'     ? 'selected' : '' }}>Teori</option>
                    <option value="praktikum" {{ old('type') == 'praktikum' ? 'selected' : '' }}>Praktikum</option>
                    <option value="campuran"  {{ old('type') == 'campuran'  ? 'selected' : '' }}>Campuran</option>
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
                       value="{{ old('sks', 2) }}" min="1" max="10" required>
                @error('sks')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">
                    <i class="fas fa-align-left me-1 text-primary"></i>
                    Deskripsi
                </label>
                <textarea id="description" name="description" rows="4"
                          class="form-control @error('description') is-invalid @enderror"
                          placeholder="Deskripsi mata pelajaran (opsional)">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input type="checkbox" id="is_active" name="is_active"
                           class="form-check-input @error('is_active') is-invalid @enderror"
                           value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Aktif</label>
                    @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.mata-pelajaran.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
                <div class="d-flex gap-2">
                    <button type="reset" class="btn btn-outline-warning">
                        <i class="fas fa-undo me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('name');
    const codeInput = document.getElementById('code');
    let autoCode = true;

    nameInput.addEventListener('input', function () {
        if (!autoCode) return;
        const words = this.value.trim().split(/\s+/).filter(Boolean);
        if (!words.length) return;
        const code = words.length === 1
            ? words[0].substring(0, 3).toUpperCase()
            : words.map(w => w[0]).join('').toUpperCase();
        codeInput.value = code;
    });

    codeInput.addEventListener('input', function () { autoCode = false; });
});
</script>
@endpush
@endsection