@extends('layouts.admin')

@section('title', 'Laporan Sistem')
@section('page-title', 'Laporan Sistem')

@section('breadcrumb')
    <li class="breadcrumb-item active">Laporan Sistem</li>
@endsection

@section('page-actions')
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-download me-2"></i>Ekspor Cepat
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="exportQuick('pdf')"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
                <li><a class="dropdown-item" href="#" onclick="exportQuick('excel')"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                <li><a class="dropdown-item" href="#" onclick="exportQuick('csv')"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
            </ul>
        </div>
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

<div class="row mb-4">
    <!-- Academic Reports Card -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card shadow h-100 report-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="p-3 bg-primary bg-opacity-10 rounded-circle me-3">
                        <i class="fas fa-graduation-cap fa-2x text-primary"></i>
                    </div>
                    <h5 class="card-title mb-0">Laporan Akademik</h5>
                </div>
                <p class="card-text text-muted mb-3">Laporan terkait kegiatan akademik dan pembelajaran</p>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.reports.attendance') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 text-decoration-none">
                        <i class="fas fa-chart-line me-2 text-primary"></i> Laporan Nilai Siswa
                    </a>
                    <a href="{{ route('admin.reports.attendance') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 text-decoration-none">
                        <i class="fas fa-tasks me-2 text-primary"></i> Laporan Penyelesaian Tugas
                    </a>
                    <a href="{{ route('admin.reports.attendance') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 text-decoration-none">
                        <i class="fas fa-book-open me-2 text-primary"></i> Laporan Penggunaan Materi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Reports Card -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card shadow h-100 report-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="p-3 bg-success bg-opacity-10 rounded-circle me-3">
                        <i class="fas fa-calendar-check fa-2x text-success"></i>
                    </div>
                    <h5 class="card-title mb-0">Laporan Kehadiran</h5>
                </div>
                <p class="card-text text-muted mb-3">Laporan kehadiran siswa dan guru</p>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.reports.attendance') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 text-decoration-none">
                        <i class="fas fa-calendar-day me-2 text-success"></i> Laporan Kehadiran Harian
                    </a>
                    <a href="{{ route('admin.reports.attendance') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 text-decoration-none">
                        <i class="fas fa-calendar-alt me-2 text-success"></i> Laporan Absensi Bulanan
                    </a>
                    <a href="{{ route('admin.reports.attendance') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 text-decoration-none">
                        <i class="fas fa-chart-pie me-2 text-success"></i> Ringkasan Kehadiran
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Practical Reports Card -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card shadow h-100 report-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="p-3 bg-warning bg-opacity-10 rounded-circle me-3">
                        <i class="fas fa-flask fa-2x text-warning"></i>
                    </div>
                    <h5 class="card-title mb-0">Laporan Praktikum</h5>
                </div>
                <p class="card-text text-muted mb-3">Laporan kegiatan praktikum dan penilaian</p>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.reports.practical') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 text-decoration-none">
                        <i class="fas fa-microscope me-2 text-warning"></i> Laporan Praktikum
                    </a>
                    <a href="{{ route('admin.reports.practical') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 text-decoration-none">
                        <i class="fas fa-star me-2 text-warning"></i> Laporan Nilai Praktikum
                    </a>
                    <a href="{{ route('admin.reports.practical') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 text-decoration-none">
                        <i class="fas fa-check-double me-2 text-warning"></i> Laporan Penyelesaian Praktikum
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- User Reports Card -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card shadow h-100 report-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="p-3 bg-info bg-opacity-10 rounded-circle me-3">
                        <i class="fas fa-users fa-2x text-info"></i>
                    </div>
                    <h5 class="card-title mb-0">Laporan Pengguna</h5>
                </div>
                <p class="card-text text-muted mb-3">Laporan aktivitas dan statistik pengguna</p>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.reports.attendance') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 text-decoration-none">
                        <i class="fas fa-chart-bar me-2 text-info"></i> Aktivitas Pengguna
                    </a>
                    <a href="{{ route('admin.reports.attendance') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 text-decoration-none">
                        <i class="fas fa-sign-in-alt me-2 text-info"></i> Log Masuk Pengguna
                    </a>
                    <a href="{{ route('admin.reports.attendance') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 text-decoration-none">
                        <i class="fas fa-trophy me-2 text-info"></i> Performa Pengguna
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- System Reports Card -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card shadow h-100 report-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="p-3 bg-danger bg-opacity-10 rounded-circle me-3">
                        <i class="fas fa-server fa-2x text-danger"></i>
                    </div>
                    <h5 class="card-title mb-0">Laporan Sistem</h5>
                </div>
                <p class="card-text text-muted mb-3">Laporan performa dan penggunaan sistem</p>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.reports.attendance') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 text-decoration-none">
                        <i class="fas fa-tachometer-alt me-2 text-danger"></i> Penggunaan Sistem
                    </a>
                    <a href="{{ route('admin.reports.attendance') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 text-decoration-none">
                        <i class="fas fa-hdd me-2 text-danger"></i> Penggunaan Penyimpanan
                    </a>
                    <a href="{{ route('admin.reports.attendance') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 text-decoration-none">
                        <i class="fas fa-clipboard-list me-2 text-danger"></i> Log Audit Sistem
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Reports Card -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card shadow h-100 report-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="p-3 bg-secondary bg-opacity-10 rounded-circle me-3">
                        <i class="fas fa-download fa-2x text-secondary"></i>
                    </div>
                    <h5 class="card-title mb-0">Ekspor Laporan</h5>
                </div>
                <p class="card-text text-muted mb-3">Ekspor laporan dalam berbagai format</p>
                <div class="d-grid gap-2">
                    <form method="GET" action="{{ route('admin.reports.attendance') }}">
                        <input type="hidden" name="export" value="pdf">
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                            <i class="fas fa-file-pdf me-2"></i>Ekspor ke PDF
                        </button>
                    </form>
                    <form method="GET" action="{{ route('admin.reports.attendance') }}">
                        <input type="hidden" name="export" value="excel">
                        <button type="submit" class="btn btn-outline-success btn-sm w-100">
                            <i class="fas fa-file-excel me-2"></i>Ekspor ke Excel
                        </button>
                    </form>
                    <form method="GET" action="{{ route('admin.reports.attendance') }}">
                        <input type="hidden" name="export" value="csv">
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-file-csv me-2"></i>Ekspor ke CSV
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Statistics --}}
<div class="row g-3 mb-4">
    @foreach([
        ['primary', 'fa-users',            $stats['total_users']           ?? 0, 'Total Pengguna'],
        ['success', 'fa-chart-line',       $stats['total_activities']      ?? 0, 'Aktivitas Hari Ini'],
        ['warning', 'fa-percentage',       ($stats['attendance_rate']      ?? 0) . '%', 'Rata-rata Kehadiran'],
        ['info',    'fa-clipboard-check',  $stats['completed_assignments'] ?? 0, 'Tugas Selesai'],
    ] as [$color, $icon, $val, $label])
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-{{ $color }} bg-opacity-10 flex-shrink-0">
                    <i class="fas {{ $icon }} text-{{ $color }} fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ $val }}</div>
                    <small class="text-muted">{{ $label }}</small>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Laporan Terbaru --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="fas fa-history me-2 text-secondary"></i>Laporan Terbaru
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Jenis Laporan</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentReports ?? [] as $report)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold">{{ $report['name'] ?? 'Tidak Diketahui' }}</div>
                            <small class="text-muted">{{ $report['type'] ?? '—' }}</small>
                        </td>
                        <td class="text-muted">{{ $report['created_by'] ?? 'Admin' }}</td>
                        <td class="text-muted">{{ $report['date'] ?? '—' }}</td>
                        <td class="text-center">
                            @php
                                $status = $report['status'] ?? 'unknown';
                                $badge = ['completed'=>'success','processing'=>'warning','failed'=>'danger','pending'=>'info','unknown'=>'secondary'][$status] ?? 'secondary';
                                $label = ['completed'=>'Selesai','processing'=>'Diproses','failed'=>'Gagal','pending'=>'Pending','unknown'=>'—'][$status] ?? ucfirst($status);
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ $label }}</span>
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex gap-1 justify-content-center">
                                <button class="btn btn-outline-info btn-sm" title="Lihat"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-outline-success btn-sm" title="Unduh"><i class="fas fa-download"></i></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-file-alt fa-3x opacity-25 mb-3 d-block"></i>
                            <p class="mb-0">Tidak ada laporan terbaru.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('css')
<style>
.report-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.report-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,.12) !important;
}
</style>
@endpush

@push('js')
<script>
function exportQuick(format) {
    const url = `{{ route('admin.reports.attendance') }}?export=${format}`;
    window.open(url, '_blank');
}

document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scroll for internal navigation
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>
@endpush
@endsection