@extends('layouts.admin')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Kelola informasi akun dan foto profil.')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Profil</li>
@endsection

@push('css')
<style>
.profile-avatar {
    width: 90px; height: 90px; border-radius: 50%;
    object-fit: cover; border: 3px solid rgba(59,130,246,.25);
}
.avatar-initials {
    width: 90px; height: 90px; border-radius: 50%;
    background: linear-gradient(135deg,#3b82f6,#6d28d9);
    display: flex; align-items: center; justify-content: center;
    font-size: 2.2rem; font-weight: 700; color: #fff;
    border: 3px solid rgba(59,130,246,.25); flex-shrink: 0;
}
.sec-card {
    border: 1px solid #e8edf2 !important;
    border-radius: 14px !important; overflow: hidden;
}
.sec-hdr {
    padding: .875rem 1.25rem;
    background: #f8fafc; border-bottom: 1px solid #e8edf2;
}
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:12px;">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:12px;">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:12px;">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>{{ $errors->count() }} kesalahan:</strong>
    <ul class="mb-0 mt-1 ps-3 small">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    {{-- ═══ KIRI: Avatar ═══════════════════════════════════════════ --}}
    <div class="col-lg-4">
        <div class="card sec-card shadow-sm">
            <div class="sec-hdr">
                <h6 class="mb-0 fw-semibold" style="font-size:.88rem;">
                    <i class="fas fa-id-card me-2 text-primary"></i>Foto Profil
                </h6>
            </div>
            <div class="card-body text-center py-4">

                {{-- Avatar --}}
                @if($user->photo)
                    <img src="{{ asset('storage/'.$user->photo) }}"
                         alt="Avatar" class="profile-avatar d-block mx-auto mb-3"
                         id="avatarPreview">
                @else
                    <div class="avatar-initials d-inline-flex mb-3" id="avatarInitials">
                        {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
                    </div>
                    <img src="" alt="Preview"
                         class="profile-avatar d-none d-block mx-auto mb-3"
                         id="avatarPreview">
                @endif

                <div class="fw-bold text-dark mb-1">{{ $user->name }}</div>
                <div class="text-muted small mb-2">{{ $user->email }}</div>
                <span class="badge fw-semibold"
                      style="background:rgba(59,130,246,.1);color:#3b82f6;border-radius:20px;font-size:.72rem;">
                    <i class="fas fa-shield-alt me-1"></i>Administrator
                </span>

                {{-- Ganti foto --}}
                <form action="{{ route('admin.profile.update') }}" method="POST"
                      enctype="multipart/form-data" id="photoForm">
                    @csrf @method('PUT')
                    <input type="hidden" name="name"  value="{{ $user->name }}">
                    <input type="hidden" name="email" value="{{ $user->email }}">
                    <input type="hidden" name="phone" value="{{ $user->phone }}">

                    <div class="mt-3">
                        <label for="photoInput" class="btn btn-sm w-100 fw-semibold"
                               style="border-radius:9px;background:rgba(59,130,246,.1);color:#3b82f6;border:1px solid rgba(59,130,246,.2);cursor:pointer;">
                            <i class="fas fa-camera me-1"></i>Ganti Foto
                        </label>
                        <input type="file" id="photoInput" name="photo" class="d-none"
                               accept="image/jpeg,image/png,image/jpg,image/webp"
                               onchange="previewAndSubmitPhoto(event)">
                        <div class="text-muted mt-1" style="font-size:.7rem;">JPG, PNG, WEBP · maks 5 MB</div>
                        @error('photo')
                            <div class="text-danger mt-1" style="font-size:.75rem;">{{ $message }}</div>
                        @enderror
                    </div>
                </form>

                {{-- Info akun --}}
                <div class="mt-3 pt-3 border-top text-start">
                    <div class="d-flex align-items-center gap-2 mb-2" style="font-size:.82rem;">
                        <i class="fas fa-phone text-muted" style="width:16px;font-size:.75rem;"></i>
                        <span class="text-muted">{{ $user->phone ?? '—' }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2" style="font-size:.82rem;">
                        <i class="fas fa-user text-muted" style="width:16px;font-size:.75rem;"></i>
                        <span class="text-muted">{{ $user->username ?? '—' }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2" style="font-size:.82rem;">
                        <i class="fas fa-circle text-success" style="width:16px;font-size:.5rem;"></i>
                        <span class="text-muted">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ KANAN: Form ════════════════════════════════════════════ --}}
    <div class="col-lg-8">

        {{-- Edit profil --}}
        <div class="card sec-card shadow-sm mb-4">
            <div class="sec-hdr">
                <h6 class="mb-0 fw-semibold" style="font-size:.88rem;">
                    <i class="fas fa-edit me-2" style="color:#0891b2;"></i>Edit Data Diri
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.profile.update') }}" method="POST"
                      id="profileForm">
                    @csrf @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}"
                                   required style="border-radius:8px;">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}"
                                   required style="border-radius:8px;">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nomor Telepon</label>
                            <input type="tel" name="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $user->phone) }}"
                                   placeholder="08xxxxxxxxxx" style="border-radius:8px;">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Username</label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $user->username }}" readonly
                                   style="border-radius:8px;">
                            <div class="form-text" style="font-size:.72rem;">
                                Username tidak dapat diubah.
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-outline-secondary"
                                style="border-radius:9px;"
                                onclick="document.getElementById('profileForm').reset()">
                            <i class="fas fa-undo me-1"></i>Reset
                        </button>
                        <button type="submit" class="btn btn-primary fw-semibold" id="saveBtn"
                                style="border-radius:9px;">
                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Ubah password --}}
        <div class="card sec-card shadow-sm">
            <div class="sec-hdr">
                <h6 class="mb-0 fw-semibold" style="font-size:.88rem;">
                    <i class="fas fa-shield-alt me-2" style="color:#d97706;"></i>Keamanan Akun
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.profile.update') }}" method="POST" id="pwForm">
                    @csrf @method('PUT')
                    {{-- Sertakan data wajib agar validasi tidak gagal --}}
                    <input type="hidden" name="name"  value="{{ $user->name }}">
                    <input type="hidden" name="email" value="{{ $user->email }}">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Password Baru</label>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Min. 8 karakter" style="border-radius:8px;">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation"
                                   class="form-control"
                                   placeholder="Ulangi password baru" style="border-radius:8px;">
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Kosongkan jika tidak ingin mengubah password.
                        </div>
                        <button type="submit" class="btn btn-warning fw-semibold"
                                style="border-radius:9px;" id="pwBtn">
                            <i class="fas fa-key me-2"></i>Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection

@push('js')
<script>
// Preview avatar + auto-submit saat pilih foto
function previewAndSubmitPhoto(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
        alert('Ukuran file terlalu besar. Maksimal 5 MB.');
        event.target.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const preview  = document.getElementById('avatarPreview');
        const initials = document.getElementById('avatarInitials');
        preview.src = e.target.result;
        preview.classList.remove('d-none');
        if (initials) initials.classList.add('d-none');
        // Submit form foto
        document.getElementById('photoForm').submit();
    };
    reader.readAsDataURL(file);
}

// Spinner saat simpan profil
document.getElementById('profileForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('saveBtn');
    if (!btn) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
});

// Validasi + spinner saat ubah password
document.getElementById('pwForm')?.addEventListener('submit', function(e) {
    const pw  = this.querySelector('[name=password]').value;
    const pwc = this.querySelector('[name=password_confirmation]').value;
    if (!pw) {
        e.preventDefault();
        alert('Password baru wajib diisi.');
        return;
    }
    if (pw !== pwc) {
        e.preventDefault();
        alert('Konfirmasi password tidak cocok.');
        return;
    }
    const btn = document.getElementById('pwBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengubah...';
    }
});

// Reset spinner saat navigasi back/forward di HP
window.addEventListener('pageshow', function(e) {
    if (!e.persisted) return;
    const s = document.getElementById('saveBtn');
    const p = document.getElementById('pwBtn');
    if (s) { s.disabled = false; s.innerHTML = '<i class="fas fa-save me-2"></i>Simpan Perubahan'; }
    if (p) { p.disabled = false; p.innerHTML = '<i class="fas fa-key me-2"></i>Ubah Password'; }
});
</script>
@endpush
