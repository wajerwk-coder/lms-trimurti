@extends('layouts.guru')

@section('title', 'Laporan')
@section('page-title', 'Laporan Mengajar')
@section('page-subtitle', 'Analitik dan laporan mengajar yang komprehensif.')

@section('page-actions')
    <button class="btn btn-outline-secondary btn-sm me-1" onclick="window.location.reload()">
        <i class="fas fa-sync-alt me-1"></i>Perbarui
    </button>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
        <i class="fas fa-download me-1"></i>Ekspor
    </button>
@endsection

@push('css')
<style>
.report-card { transition: all .2s; }
.report-card:hover { border-color: var(--bs-primary) !important; transform: translateY(-2px); }
.stats-card { position: relative; overflow: hidden; }
.stats-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:3px;
    background: linear-gradient(90deg, var(--bs-primary), var(--bs-info));
}
</style>
@endpush

@section('content')
{{-- Filter Periode --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-calendar-alt me-2 text-primary"></i>Periode Laporan</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('guru.reports.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i>Perbarui Periode
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Stats Cards --}}
<div class="row g-3 mb-4">
    @foreach([
        ['primary', 'fa-book-open',   $stats['total_materials']    ?? 0, 'Materi',   'Sumber aktif'],
        ['success', 'fa-tasks',       $stats['total_assignments']  ?? 0, 'Tugas',    'Total dibuat'],
        ['warning', 'fa-flask',       $stats['total_practicals']   ?? 0, 'Praktikum','Kegiatan lab'],
        ['info',    'fa-user-check',  $stats['total_attendance']   ?? 0, 'Absensi',  'Rekaman terlacak'],
    ] as [$c, $ic, $v, $l, $sub])
    <div class="col-6 col-md-3">
        <div class="card stats-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-{{ $c }} bg-opacity-10 flex-shrink-0">
                        <i class="fas {{ $ic }} text-{{ $c }} fa-lg"></i>
                    </div>
                    <div>
                        <div class="h3 fw-bold mb-0 text-{{ $c }}">{{ number_format($v) }}</div>
                        <div class="small fw-semibold text-dark">{{ $l }}</div>
                        <div class="text-muted" style="font-size:.72rem;">{{ $sub }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Kategori Laporan -->
<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-lift report-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar avatar-lg bg-primary bg-gradient rounded-3 me-3">
                        <i class="fas fa-user-check text-white fs-4"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-1 fw-bold">Laporan Absensi</h5>
                        <small class="text-muted">Pelacakan kehadiran siswa</small>
                    </div>
                </div>
                <p class="text-muted mb-3">Laporan kehadiran siswa yang komprehensif berdasarkan mata pelajaran, kelas, dan periode waktu. Lacak pola kehadiran dan hasilkan wawasan.</p>
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('guru.reports.attendance') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-chart-bar me-1"></i> Lihat Laporan
                    </a>
                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ number_format($stats['total_attendance'] ?? 0) }} rekam</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-lift report-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar avatar-lg bg-success bg-gradient rounded-3 me-3">
                        <i class="fas fa-flask text-white fs-4"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-1 fw-bold">Laporan Praktikum</h5>
                        <small class="text-muted">Kegiatan lab & penilaian</small>
                    </div>
                </div>
                <p class="text-muted mb-3">Laporan sesi praktikum terperinci termasuk kinerja siswa, penggunaan peralatan lab, dan skor penilaian.</p>
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('guru.reports.practical') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-microscope me-1"></i> Lihat Laporan
                    </a>
                    <span class="badge bg-success bg-opacity-10 text-success">{{ number_format($stats['total_practicals'] ?? 0) }} sesi</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-lift report-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar avatar-lg bg-info bg-gradient rounded-3 me-3">
                        <i class="fas fa-download text-white fs-4"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-1 fw-bold">Ekspor & Alat</h5>
                        <small class="text-muted">Opsi ekspor data</small>
                    </div>
                </div>
                <p class="text-muted mb-3">Ekspor laporan dalam berbagai format (PDF, Excel, CSV). Buat laporan khusus dengan opsi filter lanjutan.</p>
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                        <i class="fas fa-file-export me-1"></i> Ekspor Data
                    </button>
                    <span class="badge bg-info bg-opacity-10 text-info">Multi-format</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ringkasan Aktivitas -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light border-0">
        <h5 class="card-title mb-0 fw-bold">
            <i class="fas fa-chart-pie text-primary me-2"></i>
            Ringkasan Aktivitas
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="d-flex align-items-center p-3 bg-success bg-opacity-10 rounded-3">
                    <div class="avatar avatar-md bg-success bg-gradient rounded-circle me-3">
                        <i class="fas fa-check-circle text-white"></i>
                    </div>
                    <div>
                        <div class="h4 mb-0 text-success fw-bold">{{ number_format($stats['graded_assignments'] ?? 0) }}</div>
                        <small class="text-muted fw-medium">Tugas Dinilai</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="d-flex align-items-center p-3 bg-warning bg-opacity-10 rounded-3">
                    <div class="avatar avatar-md bg-warning bg-gradient rounded-circle me-3">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                    <div>
                        <div class="h4 mb-0 text-warning fw-bold">{{ number_format($stats['pending_assignments'] ?? 0) }}</div>
                        <small class="text-muted fw-medium">Tugas Tertunda</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="d-flex align-items-center p-3 bg-info bg-opacity-10 rounded-3">
                    <div class="avatar avatar-md bg-info bg-gradient rounded-circle me-3">
                        <i class="fas fa-download text-white"></i>
                    </div>
                    <div>
                        <div class="h4 mb-0 text-info fw-bold">{{ number_format($stats['materials_downloads'] ?? 0) }}</div>
                        <small class="text-muted fw-medium">Unduhan Materi</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="d-flex align-items-center p-3 bg-primary bg-opacity-10 rounded-3">
                    <div class="avatar avatar-md bg-primary bg-gradient rounded-circle me-3">
                        <i class="fas fa-star text-white"></i>
                    </div>
                    <div>
                        <div class="h4 mb-0 text-primary fw-bold">{{ number_format($stats['average_practical_score'] ?? 0, 1) }}</div>
                        <small class="text-muted fw-medium">Rata-rata Nilai Praktikum</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ekspor -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="fas fa-file-export me-2"></i>
                    Ekspor Laporan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form action="{{ route('guru.reports.generate') }}" method="POST" id="exportForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">
                            <i class="fas fa-chart-bar text-primary me-1"></i>
                            Jenis Laporan
                        </label>
                        <select name="type" class="form-select form-select-lg" required>
                            <option value="">Pilih jenis laporan...</option>
                            <option value="absensi">
                                <i class="fas fa-user-check"></i> Laporan Absensi
                            </option>
                            <option value="praktik">
                                <i class="fas fa-flask"></i> Laporan Praktikum
                            </option>
                            <option value="tugas">
                                <i class="fas fa-tasks"></i> Laporan Tugas
                            </option>
                            <option value="materi">
                                <i class="fas fa-book"></i> Laporan Materi
                            </option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">
                                <i class="fas fa-calendar-alt text-primary me-1"></i>
                                Tanggal Mulai
                            </label>
                            <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-lg" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">
                                <i class="fas fa-calendar-alt text-primary me-1"></i>
                                Tanggal Akhir
                            </label>
                            <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-lg" required>
                        </div>
                    </div>
                    <input type="hidden" name="format" value="pdf">
                    <div class="alert alert-info d-flex align-items-center mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>
                            <strong>Catatan:</strong> Laporan akan dibuat dalam format PDF dan otomatis diunduh.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="exportBtn">
                        <i class="fas fa-download me-1"></i> 
                        <span class="btn-text">Ekspor PDF</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Export form loading state
    const exportForm = document.getElementById('exportForm');
    if (exportForm) {
        exportForm.addEventListener('submit', function () {
            const btn    = document.getElementById('exportBtn');
            const bText  = btn.querySelector('.btn-text');
            const spin   = btn.querySelector('.spinner-border');
            bText.textContent = 'Membuat...';
            spin.classList.remove('d-none');
            btn.disabled = true;
            setTimeout(() => {
                bText.textContent = 'Ekspor PDF';
                spin.classList.add('d-none');
                btn.disabled = false;
                bootstrap.Modal.getInstance(document.getElementById('exportModal'))?.hide();
            }, 3000);
        });
    }
});
</script>
@endpush

@endsection