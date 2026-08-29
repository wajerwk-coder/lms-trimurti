@extends('layouts.guru')

@section('title', 'Profil Saya')
@section('guru-page-title', 'Profil Saya')
@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Kelola informasi akun dan profil Anda')

@section('content')

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

<div class="row g-4">

    {{-- Sidebar Info --}}
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-4">
                @if($user->photo)
                    <img src="{{ asset('storage/' . $user->photo) }}"
                         alt="{{ $user->name }}"
                         class="rounded-circle border mb-3"
                         style="width:90px;height:90px;object-fit:cover;">
                @else
                    <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center mb-3 border"
                         style="width:90px;height:90px;font-size:2rem;color:#fff;">
                        {{ strtoupper(substr($user->name ?? 'G', 0, 1)) }}
                    </div>
                @endif

                <h6 class="fw-semibold mb-0">{{ $user->name }}</h6>
                <small class="text-muted d-block mb-2">{{ $user->email }}</small>
                <span class="badge bg-success bg-opacity-10 text-success">
                    <i class="fas fa-chalkboard-teacher me-1"></i>Guru
                </span>

                <hr class="my-3">

                {{-- Upload foto --}}
                <form action="{{ route('guru.profile.update-photo') }}" method="POST"
                      enctype="multipart/form-data" id="photoForm">
                    @csrf
                    <label class="btn btn-outline-primary btn-sm w-100 mb-2" for="fotoInput">
                        <i class="fas fa-camera me-1"></i>Ganti Foto
                    </label>
                    <input type="file" id="fotoInput" name="foto" class="d-none"
                           accept="image/*" onchange="submitPhoto()">
                </form>

                @if($user->photo)
                    <form action="{{ route('guru.profile.remove-photo') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                                onclick="return confirm('Hapus foto profil?')">
                            <i class="fas fa-trash me-1"></i>Hapus Foto
                        </button>
                    </form>
                @endif

                <div class="mt-3 text-start small text-muted">
                    <div><i class="fas fa-calendar me-1"></i>Bergabung: {{ $user->created_at->format('M Y') }}</div>
                    @if($guruProfile?->nip)
                        <div class="mt-1"><i class="fas fa-id-badge me-1"></i>NIP: {{ $guruProfile->nip }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Ubah Password --}}
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="fas fa-shield-alt me-2 text-warning"></i>Keamanan</h6>
                <a href="{{ route('guru.profile.change-password') }}" class="btn btn-outline-warning btn-sm w-100">
                    <i class="fas fa-key me-1"></i>Ubah Password
                </a>
            </div>
        </div>
    </div>

    {{-- Form Utama --}}
    <div class="col-lg-9">

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i><strong>Terdapat kesalahan:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('guru.profile.update') }}" method="POST" id="profileForm">
            @csrf @method('PUT')

            {{-- Akun --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-user me-2 text-primary"></i>Informasi Akun</h6>
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
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nomor Telepon</label>
                            <input type="tel" name="phone" class="form-control"
                                   value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">NIP</label>
                            <input type="text" name="nip" class="form-control"
                                   value="{{ old('nip', $guruProfile?->nip) }}" placeholder="NIP guru">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Data Pribadi --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2 text-info"></i>Data Pribadi</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control"
                                   value="{{ old('tempat_lahir', $guruProfile?->tempat_lahir) }}" placeholder="Kota kelahiran">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control"
                                   value="{{ old('tanggal_lahir', $guruProfile?->tanggal_lahir?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="">Pilih</option>
                                <option value="L" {{ old('jenis_kelamin', $guruProfile?->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $guruProfile?->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Pendidikan Terakhir</label>
                            <select name="pendidikan_terakhir" class="form-select">
                                <option value="">Pilih</option>
                                @foreach(['D3','S1','S2','S3'] as $p)
                                    <option value="{{ $p }}" {{ old('pendidikan_terakhir', $guruProfile?->pendidikan_terakhir) == $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Mata Pelajaran</label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $guruProfile?->mata_pelajaran ?? '—' }}" readonly>
                            <small class="text-muted">Diatur oleh admin</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3"
                                      placeholder="Alamat lengkap">{{ old('alamat', $guruProfile?->address) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('guru.dashboard') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save me-1"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
function submitPhoto() {
    document.getElementById('photoForm').submit();
}
document.getElementById('profileForm').addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
});
</script>
@endpush
@endsection
