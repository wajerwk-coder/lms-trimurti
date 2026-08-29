@extends('layouts.admin')

@section('title', 'Tambah Pengguna Baru')

@section('page-title', 'Tambah Pengguna Baru')

@push('css')
<style>
/* Custom styling for user create form */
.form-container {
    max-width: 900px;
    margin: 0 auto;
}

.card-header.bg-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
    border-bottom: none;
}

.form-section-title {
    border-bottom: 2px solid #4e73df;
    padding-bottom: 0.5rem;
    margin-bottom: 1.5rem;
}

.required {
    color: #dc3545;
    font-weight: bold;
}

.input-group-text {
    cursor: pointer;
    transition: all 0.3s ease;
}

.input-group-text:hover {
    background-color: #f8f9fa;
    border-color: #4e73df;
    color: #4e73df;
}

.form-actions {
    gap: 0.75rem;
}

/* Avatar preview styling */
#avatarPreview img {
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#avatarPreview img:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Form validation improvements */
.form-control.is-invalid,
.form-select.is-invalid {
    border-color: #dc3545;
    background-image: none;
}

.form-control:focus,
.form-select:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .form-container {
        margin: 0;
        padding: 0 0.5rem;
    }
    
    .card {
        border-radius: 0;
        border-left: none;
        border-right: none;
    }
    
    .card-header {
        border-radius: 0 !important;
        margin: 0 -0.5rem;
        padding: 1rem;
    }
    
    .form-actions {
        flex-direction: column-reverse;
    }
    
    .form-actions .btn {
        width: 100%;
        margin: 0.25rem 0;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .card-footer {
        padding: 1rem;
        margin: 0 -0.5rem;
    }
}

@media (max-width: 576px) {
    .row {
        margin: 0;
    }
    
    .col-md-6 {
        padding-left: 0;
        padding-right: 0;
        margin-bottom: 1rem;
    }
    
    .form-group {
        margin-bottom: 1.25rem;
    }
    
    h4 {
        font-size: 1.1rem;
    }
    
    .card-title {
        font-size: 1.1rem;
    }
}
</style>
@endpush

@section('content')
@php
    // Definisikan semua variabel old di sini
    $oldRole = old('role', '');
    $oldNip = old('nip', '');
    $oldSubject = old('subject', '');
    $oldNis = old('nis', '');
    $oldKelasId = old('kelas_id', '');
    $oldJurusanId = old('jurusan_id', '');
    $oldBirthDate = old('birth_date', '');
    $oldAddress = old('address', '');
    $oldName = old('name', '');
    $oldEmail = old('email', '');
    $oldPhone = old('phone', '');
    $oldIsActive = old('is_active', '1');
@endphp

<div class="form-container">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-plus me-2"></i>
                    Form Tambah Pengguna
                </h5>
                <p class="card-text mt-2 mb-0 opacity-75">Tambahkan pengguna baru ke dalam sistem LMS</p>
            </div>
            <div>
                <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm text-primary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Kembali ke Manajemen User
                </a>
            </div>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Flash messages and error summary --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-times-circle me-2 mt-1"></i>
                        <div>
                            <strong>Terjadi kesalahan pada input Anda:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <small class="d-block mt-2 opacity-75">Catatan: Demi keamanan, kolom password tidak disimpan ulang dan harus diisi kembali.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card-body">
                <!-- Personal Information -->
                <div class="mb-4">
                    <h4 class="text-primary mb-3">
                        <i class="fas fa-user me-2"></i>
                        Informasi Pribadi
                    </h4>
                    <hr class="mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">
                                    Nama Lengkap <span class="required">*</span>
                                </label>
                                <input type="text" name="name" id="name" 
                                       class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" 
                                       value="{{ $oldName }}" required 
                                       placeholder="Masukkan nama lengkap">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-label">
                                    Alamat Email <span class="required">*</span>
                                </label>
                                <input type="email" name="email" id="email" 
                                       class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" 
                                       value="{{ $oldEmail }}" required 
                                       placeholder="contoh@email.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone" class="form-label">Nomor Telepon</label>
                                <input type="tel" name="phone" id="phone" 
                                       class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}" 
                                       value="{{ $oldPhone }}"
                                       placeholder="Contoh: +628123456789 atau 08123456789">
                                <small class="form-text text-muted">Opsional. Anda dapat memasukkan format +62 atau 0 diikuti nomor.</small>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="avatar" class="form-label">Foto Profil</label>
                                <input type="file" name="avatar" id="avatar" 
                                       class="form-control {{ $errors->has('avatar') ? 'is-invalid' : '' }}" 
                                       accept="image/*">
                                <small class="form-text text-muted">Format: JPG, PNG, GIF. Maksimal: 2MB</small>
                                @error('avatar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="avatarPreview" class="mt-3" style="display: none;">
                                    <img src="" alt="Preview" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #dee2e6;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="mb-4">
                    <h4 class="text-primary mb-3">
                        <i class="fas fa-key me-2"></i>
                        Informasi Akun
                    </h4>
                    <hr class="mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role" class="form-label">
                                    Role <span class="required">*</span>
                                </label>
                                <select name="role" id="role" 
                                        class="form-select {{ $errors->has('role') ? 'is-invalid' : '' }}" 
                                        required>
                                    <option value="">Pilih Role</option>
                                    <option value="admin" {{ $oldRole == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="guru" {{ $oldRole == 'guru' ? 'selected' : '' }}>Guru</option>
                                    <option value="siswa" {{ $oldRole == 'siswa' ? 'selected' : '' }}>Siswa</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active" class="form-label">Status Akun</label>
                                <select name="is_active" id="is_active" class="form-select">
                                    <option value="1" {{ $oldIsActive == '1' ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ $oldIsActive == '0' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password" class="form-label">
                                    Password <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" 
                                           class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" 
                                           required minlength="8" 
                                           placeholder="Masukkan password">
                                    <button type="button" class="input-group-text" id="togglePassword">
                                        <i class="fas fa-eye" id="passwordIcon"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">Minimal 8 karakter (harus sama dengan konfirmasi)</small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_confirmation" class="form-label">
                                    Konfirmasi Password <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="password_confirmation" 
                                           class="form-control" required 
                                           placeholder="Ulangi password">
                                    <button type="button" class="input-group-text" id="togglePasswordConfirm">
                                        <i class="fas fa-eye" id="passwordConfirmIcon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information (Conditional based on role) -->
                <div id="additionalInfo">
                    <!-- This will be populated based on selected role using JavaScript -->
                </div>
            </div>

            <div class="card-footer bg-light">
                <div class="form-actions">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-times me-1"></i>
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-1"></i>
                        Simpan Pengguna
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Avatar preview
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatarPreview');
    const previewImg = avatarPreview.querySelector('img');

    avatarInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file maksimal 2MB');
                this.value = '';
                avatarPreview.style.display = 'none';
                return;
            }

            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Format file harus JPG, PNG, atau GIF');
                this.value = '';
                avatarPreview.style.display = 'none';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                avatarPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            avatarPreview.style.display = 'none';
        }
    });

    // Password toggle functionality
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');
    const passwordIcon = document.getElementById('passwordIcon');

    if (togglePassword && passwordField && passwordIcon) {
        togglePassword.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            passwordIcon.classList.toggle('fa-eye');
            passwordIcon.classList.toggle('fa-eye-slash');
        });
    }

    // Password confirmation toggle
    const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
    const passwordConfirmField = document.getElementById('password_confirmation');
    const passwordConfirmIcon = document.getElementById('passwordConfirmIcon');

    if (togglePasswordConfirm && passwordConfirmField && passwordConfirmIcon) {
        togglePasswordConfirm.addEventListener('click', function() {
            const type = passwordConfirmField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordConfirmField.setAttribute('type', type);
            passwordConfirmIcon.classList.toggle('fa-eye');
            passwordConfirmIcon.classList.toggle('fa-eye-slash');
        });
    }

    // Role-based additional information
    const roleSelect = document.getElementById('role');
    const additionalInfo = document.getElementById('additionalInfo');

    function updateAdditionalInfo() {
        const role = roleSelect.value;
        let html = '';

        // Gunakan variabel PHP yang sudah didefinisikan di atas
        const oldNip = '{{ $oldNip }}';
        const oldSubject = '{{ $oldSubject }}';
        const oldNis = '{{ $oldNis }}';
        const oldKelasId = '{{ $oldKelasId }}';
        const oldJurusanId = '{{ $oldJurusanId }}';
        const oldBirthDate = '{{ $oldBirthDate }}';
        const oldAddress = `{{ $oldAddress }}`;

        switch(role) {
            case 'guru':
                html = `
                    <div class="mb-4">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-chalkboard-teacher me-2"></i>
                            Informasi Guru
                        </h4>
                        <hr class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nip" class="form-label">
                                        NIP <span class="required">*</span>
                                    </label>
                                    <input type="text" name="nip" id="nip" 
                                           class="form-control" value="${oldNip}" 
                                           required placeholder="Masukkan NIP">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subject" class="form-label">
                                        Mata Pelajaran <span class="required">*</span>
                                    </label>
                                    <select name="subject" id="subject" class="form-select" required>
                                        <option value="">Pilih Mata Pelajaran</option>
                                        <option value="Keperawatan Dasar" ${oldSubject === 'Keperawatan Dasar' ? 'selected' : ''}>Keperawatan Dasar</option>
                                        <option value="Anatomi Fisiologi" ${oldSubject === 'Anatomi Fisiologi' ? 'selected' : ''}>Anatomi Fisiologi</option>
                                        <option value="Farmakologi" ${oldSubject === 'Farmakologi' ? 'selected' : ''}>Farmakologi</option>
                                        <option value="Gizi Kesehatan" ${oldSubject === 'Gizi Kesehatan' ? 'selected' : ''}>Gizi Kesehatan</option>
                                        <option value="Kesehatan Lingkungan" ${oldSubject === 'Kesehatan Lingkungan' ? 'selected' : ''}>Kesehatan Lingkungan</option>
                                        <option value="Praktik Klinik" ${oldSubject === 'Praktik Klinik' ? 'selected' : ''}>Praktik Klinik</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                break;

            case 'siswa':
                // Build options HTML from server data (rendered by Blade)
                const jurusanOptions = `
                    <option value="">Pilih Jurusan (opsional)</option>
                    @foreach(($jurusans ?? []) as $jur)
                        <option value="{{ $jur->id }}" ${oldJurusanId == '{{ $jur->id }}' ? 'selected' : ''}>
                            {{ $jur->nama }} ({{ $jur->kode }})
                        </option>
                    @endforeach
                `;
                const kelasOptions = `
                    <option value="">Pilih Kelas</option>
                    @foreach(($kelas ?? []) as $k)
                        <option value="{{ $k->id }}" ${oldKelasId == '{{ $k->id }}' ? 'selected' : ''}>
                            {{ $k->grade }} {{ $k->major }} - {{ $k->name }} ({{ $k->code }})
                        </option>
                    @endforeach
                `;

                html = `
                    <div class="mb-4">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-user-graduate me-2"></i>
                            Informasi Siswa
                        </h4>
                        <hr class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nis" class="form-label">
                                        NIS <span class="required">*</span>
                                    </label>
                                    <input type="text" name="nis" id="nis"
                                           class="form-control" value="${oldNis}"
                                           required placeholder="Masukkan NIS">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="jurusan_id" class="form-label">
                                        Jurusan
                                        <a href="{{ route('admin.jurusan.create', ['return_to' => route('admin.users.create')]) }}" target="_blank" class="btn btn-sm btn-outline-secondary ms-2">
                                            <i class="fas fa-plus me-1"></i>Tambah Jurusan
                                        </a>
                                    </label>
                                    <select name="jurusan_id" id="jurusan_id" class="form-select">
                                        ${jurusanOptions}
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kelas_id" class="form-label">
                                        Kelas <span class="required">*</span>
                                        <a href="{{ route('admin.kelas.create', ['return_to' => route('admin.users.create')]) }}" target="_blank" class="btn btn-sm btn-outline-secondary ms-2">
                                            <i class="fas fa-plus me-1"></i>Tambah Kelas
                                        </a>
                                    </label>
                                    <select name="kelas_id" id="kelas_id" class="form-select" required>
                                        ${kelasOptions}
                                    </select>
                                    <small class="text-muted d-block mt-1">Jika Anda menambah Jurusan/Kelas di tab baru, silakan refresh halaman ini agar pilihan diperbarui.</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="reloadOptionsBtn">
                                    <i class="fas fa-sync-alt me-1"></i>Muat Ulang Opsi
                                </button>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="birth_date" class="form-label">
                                        Tanggal Lahir <span class="required">*</span>
                                    </label>
                                    <input type="date" name="birth_date" id="birth_date"
                                           class="form-control" value="${oldBirthDate}"
                                           required max="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="address" class="form-label">
                                        Alamat <span class="required">*</span>
                                    </label>
                                    <textarea name="address" id="address"
                                              class="form-control" rows="3"
                                              required placeholder="Masukkan alamat lengkap">${oldAddress}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                break;

            default:
                html = '';
        }

        additionalInfo.innerHTML = html;

        // Bind reload options button if present (dynamic content)
        const reloadBtn = document.getElementById('reloadOptionsBtn');
        if (reloadBtn) {
            reloadBtn.addEventListener('click', function() {
                // Simple approach: full page reload to fetch latest kelas/jurusan lists
                window.location.reload();
            });
        }
    }

    roleSelect.addEventListener('change', updateAdditionalInfo);

    // Trigger change event on page load if there's old role data
    const oldRole = '{{ $oldRole }}';
    if (oldRole) {
        roleSelect.value = oldRole;
        // Gunakan setTimeout untuk memastikan DOM siap
        setTimeout(updateAdditionalInfo, 0);
    }

    // Disable submit button on submit to prevent double submission
    const form = document.querySelector('form[action="{{ route('admin.users.store') }}"]');
    const submitBtn = document.getElementById('submitBtn');
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            // Check if form is valid
            if (form.checkValidity()) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan...';
            } else {
                // If form is invalid, let browser show validation errors
                // Don't disable the button
                return;
            }
        });
        
        // Re-enable button if there are validation errors
        form.addEventListener('invalid', function(e) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Pengguna';
        }, true);
    }

    // Focus the first invalid field after validation errors
    @if ($errors->any())
        const firstInvalid = document.querySelector('.is-invalid');
        if (firstInvalid) {
            firstInvalid.focus({ preventScroll: false });
        }
    @endif
});
</script>
@endsection
