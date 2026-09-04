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
                {{-- Avatar --}}
                @php
                    // Query langsung dari DB untuk pastikan nilai terbaru
                    $freshPhoto = \App\Models\UserCentral::find(Auth::id())?->photo;
                    $avatarSrc  = $freshPhoto && str_starts_with($freshPhoto, 'http')
                        ? $freshPhoto
                        : ($freshPhoto ? asset('storage/' . $freshPhoto)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=0f766e&color=fff&size=128&bold=true');
                @endphp
                <img src="{{ $avatarSrc }}"
                     alt="{{ $user->name }}"
                     class="rounded-circle border mb-3"
                     id="avatarPreview"
                     style="width:90px;height:90px;object-fit:cover;"
                     onerror="console.error('Avatar load failed:', this.src); this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0f766e&color=fff&size=128&bold=true'">
                @if($freshPhoto && str_starts_with($freshPhoto, 'http'))
                <div class="small text-success mb-1" style="font-size:.65rem;">
                    <i class="fas fa-check-circle me-1"></i>Foto Cloudinary aktif
                </div>
                @elseif($freshPhoto)
                <div class="small text-warning mb-1" style="font-size:.65rem;">
                    <i class="fas fa-exclamation-circle me-1"></i>Foto lokal (tidak tersedia di Railway)
                </div>
                @endif

                <h6 class="fw-semibold mb-0">{{ $user->name }}</h6>
                <small class="text-muted d-block mb-2">{{ $user->email }}</small>
                <span class="badge bg-success bg-opacity-10 text-success">
                    <i class="fas fa-chalkboard-teacher me-1"></i>Guru
                </span>

                <hr class="my-3">

                {{-- Cloudinary Upload Widget --}}
                <button type="button" id="uploadPhotoBtn"
                        class="btn btn-outline-primary btn-sm w-100 mb-2">
                    <i class="fas fa-camera me-1"></i>Ganti Foto
                </button>
                <div class="text-muted mb-2" style="font-size:.7rem;">JPG, PNG · maks 5 MB</div>

                {{-- Loading + Success indicator --}}
                <div id="uploadLoading" class="d-none text-center mb-2">
                    <span class="spinner-border spinner-border-sm text-primary me-1"></span>
                    <small class="text-muted">Mengupload...</small>
                </div>
                <div id="uploadSuccess" class="d-none mb-2 p-2 rounded-2"
                     style="background:rgba(22,163,74,.08);border:1px solid rgba(22,163,74,.2);font-size:.75rem;color:#16a34a;">
                    <i class="fas fa-check-circle me-1"></i>Foto berhasil diupload!
                </div>

                {{-- Form hidden untuk simpan URL foto --}}
                <form action="{{ route('guru.profile.update') }}" method="POST" id="photoUrlForm">
                    @csrf @method('PUT')
                    <input type="hidden" name="name"      value="{{ $user->name }}">
                    <input type="hidden" name="email"     value="{{ $user->email }}">
                    <input type="hidden" name="phone"     value="{{ $user->phone ?? '' }}">
                    <input type="hidden" name="photo_url" id="hiddenPhotoUrl" value="">
                </form>

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
<script src="https://upload-widget.cloudinary.com/global/all.js" type="text/javascript"></script>
<script>
var CLOUDINARY_CLOUD_NAME   = '{{ config("cloudinary.cloud_name", env("CLOUDINARY_CLOUD_NAME", "aw9h9icb")) }}';
var CLOUDINARY_UPLOAD_PRESET = '{{ config("cloudinary.upload_preset", env("CLOUDINARY_UPLOAD_PRESET", "lms_photos")) }}';
var uploadWidget = null;

document.getElementById('uploadPhotoBtn')?.addEventListener('click', function () {
    if (!uploadWidget) {
        uploadWidget = cloudinary.createUploadWidget({
            cloudName: CLOUDINARY_CLOUD_NAME,
            uploadPreset: CLOUDINARY_UPLOAD_PRESET,
            sources: ['local', 'camera'],
            multiple: false,
            maxFileSize: 5242880,
            cropping: true,
            croppingAspectRatio: 1,
            folder: 'profiles/guru',
            resourceType: 'image',
            clientAllowedFormats: ['jpg','jpeg','png','webp'],
        }, function (error, result) {
            // DEBUG: log semua event
            console.log('Cloudinary event:', result?.event, result, error);

            if (error) {
                document.getElementById('uploadLoading')?.classList.add('d-none');
                var msg = 'Gagal upload foto.\n';
                if (error.status === 400 || (error.message && error.message.includes('preset'))) {
                    msg += 'Upload preset "' + CLOUDINARY_UPLOAD_PRESET + '" tidak ditemukan di Cloudinary.\nBuka Cloudinary → Settings → Upload → Upload presets → Buat "lms_photos" mode Unsigned.';
                } else {
                    msg += (error.message || JSON.stringify(error));
                }
                alert(msg);
                return;
            }
            if (result && result.event === 'queues-start') {
                document.getElementById('uploadLoading')?.classList.remove('d-none');
            }
            if (result && result.event === 'success') {
                var url = result.info.secure_url;
                console.log('Upload success, URL:', url);
                var preview = document.getElementById('avatarPreview');
                if (preview) preview.src = url;
                document.getElementById('uploadLoading')?.classList.add('d-none');
                document.getElementById('uploadSuccess')?.classList.remove('d-none');

                // Kirim via AJAX ke endpoint khusus photo
                fetch('{{ route("guru.profile.update-photo-url") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ photo_url: url }),
                })
                .then(function(res) {
                    console.log('Server response status:', res.status);
                    return res.text();
                })
                .then(function(text) {
                    console.log('Server response:', text);
                    try {
                        var data = JSON.parse(text);
                        if (data.success) {
                            window.location.href = '{{ route("guru.profile.edit") }}';
                        } else {
                            alert('Server error: ' + JSON.stringify(data));
                        }
                    } catch(e) {
                        // Server mungkin return HTML (error page)
                        alert('Server response tidak valid. Cek console untuk detail.\n' + text.substring(0, 200));
                    }
                })
                .catch(function(err) {
                    console.error('Fetch error:', err);
                    alert('Network error: ' + err.message);
                });
            }
        });
    }
    uploadWidget.open();
});

document.getElementById('profileForm').addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
});

window.addEventListener('pageshow', function (e) {
    if (!e.persisted) return;
    const btn = document.getElementById('submitBtn');
    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Perubahan'; }
});
</script>
@endpush
@endsection
