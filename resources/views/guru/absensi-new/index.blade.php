@extends('layouts.guru')

@section('title', 'Manajemen Absensi - Guru')
@section('page-title', 'Manajemen Absensi')
@section('page-subtitle', 'Kelola data kehadiran siswa')

@push('css')
<link href="{{ asset('css/pagination-new.css') }}" rel="stylesheet">
<style>
/* Custom styles for new attendance management */
.attendance-stats {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 1.5rem;
    color: white;
    margin-bottom: 2rem;
}

.stat-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    background: rgba(255, 255, 255, 0.15);
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.9;
}

.filter-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border: 1px solid #e3e6f0;
}

.attendance-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border: 1px solid #e3e6f0;
}

.attendance-table .table {
    margin-bottom: 0;
    border-radius: 12px;
}

.attendance-table .table th {
    background: #f8f9fc;
    border-bottom: 2px solid #e3e6f0;
    font-weight: 600;
    color: #5a5c69;
    padding: 1rem 0.75rem;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.attendance-table .table td {
    padding: 1rem 0.75rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f9;
}

.student-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.student-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e3e6f0;
}

.student-details {
    flex: 1;
}

.student-name {
    font-weight: 600;
    color: #5a5c69;
    margin-bottom: 0.25rem;
}

.student-nis {
    font-size: 0.75rem;
    color: #858796;
}

.status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
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
    background: #f8d7da;
    color: #e74a3b;
}

.status-alpha {
    background: #f5c6cb;
    color: #e74a3b;
}

.pagination-new {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1.5rem;
}

.pagination-new .page-link {
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    border: 1px solid #e3e6f0;
    background: white;
    color: #5a5c69;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    min-width: 40px;
    text-align: center;
}

.pagination-new .page-link:hover {
    background: #4e73df;
    color: white;
    border-color: #4e73df;
    transform: translateY(-1px);
}

.pagination-new .page-item.active .page-link {
    background: #4e73df;
    color: white;
    border-color: #4e73df;
}

.pagination-new .page-item.disabled .page-link {
    opacity: 0.5;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .attendance-stats {
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .filter-card {
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .attendance-table .table th,
    .attendance-table .table td {
        padding: 0.75rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .student-avatar {
        width: 32px;
        height: 32px;
    }
}
</style>
@endpush

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('guru.dashboard') }}" class="text-decoration-none">Beranda</a></li>
<li class="breadcrumb-item active">Manajemen Absensi</li>
@endsection

@section('page-actions')
<a href="{{ route('guru.absensi-new.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-2"></i>Buat Absensi
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

<!-- Success Alert -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Statistics Cards -->
<div class="attendance-stats">
    <div class="row">
        <div class="col-6 col-md-2 mb-3">
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total'] }}</div>
                <div class="stat-label">Total</div>
            </div>
        </div>
        <div class="col-6 col-md-2 mb-3">
            <div class="stat-card">
                <div class="stat-number">{{ $stats['hadir'] }}</div>
                <div class="stat-label">Hadir</div>
            </div>
        </div>
        <div class="col-6 col-md-2 mb-3">
            <div class="stat-card">
                <div class="stat-number">{{ $stats['izin'] }}</div>
                <div class="stat-label">Izin</div>
            </div>
        </div>
        <div class="col-6 col-md-2 mb-3">
            <div class="stat-card">
                <div class="stat-number">{{ $stats['sakit'] }}</div>
                <div class="stat-label">Sakit</div>
            </div>
        </div>
        <div class="col-6 col-md-2 mb-3">
            <div class="stat-card">
                <div class="stat-number">{{ $stats['alpha'] }}</div>
                <div class="stat-label">Alpha</div>
            </div>
        </div>
        <div class="col-6 col-md-2 mb-3">
            <div class="stat-card">
                <div class="stat-number">{{ $stats['persentase_kehadiran'] }}%</div>
                <div class="stat-label">Kehadiran</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="filter-card">
    <form method="GET" action="{{ route('guru.absensi-new.index') }}" id="filterForm">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Cari Siswa</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ $filters['search'] }}" placeholder="Nama atau NIS">
                </div>
            </div>
            <div class="col-md-2">
                <label for="class" class="form-label">Kelas</label>
                <select class="form-select" id="class" name="class">
                    <option value="all">Semua Kelas</option>
                    @foreach($filterOptions['classes'] as $id => $name)
                        <option value="{{ $id }}" {{ $filters['class'] == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="subject" class="form-label">Mata Pelajaran</label>
                <select class="form-select" id="subject" name="subject">
                    <option value="all">Semua Mata Pelajaran</option>
                    @foreach($filterOptions['subjects'] as $id => $name)
                        <option value="{{ $id }}" {{ $filters['subject'] == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="date" class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="date" name="date" 
                       value="{{ $filters['date'] }}">
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="all">Semua Status</option>
                    @foreach($filterOptions['statuses'] as $value => $label)
                        <option value="{{ $value }}" {{ $filters['status'] == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Attendance Table -->
<div class="attendance-table">
    @if($attendances->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $attendance)
                    <tr>
                        <td>
                            <div class="student-info">
                                <img src="{{ $attendance->siswa?->photoUrl ?? asset('images/default-avatar.png') }}" 
                                     alt="{{ $attendance->siswa?->name }}" 
                                     class="student-avatar"
                                     onerror="this.src='/images/default-avatar.png'">
                                <div class="student-details">
                                    <div class="student-name">{{ e($attendance->siswa?->name ?? 'N/A') }}</div>
                                    <div class="student-nis">NIS: {{ e($attendance->siswa?->siswa?->nis ?? $attendance->siswa?->nis ?? '-') }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ e($attendance->siswa?->kelas?->name ?? 'Tidak ada kelas') }}</span>
                        </td>
                        <td>
                            <span class="text-muted">{{ e($attendance->subject?->name ?? 'Tidak ada mata pelajaran') }}</span>
                        </td>
                        <td>
                            <span class="text-muted">{{ $attendance->date?->format('d/m/Y') ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $attendance->status }}">
                                {{ ucfirst($attendance->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="text-muted small">{{ e($attendance->note ?? '-') }}</span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('guru.absensi-new.edit', $attendance->id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('guru.absensi-new.destroy', $attendance->id) }}" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data absensi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($attendances->hasPages())
        <div class="p-3">
            {{ $attendances->links('pagination::bootstrap-4-new') }}
        </div>
        @endif
    @else
        <div class="text-center py-5">
            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Tidak ada data absensi</h5>
            <p class="text-muted">Coba ubah filter atau buat data absensi baru.</p>
            <a href="{{ route('guru.absensi-new.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Buat Absensi Baru
            </a>
        </div>
    @endif
</div>
@endsection