@extends('layouts.admin')

@section('title', 'Laporan – ' . $period->full_label)
@section('page-title', 'Laporan Periode Pembelajaran')
@section('page-subtitle', $period->full_label . ' · ' . $period->semester_label)

@section('page-actions')
    <a href="{{ route('admin.period-reports.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Semua Periode
    </a>
@endsection

@section('content')

{{-- Header Periode --}}
<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-body p-0">
        <div style="background:linear-gradient(135deg,#0f766e,#0d9488);padding:1.75rem 2rem;">
            <div class="d-flex align-items-center gap-4 flex-wrap">
                <div class="rounded-3 bg-white bg-opacity-20 d-flex align-items-center
                            justify-content-center flex-shrink-0" style="width:64px;height:64px;">
                    <i class="fas fa-calendar-alt text-white fa-2x"></i>
                </div>
                <div class="flex-grow-1">
                    <h4 class="fw-bold text-white mb-1">{{ $period->name }}</h4>
                    <div class="d-flex gap-3 flex-wrap">
                        <span class="badge rounded-pill bg-white bg-opacity-20 text-white">
                            {{ $period->semester_label }}
                        </span>
                        <span class="badge rounded-pill bg-white bg-opacity-20 text-white">
                            <i class="fas fa-calendar me-1"></i>{{ $period->academic_year }}
                        </span>
                        @if($period->is_active)
                        <span class="badge bg-success rounded-pill">
                            <i class="fas fa-check-circle me-1"></i>Aktif
                        </span>
                        @endif
                        @if($period->start_date && $period->end_date)
                        <span class="badge rounded-pill bg-white bg-opacity-20 text-white">
                            {{ $period->start_date->format('d M Y') }} –
                            {{ $period->end_date->format('d M Y') }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Statistik Utama --}}
<div class="row g-3 mb-4">
    @foreach([
        ['Kelas',      $stats['kelas'],       'primary', 'fa-school'],
        ['Materi',     $stats['materials'],   'info',    'fa-file-alt'],
        ['Tugas',      $stats['assignments'], 'warning', 'fa-tasks'],
        ['Praktikum',  $stats['practicals'],  'success', 'fa-flask'],
        ['Absensi',    $stats['attendances'], 'secondary','fa-calendar-check'],
    ] as [$label, $val, $color, $icon])
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 shadow-sm h-100 text-center">
            <div class="card-body py-3">
                <div class="rounded-3 p-2 bg-{{ $color }} bg-opacity-10 d-inline-block mb-2">
                    <i class="fas {{ $icon }} text-{{ $color }}"></i>
                </div>
                <div class="fw-bold fs-4 mb-0">{{ $val }}</div>
                <small class="text-muted">{{ $label }}</small>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Kehadiran --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 shadow-sm h-100 text-center">
            <div class="card-body py-3">
                <div class="rounded-3 p-2 bg-{{ $stats['attendance_rate'] >= 75 ? 'success' : 'warning' }} bg-opacity-10 d-inline-block mb-2">
                    <i class="fas fa-user-check text-{{ $stats['attendance_rate'] >= 75 ? 'success' : 'warning' }}"></i>
                </div>
                <div class="fw-bold fs-4 mb-0 text-{{ $stats['attendance_rate'] >= 75 ? 'success' : 'warning' }}">
                    {{ $stats['attendance_rate'] }}%
                </div>
                <small class="text-muted">Kehadiran</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

{{-- ── KIRI: Nilai + Absensi ── --}}
<div class="col-lg-4">

    {{-- Rata-rata Nilai --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="fas fa-chart-line me-2 text-primary"></i>Rata-rata Nilai
            </h6>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-around">
                <div class="text-center">
                    @php
                        $avgAssign = $stats['avg_assignment'];
                        $colorA = $avgAssign >= 75 ? 'success' : ($avgAssign >= 60 ? 'warning' : 'danger');
                    @endphp
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center
                                bg-{{ $colorA }} bg-opacity-10 mb-2"
                         style="width:72px;height:72px;">
                        <span class="fw-bold fs-5 text-{{ $colorA }}">{{ $avgAssign }}</span>
                    </div>
                    <div class="small text-muted">Tugas</div>
                </div>
                <div class="text-center">
                    @php
                        $avgPrak = $stats['avg_practical'];
                        $colorP = $avgPrak >= 75 ? 'success' : ($avgPrak >= 60 ? 'warning' : 'danger');
                    @endphp
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center
                                bg-{{ $colorP }} bg-opacity-10 mb-2"
                         style="width:72px;height:72px;">
                        <span class="fw-bold fs-5 text-{{ $colorP }}">{{ $avgPrak }}</span>
                    </div>
                    <div class="small text-muted">Praktikum</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Rekap Kehadiran --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="fas fa-calendar-check me-2 text-success"></i>Rekap Kehadiran
            </h6>
        </div>
        <div class="card-body p-0">
            @php
                $totalAbsensi = array_sum($absensiSummary);
            @endphp
            @foreach([
                ['hadir', 'Hadir',   'success'],
                ['izin',  'Izin',    'info'],
                ['sakit', 'Sakit',   'warning'],
                ['alpha', 'Alpha',   'danger'],
            ] as [$key, $label, $color])
            @php
                $count = $absensiSummary[$key] ?? 0;
                $pct   = $totalAbsensi > 0 ? round(($count / $totalAbsensi) * 100, 1) : 0;
            @endphp
            <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }}"
                      style="width:60px;">{{ $label }}</span>
                <div class="flex-grow-1">
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar bg-{{ $color }}" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                <span class="small fw-semibold" style="width:50px;text-align:right;">
                    {{ $count }} <span class="text-muted fw-normal">({{ $pct }}%)</span>
                </span>
            </div>
            @endforeach
        </div>
    </div>

</div>

{{-- ── KANAN: Aktivitas per Kelas ── --}}
<div class="col-lg-8">

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="fas fa-school me-2 text-primary"></i>Aktivitas per Kelas
            </h6>
            <span class="badge bg-secondary">{{ $kelasList->count() }} kelas</span>
        </div>
        @if($kelasList->isEmpty())
        <div class="card-body text-center py-5">
            <i class="fas fa-school fa-3x text-muted opacity-25 mb-3 d-block"></i>
            <p class="text-muted small mb-2">Belum ada kelas yang terhubung ke periode ini.</p>
            <a href="{{ route('admin.kelas.create') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Tambah Kelas
            </a>
        </div>
        @else
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Kelas</th>
                            <th class="text-center">Siswa</th>
                            <th class="text-center">Materi</th>
                            <th class="text-center">Tugas</th>
                            <th class="text-center">Praktikum</th>
                            <th class="text-center">Rata Tugas</th>
                            <th class="text-center pe-4">Rata Praktik</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kelasList as $kelas)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $kelas->name }}</div>
                                <small class="text-muted">
                                    {{ $kelas->grade ?? '' }}
                                    @if($kelas->jurusan) · {{ $kelas->jurusan->name }} @endif
                                </small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    {{ $kelas->siswa_count }}
                                </span>
                            </td>
                            <td class="text-center text-muted">{{ $kelas->count_materials }}</td>
                            <td class="text-center text-muted">{{ $kelas->count_assignments }}</td>
                            <td class="text-center text-muted">{{ $kelas->count_practicals }}</td>
                            <td class="text-center">
                                @php $ca = $kelas->avg_assignment; @endphp
                                <span class="fw-semibold text-{{ $ca >= 75 ? 'success' : ($ca >= 60 ? 'warning' : ($ca > 0 ? 'danger' : 'muted')) }}">
                                    {{ $ca > 0 ? $ca : '—' }}
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                @php $cp = $kelas->avg_practical; @endphp
                                <span class="fw-semibold text-{{ $cp >= 75 ? 'success' : ($cp >= 60 ? 'warning' : ($cp > 0 ? 'danger' : 'muted')) }}">
                                    {{ $cp > 0 ? $cp : '—' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

</div>
</div>

{{-- ── Aktivitas Terbaru ── --}}
<div class="row g-4">

    {{-- Materi Terbaru --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-file-alt me-2 text-info"></i>Materi Terbaru
                </h6>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($recentMaterials as $m)
                <li class="list-group-item px-3 py-2">
                    <div class="fw-semibold small text-truncate" style="max-width:180px;">
                        {{ $m->title }}
                    </div>
                    <small class="text-muted">
                        {{ $m->guru?->name ?? '—' }} ·
                        {{ $m->kelas?->name ?? '—' }}
                    </small>
                </li>
                @empty
                <li class="list-group-item text-center text-muted py-3 small">
                    Belum ada materi
                </li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Tugas Terbaru --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-tasks me-2 text-warning"></i>Tugas Terbaru
                </h6>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($recentAssignments as $a)
                <li class="list-group-item px-3 py-2">
                    <div class="fw-semibold small text-truncate" style="max-width:180px;">
                        {{ $a->title }}
                    </div>
                    <small class="text-muted">
                        {{ $a->guru?->name ?? '—' }} ·
                        {{ $a->kelas?->name ?? '—' }}
                    </small>
                </li>
                @empty
                <li class="list-group-item text-center text-muted py-3 small">
                    Belum ada tugas
                </li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Praktikum Terbaru --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-flask me-2 text-success"></i>Praktikum Terbaru
                </h6>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($recentPracticals as $p)
                <li class="list-group-item px-3 py-2">
                    <div class="fw-semibold small text-truncate" style="max-width:180px;">
                        {{ $p->title }}
                    </div>
                    <small class="text-muted">
                        {{ $p->guru?->name ?? '—' }} ·
                        {{ $p->kelas?->name ?? '—' }}
                    </small>
                </li>
                @empty
                <li class="list-group-item text-center text-muted py-3 small">
                    Belum ada praktikum
                </li>
                @endforelse
            </ul>
        </div>
    </div>

</div>

@endsection
