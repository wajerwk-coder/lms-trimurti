@extends('layouts.guru')

@section('title', 'Laporan Detail Praktikum - ' . ($practical->title ?? 'Praktikum'))
@section('page-title', 'Laporan Detail Praktikum')
@section('page-subtitle', ($practical->title ?? 'Praktikum') . ' — ' . ($practical->subject?->name ?? 'Mata Pelajaran'))

@section('page-actions')
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm me-2">
        <i class="fas fa-print me-1"></i>Cetak
    </button>
    <a href="{{ route('guru.reports.index') }}" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')

{{-- Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="h2 fw-bold text-primary mb-0">{{ $practical->participants_count ?? 0 }}</div>
            <small class="text-muted"><i class="fas fa-users me-1"></i>Total Peserta</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="h2 fw-bold text-success mb-0">{{ $practical->scores_count ?? 0 }}</div>
            <small class="text-muted"><i class="fas fa-check-circle me-1"></i>Telah Dinilai</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="h2 fw-bold text-purple mb-0">{{ $practical->average_score ?? 0 }}<small class="fs-6 text-muted">/100</small></div>
            <small class="text-muted"><i class="fas fa-chart-bar me-1"></i>Nilai Rata-rata</small>
        </div>
    </div>
</div>

{{-- Score Distribution Chart --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-chart-bar me-2 text-primary"></i>Distribusi Nilai</h6>
    </div>
    <div class="card-body">
        <div style="height: 200px;">
            <canvas id="scoreDistributionChart"></canvas>
        </div>
    </div>
</div>

{{-- Participants Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-list me-2 text-primary"></i>Daftar Peserta dan Nilai</h6>
        <span class="badge bg-secondary">{{ count($participants ?? []) }} peserta</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Siswa</th>
                        <th>NIS</th>
                        <th>Kelas</th>
                        <th class="text-center">Kehadiran</th>
                        <th class="text-center">Nilai</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($participants ?? [] as $participant)
                        @php
                            $attendanceStatus = $participant->attendance_status ?? null;
                            [$abColor, $abLabel] = match($attendanceStatus) {
                                'present' => ['success', 'Hadir'],
                                'late'    => ['warning', 'Terlambat'],
                                'absent'  => ['danger',  'Absen'],
                                'excused' => ['info',    'Izin'],
                                default   => ['secondary','Belum Absen'],
                            };
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $participant->student?->avatar_url ?? asset('images/default-avatar.png') }}"
                                         alt="{{ $participant->student?->name ?? 'Siswa' }}"
                                         class="rounded-circle flex-shrink-0"
                                         style="width:34px;height:34px;object-fit:cover;"
                                         onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                                    <div class="fw-semibold">{{ $participant->student?->name ?? '—' }}</div>
                                </div>
                            </td>
                            <td class="text-muted">{{ $participant->student?->nis ?? '—' }}</td>
                            <td class="text-muted">{{ $participant->student?->kelas?->name ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $abColor }}">{{ $abLabel }}</span>
                            </td>
                            <td class="text-center fw-bold {{ ($participant->score ?? 0) >= 75 ? 'text-success' : 'text-danger' }}">
                                @if($participant->score !== null)
                                    {{ $participant->score }}/100
                                @else
                                    <span class="text-muted fw-normal">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($participant->score !== null)
                                    <span class="badge bg-success">Dinilai</span>
                                @else
                                    <span class="badge bg-secondary">Belum Dinilai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-flask fa-2x text-muted opacity-25 mb-3 d-block"></i>
                                <p class="text-muted mb-0">Tidak ada peserta praktikum.</p>
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
@media print {
    .lms-header, .sidebar, .lms-page-header, .lms-footer, .btn { display: none !important; }
    .lms-main { margin: 0 !important; }
    .card { border: 1px solid #dee2e6 !important; }
}
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('scoreDistributionChart');
    if (ctx && typeof Chart !== 'undefined') {
        const scoreData = @json($scoreDistribution ?? [0, 0, 0, 0, 0, 0]);
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['0–49', '50–59', '60–69', '70–79', '80–89', '90–100'],
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: scoreData,
                    backgroundColor: 'rgba(99,102,241,0.7)',
                    borderColor: '#6366f1',
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
@endpush

@endsection