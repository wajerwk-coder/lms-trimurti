@extends('layouts.admin')

@section('title', 'Pengaturan Sistem')
@section('page-title', 'Pengaturan Sistem')
@section('page-subtitle', 'Konfigurasi sistem LMS Trimurti Husada.')

@section('breadcrumb')
    <li class="breadcrumb-item active">Pengaturan Sistem</li>
@endsection

@section('page-actions')
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary" onclick="resetToDefault()">
            <i class="fas fa-undo me-2"></i>Reset Default
        </button>
        <button type="submit" form="settingsForm" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Simpan Pengaturan
        </button>
    </div>
@endsection

@section('content')
<!-- Alerts -->
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

<!-- Settings Form -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="fas fa-cogs me-2 text-primary"></i>Konfigurasi Sistem
        </h6>
    </div>
    <div class="card-body">
        <form id="settingsForm" action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Nav tabs -->
            <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                        <i class="fas fa-cog me-2"></i>Umum
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="academic-tab" data-bs-toggle="tab" data-bs-target="#academic" type="button" role="tab">
                        <i class="fas fa-graduation-cap me-2"></i>Akademik
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab">
                        <i class="fas fa-upload me-2"></i>File Upload
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="notification-tab" data-bs-toggle="tab" data-bs-target="#notification" type="button" role="tab">
                        <i class="fas fa-bell me-2"></i>Notifikasi
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance" type="button" role="tab">
                        <i class="fas fa-tools me-2"></i>Pemeliharaan
                    </button>
                </li>
            </ul>

            <!-- Tab content -->
            <div class="tab-content" id="settingsTabContent">
                <!-- General Settings Tab -->
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="site_name" class="form-label">Nama Aplikasi <span class="text-danger">*</span></label>
                            <input type="text" name="site_name" id="site_name" class="form-control"
                                   value="{{ old('site_name', $settings->site_name ?? 'LMS Trimurti Husada') }}" required>
                            @error('site_name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="contact_email" class="form-label">Email Kontak <span class="text-danger">*</span></label>
                            <input type="email" name="contact_email" id="contact_email" class="form-control"
                                   value="{{ old('contact_email', $settings->contact_email ?? 'admin@trimurtihusada.sch.id') }}" required>
                            @error('contact_email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone_number" class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                            <input type="text" name="phone_number" id="phone_number" class="form-control"
                                   value="{{ old('phone_number', $settings->phone_number ?? '') }}" required>
                            @error('phone_number')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="address" class="form-label">Alamat Sekolah</label>
                            <textarea name="address" id="address" class="form-control" rows="3">{{ old('address', $settings->address ?? 'Jl. Raya Ambon, Maluku') }}</textarea>
                            @error('address')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="logo" class="form-label">Logo Sekolah</label>
                            <input type="file" name="logo" id="logo" class="form-control" accept="image/jpeg,image/png,image/jpg">
                            @error('logo')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            @if($settings && $settings->logo)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $settings->logo) }}" alt="Current Logo" class="img-thumbnail" style="max-height: 60px;">
                                <p class="small text-muted mt-1">Logo saat ini</p>
                            </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label for="favicon" class="form-label">Favicon</label>
                            <input type="file" name="favicon" id="favicon" class="form-control" accept="image/png,image/ico">
                            @error('favicon')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            @if($settings && $settings->favicon)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $settings->favicon) }}" alt="Current Favicon" class="img-thumbnail" style="max-height: 32px;">
                                <p class="small text-muted mt-1">Favicon saat ini</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Academic Settings Tab -->
                <div class="tab-pane fade" id="academic" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="academic_year" class="form-label">Tahun Akademik <span class="text-danger">*</span></label>
                            <input type="text" name="academic_year" id="academic_year" class="form-control"
                                   value="{{ date('Y') . '/' . (date('Y') + 1) }}" placeholder="2024/2025" required>
                            <div class="form-text">Format: YYYY/YYYY</div>
                        </div>

                        <div class="col-md-6">
                            <label for="semester" class="form-label">Semester Aktif <span class="text-danger">*</span></label>
                            <select name="semester" id="semester" class="form-select" required>
                                <option value="1" selected>Semester 1 (Ganjil)</option>
                                <option value="2">Semester 2 (Genap)</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="min_attendance_percentage" class="form-label">Min. Kehadiran (%) <span class="text-danger">*</span></label>
                            <input type="number" name="min_attendance_percentage" id="min_attendance_percentage" class="form-control"
                                   value="75" min="0" max="100" required>
                        </div>

                        <div class="col-md-4">
                            <label for="passing_grade" class="form-label">Nilai Kelulusan <span class="text-danger">*</span></label>
                            <input type="number" name="passing_grade" id="passing_grade" class="form-control"
                                   value="75" min="0" max="100" step="0.1" required>
                        </div>

                        <div class="col-md-4">
                            <label for="practical_min_score" class="form-label">Min. Nilai Praktikum <span class="text-danger">*</span></label>
                            <input type="number" name="practical_min_score" id="practical_min_score" class="form-control"
                                   value="70" min="0" max="100" step="0.1" required>
                        </div>

                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Catatan:</strong> Pengaturan ini akan mempengaruhi sistem penilaian dan kelulusan siswa.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- File Upload Settings Tab -->
                <div class="tab-pane fade" id="upload" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="max_upload_size" class="form-label">Ukuran Max File (MB) <span class="text-danger">*</span></label>
                            <input type="number" name="max_upload_size" id="max_upload_size" class="form-control"
                                   value="10" min="1" max="100" required>
                            <div class="form-text">Ukuran maksimal per file dalam megabytes</div>
                        </div>

                        <div class="col-md-6">
                            <label for="max_files_per_upload" class="form-label">Max File per Upload</label>
                            <input type="number" name="max_files_per_upload" id="max_files_per_upload" class="form-control"
                                   value="5" min="1" max="20">
                        </div>

                        <div class="col-12">
                            <label for="allowed_file_types" class="form-label">Jenis File Diizinkan <span class="text-danger">*</span></label>
                            <input type="text" name="allowed_file_types" id="allowed_file_types" class="form-control"
                                   value="pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,rar" required>
                            <div class="form-text">Pisahkan dengan koma (contoh: pdf,doc,jpg)</div>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings Tab -->
                <div class="tab-pane fade" id="notification" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="email_notifications" value="1" class="form-check-input" checked>
                                <label class="form-check-label">Notifikasi Email</label>
                            </div>
                            <div class="form-text">Kirim notifikasi melalui email</div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="assignment_reminders" value="1" class="form-check-input" checked>
                                <label class="form-check-label">Pengingat Tugas</label>
                            </div>
                            <div class="form-text">Reminder untuk tugas yang akan berakhir</div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="attendance_alerts" value="1" class="form-check-input" checked>
                                <label class="form-check-label">Alert Kehadiran</label>
                            </div>
                            <div class="form-text">Peringatan untuk kehadiran rendah</div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="grade_notifications" value="1" class="form-check-input" checked>
                                <label class="form-check-label">Notifikasi Nilai</label>
                            </div>
                            <div class="form-text">Pemberitahuan nilai baru</div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="practical_reminders" value="1" class="form-check-input" checked>
                                <label class="form-check-label">Pengingat Praktikum</label>
                            </div>
                            <div class="form-text">Reminder jadwal praktikum</div>
                        </div>
                    </div>
                </div>

                <!-- System Maintenance Tab -->
                <div class="tab-pane fade" id="maintenance" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="maintenance_mode" value="1" class="form-check-input" role="switch">
                                <label class="form-check-label">Mode Pemeliharaan</label>
                            </div>
                            <div class="form-text text-warning">Sistem tidak dapat diakses pengguna biasa</div>
                        </div>

                        <div class="col-md-6">
                            <label for="backup_frequency" class="form-label">Frekuensi Backup</label>
                            <select name="backup_frequency" id="backup_frequency" class="form-select">
                                <option value="daily" selected>Harian</option>
                                <option value="weekly">Mingguan</option>
                                <option value="monthly">Bulanan</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="session_lifetime" class="form-label">Masa Aktif Sesi (menit)</label>
                            <input type="number" name="session_lifetime" id="session_lifetime" class="form-control"
                                   value="120" min="5" max="1440">
                        </div>

                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Perhatian:</strong> Mode pemeliharaan akan membuat sistem tidak dapat diakses oleh pengguna biasa.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Card Footer -->
        <div class="card-footer d-flex justify-content-end gap-2">
            <button type="button" onclick="resetToDefault()" class="btn btn-outline-secondary">
                <i class="fas fa-undo me-2"></i>Reset ke Default
            </button>
            <button type="submit" form="settingsForm" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Simpan Pengaturan
            </button>
        </div>
    </div>
</div>

<script>
// Initialize Bootstrap components and form functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tabs
    const triggerTabList = [].slice.call(document.querySelectorAll('#settingsTabs a[data-bs-toggle="tab"]'));
    triggerTabList.forEach(function (triggerEl) {
        const tabTrigger = new bootstrap.Tab(triggerEl);
        
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
        });
    });
    
    // File preview functionality
    setupFilePreview('school_logo', 'logoPreview');
    setupFilePreview('school_favicon', 'faviconPreview');
    
    // Form validation
    const form = document.getElementById('settingsForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                showAlert('Mohon lengkapi semua field yang wajib diisi', 'danger');
                return;
            }
            
            // Show loading state
            const submitBtn = document.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';
            submitBtn.disabled = true;
            
            // Submit form (simulate for demo)
            setTimeout(() => {
                showAlert('Pengaturan berhasil disimpan!', 'success');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 1500);
        });
    }
});

// Reset form to default values
function resetToDefault() {
    if (confirm('Apakah Anda yakin ingin mengembalikan semua pengaturan ke nilai default?')) {
        // General Settings
        document.getElementById('app_name').value = 'LMS Trimurti';
        document.getElementById('school_name').value = 'SMK Kesehatan Trimurti Husada';
        document.getElementById('school_address').value = 'Jl. Raya Ambon, Maluku';
        document.getElementById('school_phone').value = '';
        document.getElementById('school_email').value = 'admin@trimurti.ac.id';
        document.getElementById('school_website').value = 'https://trimurti.ac.id';
        document.getElementById('timezone').value = 'Asia/Jakarta';
        document.getElementById('language').value = 'id';
        
        // Academic Settings
        const currentYear = new Date().getFullYear();
        document.getElementById('academic_year').value = currentYear + '/' + (currentYear + 1);
        document.getElementById('current_semester').value = '1';
        document.getElementById('min_attendance').value = 75;
        document.getElementById('passing_grade').value = 70;
        document.getElementById('practical_min_score').value = 75;
        
        // File Upload Settings
        document.getElementById('max_upload_size').value = 10;
        document.getElementById('allowed_file_types').value = 'pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,rar';
        document.getElementById('max_files_per_upload').value = 5;
        
        // Notification Settings
        document.querySelector('input[name="email_notifications"]').checked = true;
        document.querySelector('input[name="assignment_reminders"]').checked = true;
        document.querySelector('input[name="attendance_alerts"]').checked = true;
        document.querySelector('input[name="grade_notifications"]').checked = true;
        document.querySelector('input[name="practical_reminders"]').checked = true;
        
        // System Maintenance
        document.querySelector('input[name="maintenance_mode"]').checked = false;
        document.getElementById('backup_frequency').value = 'daily';
        document.getElementById('session_lifetime').value = 120;
        
        // Clear file inputs and previews
        const logoInput = document.getElementById('school_logo');
        const faviconInput = document.getElementById('school_favicon');
        if (logoInput) logoInput.value = '';
        if (faviconInput) faviconInput.value = '';
        
        const logoPreview = document.getElementById('logoPreview');
        const faviconPreview = document.getElementById('faviconPreview');
        if (logoPreview) logoPreview.style.display = 'none';
        if (faviconPreview) faviconPreview.style.display = 'none';
        
        showAlert('Pengaturan berhasil dikembalikan ke default', 'success');
    }
}

// Setup file preview functionality
function setupFilePreview(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    
    if (input && preview) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
    }
}

// Show alert helper
function showAlert(message, type = 'info') {
    // Remove existing alerts
    document.querySelectorAll('.alert.auto-dismiss').forEach(alert => {
        alert.remove();
    });
    
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show auto-dismiss" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert at the top of the card body
    const cardBody = document.querySelector('.card-body');
    if (cardBody) {
        cardBody.insertAdjacentHTML('afterbegin', alertHtml);
    }
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert.auto-dismiss');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}
</script>
@endsection