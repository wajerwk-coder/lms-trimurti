@extends('layouts.admin')

@section('title', 'Laporan Praktikum')
@section('page-title', 'Laporan Praktikum')
@section('page-subtitle', 'Ringkasan kegiatan praktikum dan penilaian siswa.')

@section('content')

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0 fw-bold"><i class="fas fa-filter me-2"></i>Filter Laporan</h6>
    </div>
    <div class="card-body">
        <form id="filterForm" method="GET" action="{{ route('admin.reports.praktik') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Mata Pelajaran</label>
                    <select name="subject" class="form-select">
                        <option value="">Semua Mata Pelajaran</option>
                        @foreach($subjects ?? [] as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Guru</label>
                    <select name="teacher" class="form-select">
                        <option value="">Semua Guru</option>
                        @foreach($teachers ?? [] as $teacher)
                            <option value="{{ $teacher->id }}" {{ request('teacher') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Periode</label>
                    <select name="date_range" id="dateRangeSelect" class="form-select">
                        <option value="today" {{ request('date_range')=='today' ? 'selected' : '' }}>Hari Ini</option>
                        <option value="week" {{ request('date_range')=='week' ? 'selected' : '' }}>Minggu Ini</option>
                        <option value="month" {{ !request('date_range') || request('date_range')=='month' ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="semester" {{ request('date_range')=='semester' ? 'selected' : '' }}>Semester Ini</option>
                        <option value="custom" {{ request('date_range')=='custom' ? 'selected' : '' }}>Kustom</option>
                    </select>
                </div>
                <div class="col-md-3" id="customDateRange" style="{{ request('date_range')=='custom' ? '' : 'display:none;' }}">
                    <label class="form-label small fw-semibold">Rentang Tanggal</label>
                    <div class="d-flex gap-1">
                        <input type="date" name="start_date" class="form-control form-control-sm"
                               value="{{ request('start_date') }}">
                        <input type="date" name="end_date" class="form-control form-control-sm"
                               value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([
        ['Total Praktikum', $stats['total_practicals'] ?? 0, 'primary', 'flask'],
        ['Selesai', $stats['completed_practicals'] ?? 0, 'success', 'check-circle'],
        ['Tertunda', $stats['pending_practicals'] ?? 0, 'warning', 'clock'],
        ['Rata-rata Nilai', ($stats['average_score'] ?? 0) . '/100', 'info', 'star'],
    ] as [$label, $value, $color, $icon])
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-{{ $color }} bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-{{ $icon }} text-{{ $color }} fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0 text-{{ $color }}">{{ $value }}</div>
                        <small class="text-muted">{{ $label }}</small>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-flask me-2 text-primary"></i>Data Praktikum</h6>
        <span class="badge bg-secondary">{{ $practicals->total() ?? 0 }} hasil</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Praktikum</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru</th>
                        <th>Batas Waktu</th>
                        <th class="text-center">Penilaian</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Rata-rata</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($practicals as $practical)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold">{{ $practical->title ?? '—' }}</div>
                                <small class="text-muted">{{ Str::limit($practical->description ?? '', 50) }}</small>
                            </td>
                            <td class="text-muted">{{ $practical->subject?->name ?? '—' }}</td>
                            <td class="text-muted">{{ $practical->guru?->name ?? $practical->teacher?->name ?? '—' }}</td>
                            <td class="text-muted">
                                {{ optional($practical->due_date)->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $practical->scores?->count() ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                @if($practical->is_published || $practical->published_at)
                                    <span class="badge bg-success">Published</span>
                                @else
                                    <span class="badge bg-secondary">Draft</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $avg = $practical->scores?->avg('score') ?? 0;
                                @endphp
                                <span class="fw-bold {{ $avg >= 75 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($avg, 1) }}
                                </span>
                            </td>
                            <td class="text-center pe-3">
                                <a href="{{ route('admin.practicals.show', $practical->id) }}"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-flask fa-3x text-muted opacity-25 mb-3 d-block"></i>
                                <p class="text-muted mb-0">Tidak ada data praktikum yang sesuai filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($practicals) && $practicals->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $practicals->appends(request()->query())->links() }}
        </div>
    @endif
</div>

@if(isset($practicals) && $practicals->count() > 0)
{{-- Charts --}}
<div class="row g-4 mt-2">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-chart-bar me-2 text-primary"></i>Distribusi Nilai</h6>
            </div>
            <div class="card-body">
                <canvas id="scoreDistributionChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-chart-pie me-2 text-primary"></i>Status Praktikum</h6>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Custom date toggle
    const rangeSelect = document.getElementById('dateRangeSelect');
    const customDiv   = document.getElementById('customDateRange');
    rangeSelect.addEventListener('change', function () {
        customDiv.style.display = this.value === 'custom' ? '' : 'none';
    });

    @if(isset($practicals) && $practicals->count() > 0)
    // Score distribution
    const sCtx = document.getElementById('scoreDistributionChart');
    if (sCtx) {
        new Chart(sCtx, {
            type: 'bar',
            data: {
                labels: ['0-49', '50-59', '60-69', '70-79', '80-89', '90-100'],
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: @json($scoreDistribution ?? [0,0,0,0,0,0]),
                    backgroundColor: 'rgba(59,130,246,0.6)',
                    borderColor: '#2563eb',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    }

    // Status pie
    const pCtx = document.getElementById('statusChart');
    if (pCtx) {
        new Chart(pCtx, {
            type: 'doughnut',
            data: {
                labels: ['Selesai', 'Tertunda'],
                datasets: [{
                    data: [{{ $stats['completed_practicals'] ?? 0 }}, {{ $stats['pending_practicals'] ?? 0 }}],
                    backgroundColor: ['#10b981', '#f59e0b'],
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    }
    @endif
});
</script>
@endpush

@endsection
