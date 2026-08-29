@extends('layouts.siswa')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Kelola informasi akun dan data diri Anda.')

@push('css')
<style>
.profile-avatar {
    width: 90px; height: 90px; border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(124,58,237,.25);
}
.avatar-initials {
    width: 90px; height: 90px; border-radius: 50%;
    background: linear-gradient(135deg,#7c3aed,#a21caf);
    display: flex; align-items: center; justify-content: center;
    font-size: 2.2rem; font-weight: 700; color: #fff;
    border: 3px solid rgba(124,58,237,.25);
    flex-shrink: 0;
}
.info-row {
    display: flex; align-items: center; gap: .65rem;
    padding: .5rem 0; border-bottom: 1px solid #f1f5f9;
    font-size: .84rem;
}
.info-row:last-child { border-bottom: none; }
.info-icon {
    width: 28px; height: 28px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: .65rem;
}
.section-card {
    border: 1px solid #e8edf2 !important;
    border-radius: 14px !important;
    overflow: hidden;
}
.section-header {
    padding: .875rem 1.25rem;
    background: #f8fafc;
    border-bottom: 1px solid #e8edf2;
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

    {{-- ═══ KIRI: Avatar + Info ═══════════════════════════════════ --}}
    <div class="col-lg-4">

        {{-- Avatar card --}}
        <div class="card section-card shadow-sm mb-4">
            <div class="section-header">
                <h6 class="mb-0 fw-semibold" style="font-size:.88rem;">
                    <i class="fas fa-id-card me-2" style="color:#7c3aed;"></i>Foto Profil
                </h6>
            </div>
            <div class="card-body text-center py-4">
                @if($student->foto)
                    <img src="{{ asset('storage/'.$student->foto) }}"
                         alt="Foto Profil"
                         class="profile-avatar mb-3 d-block mx-auto"
                         id="avatarPreview">
                @else
                    <div class="avatar-initials d-inline-flex mb-3" id="avatarInitials">
                        {{ strtoupper(substr($user->name ?? 'S', 0, 1)) }}
                    </div>
                    <img src="" alt="Preview" class="profile-avatar mb-3 d-none mx-auto" id="avatarPreview">
                @endif

                <div class="fw-bold text-dark mb-1">{{ $user->name }}</div>
                <div class="text-muted small mb-2">{{ $user->email }}</div>
                <span class="badge fw-semibold"
                      style="background:rgba(124,58,237,.1);color:#7c3aed;border-radius:20px;font-size:.72rem;">
                    <i class="fas fa-graduation-cap me-1"></i>Siswa
                </span>

                <div class="mt-3">
                    <label for="foto" class="btn btn-sm w-100 fw-semibold"
                           style="border-radius:9px;background:rgba(124,58,237,.1);color:#7c3aed;border:1px solid rgba(124,58,237,.2);cursor:pointer;">
                        <i class="fas fa-camera me-1"></i>Ganti Foto
                    </label>
                    <input type="file" id="foto" name="foto" form="profileForm"
                           accept="image/jpeg,image/png,image/jpg"
                           class="d-none @error('foto') is-invalid @enderror"
                           onchange="previewAvatar(event)">
                    @error('foto')
                        <div class="text-danger mt-1" style="font-size:.75rem;">{{ $message }}</div>
                    @enderror
                    <div class="text-muted mt-1" style="font-size:.7rem;">JPG, PNG · maks 2 MB</div>
                </div>
            </div>
        </div>

        {{-- Info akademik --}}
        <div class="card section-card shadow-sm">
            <div class="section-header">
                <h6 class="mb-0 fw-semibold" style="font-size:.88rem;">
                    <i class="fas fa-graduation-cap me-2" style="color:#16a34a;"></i>Info Akademik
                </h6>
            </div>
            <div class="card-body px-4 py-3">
                @foreach([
                    ['fa-id-badge',     'rgba(124,58,237,.09)', '#7c3aed', 'NIS',          $student->nis ?? '—'],
                    ['fa-id-card',      'rgba(59,130,246,.09)', '#3b82f6', 'NISN',         $student->nisn ?? '—'],
                    ['fa-door-open',    'rgba(22,163,74,.09)',  '#16a34a', 'Kelas',        $student->kelas?->name ?? '—'],
                    ['fa-briefcase',    'rgba(217,119,6,.09)',  '#d97706', 'Jurusan',      $student->major ?? '—'],
                    ['fa-calendar-alt', 'rgba(8,145,178,.09)',  '#0891b2', 'Tahun Ajaran', $student->tahun_ajaran ?? '—'],
                    ['fa-circle',       $student->status === 'aktif' ? 'rgba(22,163,74,.09)' : 'rgba(220,38,38,.09)',
                                        $student->status === 'aktif' ? '#16a34a' : '#dc2626',
                                        'Status', ucfirst($student->status ?? 'aktif')],
                ] as [$ic, $ibg, $iclr, $label, $val])
                <div class="info-row">
                    <div class="info-icon" style="background:{{ $ibg }};">
                        <i class="fas {{ $ic }}" style="color:{{ $iclr }};"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:.67rem;text-transform:uppercase;letter-spacing:.04em;">{{ $label }}</div>
                        <div class="fw-semibold" style="font-size:.84rem;">{{ $val }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══ KANAN: Form ════════════════════════════════════════════ --}}
    <div class="col-lg-8">

        {{-- Form Edit Profil --}}
        <div class="card section-card shadow-sm mb-4">
            <div class="section-header">
                <h6 class="mb-0 fw-semibold" style="font-size:.88rem;">
                    <i class="fas fa-edit me-2" style="color:#0891b2;"></i>Edit Data Diri
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('siswa.profile.update') }}" method="POST"
                      enctype="multipart/form-data" id="profileForm">
                    @csrf @method('PUT')

                    <div class="row g-3">
                        {{-- Nama --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}"
                                   placeholder="Nama lengkap" required style="border-radius:8px;">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}"
                                   placeholder="email@contoh.com" required style="border-radius:8px;">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- NISN --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                NISN <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nisn" class="form-control @error('nisn') is-invalid @enderror"
                                   value="{{ old('nisn', $student->nisn) }}"
                                   placeholder="NISN" required style="border-radius:8px;">
                            @error('nisn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- No HP --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nomor HP</label>
                            <input type="tel" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror"
                                   value="{{ old('no_hp', $student->no_telepon) }}"
                                   placeholder="08xxxxxxxxxx" style="border-radius:8px;">
                            @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Jenis Kelamin --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror"
                                    style="border-radius:8px;">
                                <option value="">Pilih</option>
                                <option value="L" {{ old('jenis_kelamin', $student->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $student->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Tanggal Lahir --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir"
                                   class="form-control @error('tanggal_lahir') is-invalid @enderror"
                                   value="{{ old('tanggal_lahir', $student->tanggal_lahir?->format('Y-m-d')) }}"
                                   style="border-radius:8px;">
                            @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Tempat Lahir --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir"
                                   class="form-control"
                                   value="{{ old('tempat_lahir', $student->tempat_lahir) }}"
                                   placeholder="Kota kelahiran" style="border-radius:8px;">
                        </div>

                        {{-- Nama Orang Tua --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Orang Tua/Wali</label>
                            <input type="text" name="nama_ortu"
                                   class="form-control"
                                   value="{{ old('nama_ortu', $student->nama_ortu) }}"
                                   placeholder="Nama orang tua" style="border-radius:8px;">
                        </div>

                        {{-- No Telp Ortu --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">No. Telepon Orang Tua</label>
                            <input type="tel" name="no_telepon_ortu"
                                   class="form-control"
                                   value="{{ old('no_telepon_ortu', $student->no_telepon_ortu) }}"
                                   placeholder="08xxxxxxxxxx" style="border-radius:8px;">
                        </div>

                        {{-- Alamat --}}
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Alamat</label>
                            <textarea name="alamat" rows="3"
                                      class="form-control @error('alamat') is-invalid @enderror"
                                      placeholder="Alamat lengkap"
                                      style="border-radius:8px;resize:none;">{{ old('alamat', $student->alamat) }}</textarea>
                            @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-outline-secondary"
                                style="border-radius:9px;"
                                onclick="if(confirm('Reset semua perubahan?')) document.getElementById('profileForm').reset();">
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

        {{-- Ubah Password --}}
        <div class="card section-card shadow-sm">
            <div class="section-header">
                <h6 class="mb-0 fw-semibold" style="font-size:.88rem;">
                    <i class="fas fa-shield-alt me-2" style="color:#d97706;"></i>Keamanan Akun
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('siswa.profile.update') }}" method="POST" id="passwordForm">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Password Saat Ini</label>
                            <input type="password" name="current_password"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   placeholder="Password lama" style="border-radius:8px;">
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Password Baru</label>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Password baru" style="border-radius:8px;">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation"
                                   class="form-control"
                                   placeholder="Ulangi password baru" style="border-radius:8px;">
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between">
                        <div class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Minimal 6 karakter. Kosongkan jika tidak ingin mengubah password.
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
// Preview foto sebelum upload
function previewAvatar(event) {
    const file = event.target.files[0];
    if (!file) return;

    const maxMB = 2;
    if (file.size > maxMB * 1024 * 1024) {
        alert('Ukuran file terlalu besar. Maksimal 2 MB.');
        event.target.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        const preview    = document.getElementById('avatarPreview');
        const initials   = document.getElementById('avatarInitials');
        preview.src      = e.target.result;
        preview.classList.remove('d-none');
        if (initials) initials.classList.add('d-none');
    };
    reader.readAsDataURL(file);
}

// Spinner saat submit
document.getElementById('profileForm').addEventListener('submit', function () {
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
});

document.getElementById('passwordForm').addEventListener('submit', function () {
    const btn = document.getElementById('pwBtn');
    if (!btn) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengubah...';
});

window.addEventListener('pageshow', function (e) {
    if (!e.persisted) return;
    const s = document.getElementById('saveBtn');
    const p = document.getElementById('pwBtn');
    if (s) { s.disabled = false; s.innerHTML = '<i class="fas fa-save me-2"></i>Simpan Perubahan'; }
    if (p) { p.disabled = false; p.innerHTML = '<i class="fas fa-key me-2"></i>Ubah Password'; }
});
</script>
@endpush
