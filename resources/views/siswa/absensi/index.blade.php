@extends('layouts.siswa')

@section('title', 'Rekap Absensi')
@section('page-title', 'Rekap Absensi')
@section('page-subtitle', 'Riwayat kehadiran saya per bulan.')

@section('page-actions')
    <a href="{{ route('siswa.absensi.export', ['month' => $month ?? now()->month, 'year' => $year ?? now()->year]) }}"
       class="btn btn-outline-success btn-sm" target="_blank">
        <i class="fas fa-print me-1"></i>Cetak / Export
    </a>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@php
    $hadirCount    = $monthlyStats['present']         ?? 0;
    $izinCount     = $monthlyStats['izin']            ?? 0;
    $sakitCount    = $monthlyStats['sakit']           ?? 0;
    $alpaCount     = $monthlyStats['absent']          ?? 0;
    $attendancePct = $monthlyStats['attendance_rate'] ?? ($monthlyStats['percentage'] ?? 0);
    $totalCount    = $monthlyStats['total']           ?? 0;
    $workingDays   = $monthlyStats['working_days']    ?? 0;
    $barColor      = $attendancePct >= 80 ? 'success' : ($attendancePct >= 60 ? 'warning' : 'danger');
@endphp

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('siswa.absensi.index') }}"
              class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Bulan</label>
                <select name="month" class="form-select">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $i == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $i)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Tahun</label>
                <select name="year" class="form-select">
                    @for($i = date('Y'); $i >= date('Y') - 3; $i--)
                        <option value="{{ $i }}" {{ $i == $year ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Stats Cards --}}
<div class="row g-3 mb-4">
    @foreach([
        ['success', $hadirCount,    'Hadir',    'fa-check-circle'],
        ['info',    $izinCount,     'Izin',     'fa-info-circle'],
        ['warning', $sakitCount,    'Sakit',    'fa-heartbeat'],
        ['danger',  $alpaCount,     'Alpha',    'fa-times-circle'],
    ] as [$c, $v, $l, $ic])
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100"
             style="border-bottom: 4px solid var(--bs-{{ $c }}) !important;">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 bg-{{ $c }} bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:44px;height:44px;">
                    <i class="fas {{ $ic }} text-{{ $c }}"></i>
                </div>
                <div>
                    <div class="h3 fw-bold mb-0 text-{{ $c }}">{{ $v }}</div>
                    <div class="text-muted small">{{ $l }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Kehadiran Rate + Progress --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center g-4">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-semibold">Persentase Kehadiran</span>
                    <span class="fw-bold text-{{ $barColor }}">
                        {{ number_format($attendancePct, 1) }}%
                    </span>
                </div>
                <div class="progress mb-2" style="height:12px;border-radius:6px;">
                    <div class="progress-bar bg-{{ $barColor }}" role="progressbar"
                         style="width:{{ min($attendancePct, 100) }}%;border-radius:6px;"
                         aria-valuenow="{{ $attendancePct }}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <div class="d-flex justify-content-between small text-muted">
                    <span>{{ $hadirCount }} dari {{ $totalCount }} pertemuan</span>
                    <span>
                        @if($attendancePct >= 80)
                            <span class="text-success"><i class="fas fa-check-circle me-1"></i>Kehadiran baik</span>
                        @elseif($attendancePct >= 60)
                            <span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Perlu ditingkatkan</span>
                        @else
                            <span class="text-danger"><i class="fas fa-times-circle me-1"></i>Kehadiran rendah</span>
                        @endif
                    </span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="row g-2 text-center small">
                    <div class="col-6">
                        <div class="bg-light rounded-2 p-2">
                            <div class="fw-bold text-dark">{{ $totalCount }}</div>
                            <div class="text-muted">Total Tercatat</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded-2 p-2">
                            <div class="fw-bold text-dark">{{ $workingDays }}</div>
                            <div class="text-muted">Hari Kerja</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tabel Absensi --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="fas fa-calendar-check me-2 text-primary"></i>
            Detail Absensi —
            {{ \Carbon\Carbon::create(null, $month)->translatedFormat('F') }} {{ $year }}
        </h6>
        <span class="badge bg-secondary">{{ $attendances->total() }} record</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Tanggal</th>
                        <th>Hari</th>
                        <th>Mata Pelajaran</th>
                        <th class="text-center">Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $att)
                    @php
                        $st    = $att->status;
                        $color = match($st) { 'hadir'=>'success','izin'=>'info','sakit'=>'warning','alpha'=>'danger', default=>'secondary' };
                        $label = match($st) { 'hadir'=>'Hadir','izin'=>'Izin','sakit'=>'Sakit','alpha'=>'Alpha', default=>ucfirst($st) };
                        $icon  = match($st) { 'hadir'=>'fa-check-circle','izin'=>'fa-info-circle','sakit'=>'fa-heartbeat','alpha'=>'fa-times-circle', default=>'fa-circle' };
                        $d     = \Carbon\Carbon::parse($att->date);
                    @endphp
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $d->format('d M Y') }}</td>
                        <td class="text-muted">{{ $d->translatedFormat('l') }}</td>
                        <td class="text-muted">{{ $att->subject?->name ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $color }} bg-opacity-15 text-{{ $color }}
                                          border border-{{ $color }} border-opacity-25 px-2 py-1">
                                <i class="fas {{ $icon }} me-1" style="font-size:.7rem;"></i>{{ $label }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $att->note ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="fas fa-calendar-times fa-2x text-muted opacity-25 mb-3 d-block"></i>
                            <p class="text-muted mb-0">Tidak ada data absensi untuk periode ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($attendances->hasPages())
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-2 px-4">
            <small class="text-muted">
                {{ $attendances->firstItem() }}–{{ $attendances->lastItem() }} dari {{ $attendances->total() }}
            </small>
            {{ $attendances->appends(request()->query())->links() }}
        </div>
    @endif
</div>

@endsection
