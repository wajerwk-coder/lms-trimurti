@extends('layouts.admin')

@section('title', 'Profil Admin')
@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Kelola informasi akun dan foto profil.')

@section('content')
<div>
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Profil Saya</h4>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="card-title mb-0">Edit Profil</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label d-block">Foto Profil Saat Ini</label>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $user->photo_url }}" alt="Foto Profil" width="96" height="96" class="rounded-circle" style="object-fit: cover;">
                            <div class="flex-grow-1">
                                <label class="form-label mb-1">Ganti Foto Profil</label>
                                <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                                <small class="text-muted d-block mt-1">Format: JPG, JPEG, PNG, atau WEBP. Maks 5 MB.</small>
                                @error('photo')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <hr>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="3">{{ old('address', $user->address) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jenis Kelamin</label>
        <select name="gender" class="form-select">
                            <option value="" {{ old('gender', $user->gender) === null ? 'selected' : '' }}>Pilih</option>
                            <option value="L" {{ old('gender', $user->gender) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('gender', $user->gender) === 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
