@extends('layouts.admin')

@section('title', 'Edit Admin')
@section('page-title', 'Edit Administrator')
@section('page-subtitle', 'Perbarui data akun administrator.')

@section('page-actions')
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <div class="d-flex align-items-start gap-2">
            <i class="fas fa-exclamation-circle mt-1 flex-shrink-0"></i>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.users.update', $user->id) }}" method="POST" id="editAdminForm">
    @csrf @method('PUT')

    <div class="row g-4">

        {{-- Kiri: Form --}}
        <div class="col-lg-7">

            {{-- Akun --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-2 bg-danger bg-opacity-10 p-2">
                            <i class="fas fa-user-shield text-danger"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold">Informasi Akun</h6>
                            <small class="text-muted">Data login administrator</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                <input type="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-at text-muted"></i></span>
                                <input type="text" name="username"
                                       class="form-control @error('username') is-invalid @enderror"
                                       value="{{ old('username', $user->username) }}" required>
                                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Nomor Telepon</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-phone text-muted"></i></span>
                                <input type="tel" name="phone" class="form-control"
                                       value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Password --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-2 bg-warning bg-opacity-10 p-2">
                            <i class="fas fa-lock text-warning"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold">Ubah Password</h6>
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Password Baru</label>
                            <div class="input-group">
                                <input type="password" name="password" id="passwordInput"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Min. 8 karakter">
                                <button type="button" class="btn btn-outline-secondary" id="togglePw">
                                    <i class="fas fa-eye" id="pwIcon"></i>
                                </button>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation"
                                   class="form-control" placeholder="Ulangi password baru">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kanan: Info --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4"
                 style="background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center
                                    justify-content-center fw-bold fs-3"
                             style="width:56px;height:56px;min-width:56px;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="fw-semibold">{{ $user->name }}</div>
                            <div class="opacity-75 small">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-white bg-opacity-25 text-white">
                            <i class="fas fa-shield-alt me-1"></i>Administrator
                        </span>
                        <span class="badge bg-white bg-opacity-25 text-white">
                            <i class="fas fa-calendar me-1"></i>
                            Bergabung {{ $user->created_at->format('M Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body small text-muted">
                    <h6 class="fw-semibold text-dark mb-3"><i class="fas fa-info-circle me-2 text-info"></i>Catatan</h6>
                    <ul class="ps-3 mb-0">
                        <li class="mb-1">Email dan username harus unik di sistem</li>
                        <li class="mb-1">Kosongkan password jika tidak ingin mengubah</li>
                        @if($user->id === auth()->id())
                            <li class="text-warning fw-semibold">Ini adalah akun Anda sendiri</li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="d-flex flex-column gap-2">
                <button type="submit" class="btn btn-danger" id="submitBtn">
                    <i class="fas fa-save me-1"></i>Simpan Perubahan
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </div>
    </div>
</form>

@push('js')
<script>
document.getElementById('togglePw').addEventListener('click', function () {
    const inp  = document.getElementById('passwordInput');
    const icon = document.getElementById('pwIcon');
    const show = inp.type === 'password';
    inp.type   = show ? 'text' : 'password';
    icon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
});
document.getElementById('editAdminForm').addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
});
</script>
@endpush
@endsection
