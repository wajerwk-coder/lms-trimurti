@extends('layouts.admin')

@section('title', 'Edit Siswa')
@section('page-title', 'Edit Siswa')
@section('page-subtitle', 'Perbarui data akun dan profil siswa.')

@section('page-actions')
    <a href="{{ route('admin.users.siswa') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="fas fa-exclamation-circle me-2"></i>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
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

@php $profile = $user->siswaProfile; @endphp

<form action="{{ route('admin.users.update', $user->id) }}" method="POST" id="editForm">
    @csrf @method('PUT')
    <div class="row g-4">

        {{-- Akun --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-key me-2 text-warning"></i>Informasi Akun</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                                   value="{{ old('username', $user->username) }}" required>
                            @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">NIS <span class="text-danger">*</span></label>
                            <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror"
                                   value="{{ old('nis', $profile?->nis) }}" required>
                            @error('nis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">NISN <span class="text-danger">*</span></label>
                            <input type="text" name="nisn" class="form-control @error('nisn') is-invalid @enderror"
                                   value="{{ old('nisn', $profile?->nisn) }}" required>
                            @error('nisn') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Password Baru <span class="text-muted fw-normal">(kosongkan jika tidak diubah)</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="password"
                                       class="form-control" placeholder="Min. 8 karakter">
                                <button type="button" class="btn btn-outline-secondary" id="togglePw">
                                    <i class="fas fa-eye" id="pwIcon"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Akademik --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-graduation-cap me-2 text-success"></i>Informasi Akademik</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Kelas <span class="text-danger">*</span></label>
                            <select name="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror" required>
                                <option value="">Pilih Kelas</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id }}" {{ old('kelas_id', $profile?->kelas_id) == $k->id ? 'selected' : '' }}>
                                        {{ $k->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kelas_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Jurusan <span class="text-danger">*</span></label>
                            <select name="major" class="form-select @error('major') is-invalid @enderror" required>
                                <option value="">Pilih Jurusan</option>
                                @foreach($jurusans as $j)
                                    <option value="{{ $j->name }}" {{ old('major', $profile?->major) == $j->name ? 'selected' : '' }}>
                                        {{ $j->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('major') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tahun Ajaran</label>
                            <input type="text" name="tahun_ajaran" class="form-control"
                                   value="{{ old('tahun_ajaran', $profile?->tahun_ajaran) }}" placeholder="2024/2025">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Orang Tua --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-users me-2 text-secondary"></i>Data Orang Tua / Wali</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Orang Tua / Wali</label>
                            <input type="text" name="nama_ortu" class="form-control"
                                   value="{{ old('nama_ortu', $profile?->nama_ortu) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Telepon Orang Tua</label>
                            <input type="tel" name="no_telepon_ortu" class="form-control"
                                   value="{{ old('no_telepon_ortu', $profile?->no_telepon_ortu) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-user me-2 text-primary"></i>Data Pribadi</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Nomor Telepon</label>
                            <input type="tel" name="phone" class="form-control"
                                   value="{{ old('phone', $user->phone) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control"
                                   value="{{ old('tempat_lahir', $profile?->tempat_lahir) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control"
                                   value="{{ old('tanggal_lahir', $profile?->tanggal_lahir?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="">Pilih</option>
                                <option value="L" {{ old('jenis_kelamin', $profile?->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $profile?->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $profile?->alamat) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-heartbeat me-2 text-danger"></i>Info Kesehatan</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Golongan Darah</label>
                            <select name="golongan_darah" class="form-select">
                                <option value="">Pilih</option>
                                @foreach(['A','B','AB','O'] as $gol)
                                    <option value="{{ $gol }}" {{ old('golongan_darah', $profile?->golongan_darah) == $gol ? 'selected' : '' }}>{{ $gol }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Riwayat Penyakit</label>
                            <input type="text" name="riwayat_penyakit" class="form-control"
                                   value="{{ old('riwayat_penyakit', $profile?->riwayat_penyakit) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Alergi</label>
                            <input type="text" name="alergi" class="form-control"
                                   value="{{ old('alergi', $profile?->alergi) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.users.siswa') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-warning" id="submitBtn">
                    <i class="fas fa-save me-1"></i>Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</form>

@push('js')
<script>
document.getElementById('togglePw').addEventListener('click', function () {
    const pw = document.getElementById('password');
    const ic = document.getElementById('pwIcon');
    const show = pw.type === 'password';
    pw.type = show ? 'text' : 'password';
    ic.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
});
document.getElementById('editForm').addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
});
</script>
@endpush
@endsection
