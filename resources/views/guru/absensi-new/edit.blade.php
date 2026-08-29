@extends('layouts.guru')

@section('title', 'Edit Absensi - Guru')
@section('page-title', 'Edit Absensi')
@section('page-subtitle', 'Perbarui data kehadiran siswa')

@push('css')
<style>
.form-container {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e3e6f0;
    max-width: 800px;
    margin: 0 auto;
}

.student-info-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 1.5rem;
    color: white;
    margin-bottom: 2rem;
}

.student-avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid rgba(255, 255, 255, 0.2);
}

.student-details-large {
    flex: 1;
}

.student-name-large {
    font-size: 1.25rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.student-meta {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    opacity: 0.9;
}

.status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.status-option {
    background: #f8f9fc;
    border-radius: 12px;
    padding: 1.5rem;
    border: 2px solid #e3e6f0;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.status-option:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.status-option input[type="radio"] {
    display: none;
}

.status-option input[type="radio"]:checked + .status-content {
    color: white;
}

.status-option input[type="radio"]:checked + .status-content.status-hadir {
    background: #1cc88a;
}

.status-option input[type="radio"]:checked + .status-content.status-izin {
    background: #36b9cc;
}

.status-option input[type="radio"]:checked + .status-content.status-sakit {
    background: #f6c23e;
}

.status-option input[type="radio"]:checked + .status-content.status-alpha {
    background: #e74a3b;
}

.status-content {
    padding: 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.status-hadir {
    background: #d1f2eb;
    color: #1cc88a;
}

.status-izin {
    background: #d1ecf1;
    color: #36b9cc;
}

.status-sakit {
    background: #fff3cd;
    color: #f6c23e;
}

.status-alpha {
    background: #f5c6cb;
    color: #e74a3b;
}

.status-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.status-label {
    font-weight: 600;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.time-inputs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 2rem;
}

.time-card {
    background: #f8f9fc;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #e3e6f0;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

@media (max-width: 768px) {
    .form-container {
        padding: 1rem;
        margin: 1rem;
    }
    
    .student-info-card {
        padding: 1rem;
        flex-direction: column;
        text-align: center;
    }
    
    .student-avatar-large {
        width: 60px;
        height: 60px;
        margin: 0 auto 1rem;
    }
    
    .status-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .time-inputs {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>
@endpush

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('guru.dashboard') }}" class="text-decoration-none">Beranda</a></li>
<li class="breadcrumb-item"><a href="{{ route('guru.absensi-new.index') }}" class="text-decoration-none">Manajemen Absensi</a></li>
<li class="breadcrumb-item active">Edit Absensi</li>
@endsection

@section('page-actions')
<a href="{{ route('guru.absensi-new.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left me-2"></i>Kembali
</a>
@endsection

@section('content')
<div class="form-container">
    <!-- Student Information -->
    <div class="student-info-card">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ $attendance->siswa?->photoUrl ?? asset('images/default-avatar.png') }}" 
                 alt="{{ $attendance->siswa?->name }}" 
                 class="student-avatar-large"
                 onerror="this.src='/images/default-avatar.png'">
            <div class="student-details-large">
                <div class="student-name-large">{{ e($attendance->siswa?->name ?? 'N/A') }}</div>
                <div class="student-meta">
                    <div class="meta-item">
                        <i class="fas fa-id-card"></i>
                        <span>NIS: {{ e($attendance->siswa?->siswa?->nis ?? $attendance->siswa?->nis ?? '-') }}</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-graduation-cap"></i>
                        <span>{{ e($attendance->siswa?->kelas?->name ?? 'Tidak ada kelas') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('guru.absensi-new.update', $attendance->id) }}">
        @csrf
        @method('PUT')
        
        <!-- Subject Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <label class="form-label fw-bold">Mata Pelajaran</label>
                <input type="text" class="form-control form-control-lg" 
                       value="{{ e($attendance->subject?->name ?? 'Tidak ada mata pelajaran') }}" 
                       readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Tanggal</label>
                <input type="text" class="form-control form-control-lg" 
                       value="{{ $attendance->date?->format('d/m/Y') ?? '-' }}" 
                       readonly>
            </div>
        </div>

        <!-- Status Selection -->
        <div class="mb-4">
            <label class="form-label fw-bold mb-3">Status Kehadiran</label>
            <div class="status-grid">
                <div class="status-option">
                    <input type="radio" name="status" id="status_hadir" value="hadir" 
                           {{ $attendance->status === 'hadir' ? 'checked' : '' }}>
                    <label for="status_hadir" class="status-content status-hadir">
                        <div class="status-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="status-label">Hadir</div>
                    </label>
                </div>
                
                <div class="status-option">
                    <input type="radio" name="status" id="status_izin" value="izin" 
                           {{ $attendance->status === 'izin' ? 'checked' : '' }}>
                    <label for="status_izin" class="status-content status-izin">
                        <div class="status-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="status-label">Izin</div>
                    </label>
                </div>
                
                <div class="status-option">
                    <input type="radio" name="status" id="status_sakit" value="sakit" 
                           {{ $attendance->status === 'sakit' ? 'checked' : '' }}>
                    <label for="status_sakit" class="status-content status-sakit">
                        <div class="status-icon">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <div class="status-label">Sakit</div>
                    </label>
                </div>
                
                <div class="status-option">
                    <input type="radio" name="status" id="status_alpha" value="alpha" 
                           {{ $attendance->status === 'alpha' ? 'checked' : '' }}>
                    <label for="status_alpha" class="status-content status-alpha">
                        <div class="status-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="status-label">Alpha</div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Time Inputs -->
        <div class="time-inputs">
            <div class="time-card">
                <label for="waktu_masuk" class="form-label fw-bold">
                    <i class="fas fa-sign-in-alt me-2"></i>Waktu Masuk
                </label>
                <input type="time" class="form-control form-control-lg" id="waktu_masuk" name="waktu_masuk" 
                       value="{{ null?->format('H:i') ?? '' }}">
            </div>
            
            <div class="time-card">
                <label for="waktu_keluar" class="form-label fw-bold">
                    <i class="fas fa-sign-out-alt me-2"></i>Waktu Keluar
                </label>
                <input type="time" class="form-control form-control-lg" id="waktu_keluar" name="waktu_keluar" 
                       value="{{ null?->format('H:i') ?? '' }}">
            </div>
        </div>

        <!-- Keterangan -->
        <div class="mb-4">
            <label for="keterangan" class="form-label fw-bold">
                <i class="fas fa-comment me-2"></i>Keterangan
            </label>
            <textarea class="form-control" id="keterangan" name="note" rows="3" 
                      placeholder="Tambahkan keterangan jika diperlukan...">{{ e($attendance->note ?? '') }}</textarea>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button type="submit" class="btn btn-primary btn-lg px-5">
                <i class="fas fa-save me-2"></i>
                Simpan Perubahan
            </button>
            <a href="{{ route('guru.absensi-new.index') }}" class="btn btn-outline-secondary btn-lg px-5">
                <i class="fas fa-times me-2"></i>
                Batal
            </a>
        </div>
    </form>
</div>
@endsection