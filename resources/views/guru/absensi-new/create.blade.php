@extends('layouts.guru')

@section('title', 'Buat Absensi - Guru')
@section('page-title', 'Buat Absensi Baru')
@section('page-subtitle', 'Catat kehadiran siswa untuk kelas dan mata pelajaran tertentu')

@push('css')
<style>
.form-container {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e3e6f0;
}

.form-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    color: white;
}

.student-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.student-card {
    background: #f8f9fc;
    border-radius: 12px;
    padding: 1rem;
    border: 1px solid #e3e6f0;
    transition: all 0.3s ease;
}

.student-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.student-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e3e6f0;
}

.student-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.student-details {
    flex: 1;
}

.student-name {
    font-weight: 600;
    color: #5a5c69;
    margin-bottom: 0.25rem;
}

.student-class {
    font-size: 0.875rem;
    color: #858796;
}

.attendance-options {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.radio-card {
    flex: 1;
    min-width: 80px;
    text-align: center;
    padding: 0.75rem 0.5rem;
    border-radius: 8px;
    border: 2px solid #e3e6f0;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.radio-card:hover {
    border-color: #4e73df;
    background: #f8f9fc;
}

.radio-card input[type="radio"] {
    display: none;
}

.radio-card input[type="radio"]:checked + .radio-label {
    color: white;
}

.radio-card input[type="radio"]:checked + .radio-label.status-hadir {
    background: #1cc88a;
}

.radio-card input[type="radio"]:checked + .radio-label.status-izin {
    background: #36b9cc;
}

.radio-card input[type="radio"]:checked + .radio-label.status-sakit {
    background: #f6c23e;
}

.radio-card input[type="radio"]:checked + .radio-label.status-alpha {
    background: #e74a3b;
}

.radio-label {
    display: block;
    padding: 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
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

.keterangan-input {
    width: 100%;
    margin-top: 0.5rem;
    padding: 0.5rem;
    border: 1px solid #e3e6f0;
    border-radius: 6px;
    font-size: 0.875rem;
}

.action-buttons {
    position: sticky;
    bottom: 0;
    background: white;
    padding: 1.5rem;
    border-top: 1px solid #e3e6f0;
    margin: 2rem -2rem -2rem -2rem;
    border-radius: 0 0 16px 16px;
}

@media (max-width: 768px) {
    .form-container {
        padding: 1rem;
        margin: 1rem;
    }
    
    .student-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .attendance-options {
        flex-direction: column;
    }
    
    .radio-card {
        min-width: 100%;
    }
    
    .action-buttons {
        margin: 1rem -1rem -1rem -1rem;
        padding: 1rem;
    }
}
</style>
@endpush

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('guru.dashboard') }}" class="text-decoration-none">Beranda</a></li>
<li class="breadcrumb-item"><a href="{{ route('guru.absensi-new.index') }}" class="text-decoration-none">Manajemen Absensi</a></li>
<li class="breadcrumb-item active">Buat Absensi</li>
@endsection

@section('page-actions')
<a href="{{ route('guru.absensi-new.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left me-2"></i>Kembali
</a>
@endsection

@section('content')
<!-- Error Alert -->
@if(isset($error))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    {{ $error }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="form-container">
    <!-- Form Header -->
    <div class="form-header">
        <h4 class="mb-0">
            <i class="fas fa-calendar-plus me-2"></i>
            Form Pembuatan Absensi
        </h4>
        <p class="mb-0 mt-2 opacity-75">Pilih kelas dan mata pelajaran, kemudian catat kehadiran siswa</p>
    </div>

    <form method="POST" action="{{ route('guru.absensi-new.store') }}" id="attendanceForm">
        @csrf
        
        <!-- Class and Subject Selection -->
        <div class="row mb-4">
            <div class="col-md-4">
                <label for="class_id" class="form-label fw-bold">Kelas *</label>
                <select class="form-select form-select-lg" id="class_id" name="class_id" required>
                    <option value="">Pilih Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $selectedClass == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="subject_id" class="form-label fw-bold">Mata Pelajaran *</label>
                <select class="form-select form-select-lg" id="subject_id" name="subject_id" required>
                    <option value="">Pilih Mata Pelajaran</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="date" class="form-label fw-bold">Tanggal *</label>
                <input type="date" class="form-control form-control-lg" id="date" name="date" 
                       value="{{ $selectedDate }}" required>
            </div>
            <div class="col-md-2">
                <label for="type" class="form-label fw-bold">Tipe *</label>
                <select class="form-select form-select-lg" id="type" name="type" required>
                    <option value="regular" selected>Reguler</option>
                    <option value="praktik">Praktik</option>
                </select>
            </div>
        </div>

        <!-- Students List -->
        <div id="studentsContainer">
            @if($students->count() > 0)
                <h5 class="mb-3">
                    <i class="fas fa-users me-2"></i>
                    Daftar Siswa ({{ $students->count() }} siswa)
                </h5>
                
                <div class="student-grid">
                    @foreach($students as $student)
                    <div class="student-card">
                        <div class="student-info">
                            <img src="{{ $student->photoUrl ?? asset('images/default-avatar.png') }}" 
                                 alt="{{ $student->name }}" 
                                 class="student-avatar"
                                 onerror="this.src='/images/default-avatar.png'">
                            <div class="student-details">
                                <div class="student-name">{{ e($student->name) }}</div>
                                <div class="student-class">{{ e($student->kelas?->name ?? 'Tidak ada kelas') }}</div>
                            </div>
                        </div>
                        
                        <div class="attendance-options">
                            <div class="radio-card">
                                <input type="radio" name="attendances[{{ $student->id }}][status]" 
                                       id="hadir_{{ $student->id }}" value="hadir" checked>
                                <label for="hadir_{{ $student->id }}" class="radio-label status-hadir">
                                    <i class="fas fa-check me-1"></i>Hadir
                                </label>
                            </div>
                            <div class="radio-card">
                                <input type="radio" name="attendances[{{ $student->id }}][status]" 
                                       id="izin_{{ $student->id }}" value="izin">
                                <label for="izin_{{ $student->id }}" class="radio-label status-izin">
                                    <i class="fas fa-clock me-1"></i>Izin
                                </label>
                            </div>
                            <div class="radio-card">
                                <input type="radio" name="attendances[{{ $student->id }}][status]" 
                                       id="sakit_{{ $student->id }}" value="sakit">
                                <label for="sakit_{{ $student->id }}" class="radio-label status-sakit">
                                    <i class="fas fa-heartbeat me-1"></i>Sakit
                                </label>
                            </div>
                            <div class="radio-card">
                                <input type="radio" name="attendances[{{ $student->id }}][status]" 
                                       id="alpha_{{ $student->id }}" value="alpha">
                                <label for="alpha_{{ $student->id }}" class="radio-label status-alpha">
                                    <i class="fas fa-times me-1"></i>Alpha
                                </label>
                            </div>
                        </div>
                        
                        <input type="hidden" name="attendances[{{ $student->id }}][siswa_id]" value="{{ $student->id }}">
                        <input type="text" name="attendances[{{ $student->id }}][keterangan]" 
                               class="keterangan-input" placeholder="Keterangan (jika ada)">
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Pilih Kelas Terlebih Dahulu</h5>
                    <p class="text-muted">Silakan pilih kelas untuk melihat daftar siswa.</p>
                </div>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <div class="row">
                <div class="col-md-6">
                    <button type="button" class="btn btn-outline-secondary btn-lg w-100" onclick="markAllPresent()">
                        <i class="fas fa-check-double me-2"></i>
                        Tandai Semua Hadir
                    </button>
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-save me-2"></i>
                        Simpan Absensi
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection