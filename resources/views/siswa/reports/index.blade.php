@extends('layouts.siswa')

@section('title', 'Laporan Akademik')
@section('page-title', 'Laporan Akademik')
@section('page-subtitle', 'Ringkasan nilai, tugas, dan kehadiran kamu')

@section('page-actions')
    <a href="{{ route('siswa.nilai.index') }}" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-chart-bar me-1"></i>Lihat Nilai Detail
    </a>
@endsection

@section('content')
<div class="container-fluid px-0">

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 p-2 bg-primary bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-tasks text-primary fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ $gradedAssignments }}/{{ $totalAssignments }}</div>
                        <small class="text-muted">Tugas Dinilai</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 p-2 bg-warning bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-flask text-warning fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ $gradedPracticals }}/{{ $totalPracticals }}</div>
                        <small class="text-muted">Praktikum Dinilai</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 p-2 bg-success bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-star text-success fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ number_format($averageScore ?? 0, 1) }}</div>
                        <small class="text-muted">Rata-rata Tugas</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 p-2 bg-info bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-calendar-check text-info fa-lg"></i>
                    </div>
                    <div>
                        @php
                            $kehadiran = $totalAttendances > 0
                                ? round(($presentAttendances / $totalAttendances) * 100)
                                : 0;
                        @endphp
                        <div class="h4 fw-bold mb-0">{{ $kehadiran }}%</div>
                        <small class="text-muted">Kehadiran</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- Nilai Tugas --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-tasks me-2 text-primary"></i>Nilai Tugas
                    </h6>
                </div>
                <div class="card-body p-0">
                    @forelse($assignmentSubmissions->whereNotNull('score') as $sub)
                    @php
                        $sc    = (float)($sub->score ?? 0);
                        $max   = $sub->assignment?->max_score ?? 100;
                        $pct   = $max > 0 ? min(100, ($sc / $max) * 100) : 0;
                        $clr   = $pct >= 80 ? 'success' : ($pct >= 60 ? 'warning' : 'danger');
                        $grade = $sc >= 90 ? 'A' : ($sc >= 80 ? 'B' : ($sc >= 70 ? 'C' : ($sc >= 60 ? 'D' : 'E')));
                    @endphp
                    <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom">
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate" style="font-size:.85rem;">
                                {{ $sub->assignment?->title ?? '—' }}
                            </div>
                            <small class="text-muted">
                                {{ $sub->assignment?->subject?->name ?? '—' }}
                            </small>
                        </div>
                        <div class="text-center flex-shrink-0" style="min-width:60px;">
                            <div class="fw-bold text-{{ $clr }}" style="font-size:1.1rem;">{{ $sc }}</div>
                            <span class="badge bg-{{ $clr }} bg-opacity-10 text-{{ $clr }}" style="font-size:.7rem;">
                                Grade {{ $grade }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-tasks fa-2x opacity-25 mb-3 d-block"></i>
                        <p class="mb-0 small">Belum ada nilai tugas</p>
                    </div>
                    @endforelse
                </div>
                @if($assignmentSubmissions->whereNotNull('score')->count() > 0)
                <div class="card-footer bg-white py-2 text-center">
                    <a href="{{ route('siswa.nilai.index') }}" class="small text-primary text-decoration-none">
                        Lihat semua nilai <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Nilai Praktikum --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-flask me-2 text-warning"></i>Nilai Praktikum
                    </h6>
                </div>
                <div class="card-body p-0">
                    @forelse($practicalScores->whereNotNull('score') as $sc)
                    @php
                        $val   = (float)($sc->score ?? 0);
                        $clr   = $val >= 80 ? 'success' : ($val >= 60 ? 'warning' : 'danger');
                        $grade = $val >= 90 ? 'A' : ($val >= 80 ? 'B' : ($val >= 70 ? 'C' : ($val >= 60 ? 'D' : 'E')));
                    @endphp
                    <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom">
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate" style="font-size:.85rem;">
                                {{ $sc->practical?->title ?? '—' }}
                            </div>
                            <small class="text-muted">
                                {{ $sc->practical?->subject?->name ?? '—' }}
                            </small>
                        </div>
                        <div class="text-center flex-shrink-0" style="min-width:60px;">
                            <div class="fw-bold text-{{ $clr }}" style="font-size:1.1rem;">{{ $val }}</div>
                            <span class="badge bg-{{ $clr }} bg-opacity-10 text-{{ $clr }}" style="font-size:.7rem;">
                                Grade {{ $grade }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-flask fa-2x opacity-25 mb-3 d-block"></i>
                        <p class="mb-0 small">Belum ada nilai praktikum</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Ringkasan Kehadiran --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-calendar-check me-2 text-success"></i>Ringkasan Kehadiran
                    </h6>
                    <a href="{{ route('siswa.absensi.index') }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-external-link-alt me-1"></i>Detail
                    </a>
                </div>
                <div class="card-body">
                    @php
                        $hadirCount = $attendances->where('status','hadir')->count();
                        $izinCount  = $attendances->where('status','izin')->count();
                        $sakitCount = $attendances->where('status','sakit')->count();
                        $alphaCount = $attendances->where('status','alpha')->count();
                        $total      = $attendances->count();
                    @endphp
                    <div class="row g-3 text-center">
                        @foreach([
                            ['success', 'fa-user-check', $hadirCount, 'Hadir'],
                            ['info',    'fa-clock',      $izinCount,  'Izin'],
                            ['warning', 'fa-heartbeat',  $sakitCount, 'Sakit'],
                            ['danger',  'fa-user-times', $alphaCount, 'Alpa'],
                        ] as [$color, $icon, $count, $label])
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded-3 bg-{{ $color }} bg-opacity-10">
                                <i class="fas {{ $icon }} text-{{ $color }} fa-lg mb-2 d-block"></i>
                                <div class="h4 fw-bold text-{{ $color }} mb-0">{{ $count }}</div>
                                <small class="text-muted">{{ $label }}</small>
                                @if($total > 0)
                                <div class="text-muted mt-1" style="font-size:.7rem;">
                                    {{ round(($count / $total) * 100) }}%
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
