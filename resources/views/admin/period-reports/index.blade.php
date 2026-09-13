@extends('layouts.admin')

@section('title', 'Laporan per Periode')
@section('page-title', 'Laporan Periode Pembelajaran')
@section('page-subtitle', 'Ringkasan aktivitas guru dan siswa per semester/tahun ajaran.')

@section('page-actions')
    <a href="{{ route('admin.academic-periods.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Periode
    </a>
@endsection

@section('content')

{{-- Periode Aktif Banner --}}
@if($activePeriod)
<div class="alert border-0 mb-4"
     style="background:linear-gradient(135deg,#0f766e,#0d9488);color:#fff;border-radius:14px;">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-3 bg-white bg-opacity-20 p-3 flex-shrink-0">
            <i class="fas fa-calendar-check fa-lg text-white"></i>
        </div>
        <div class="flex-grow-1">
            <div class="fw-bold fs-6">Periode Aktif: {{ $activePeriod->full_label }}</div>
            @if($activePeriod->start_date && $activePeriod->end_date)
            <small class="opacity-75">
                {{ $activePeriod->start_date->format('d M Y') }} –
                {{ $activePeriod->end_date->format('d M Y') }}
            </small>
            @endif
        </div>
        <a href="{{ route('admin.period-reports.show', $activePeriod) }}"
           class="btn btn-light btn-sm ms-auto">
            <i class="fas fa-chart-bar me-1"></i>Lihat Laporan
        </a>
    </div>
</div>
@endif

@if($periods->isEmpty())
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="fas fa-calendar-alt fa-3x text-muted opacity-25 mb-3 d-block"></i>
        <h6 class="text-muted">Belum ada periode pembelajaran</h6>
        <p class="text-muted small mb-3">Buat periode pembelajaran terlebih dahulu.</p>
        <a href="{{ route('admin.academic-periods.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Buat Periode
        </a>
    </div>
</div>
@else

{{-- Grid Kartu Per Periode --}}
<div class="row g-4">
    @foreach($periods as $period)
    @php
        $colors = ['primary','success','info','warning','danger','secondary'];
        $color  = $colors[$loop->index % count($colors)];
    @endphp
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">

            {{-- Header --}}
            <div class="card-header py-3 border-bottom"
                 style="background:var(--bs-{{ $color }}-bg-subtle, #f0f4ff);">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-3 p-2 bg-{{ $color }} bg-opacity-10">
                            <i class="fas fa-calendar-alt text-{{ $color }}"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small">{{ $period->full_label }}</div>
                            <small class="text-muted">{{ $period->kelas_count }} kelas</small>
                        </div>
                    </div>
                    @if($period->is_active)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-secondary">Nonaktif</span>
                    @endif
                </div>
            </div>

            {{-- Statistik --}}
            <div class="card-body py-3">
                <div class="row g-2 text-center mb-3">
                    <div class="col-4">
                        <div class="fw-bold text-primary">{{ $period->stat_materials }}</div>
                        <small class="text-muted d-block" style="font-size:.7rem;">Materi</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-warning">{{ $period->stat_assignments }}</div>
                        <small class="text-muted d-block" style="font-size:.7rem;">Tugas</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-success">{{ $period->stat_practicals }}</div>
                        <small class="text-muted d-block" style="font-size:.7rem;">Praktikum</small>
                    </div>
                </div>

                {{-- Progress kehadiran --}}
                <div class="mb-2">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Kehadiran</span>
                        <span class="fw-semibold text-{{ $period->attendance_rate >= 75 ? 'success' : 'warning' }}">
                            {{ $period->attendance_rate }}%
                        </span>
                    </div>
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar bg-{{ $period->attendance_rate >= 75 ? 'success' : 'warning' }}"
                             style="width:{{ $period->attendance_rate }}%"></div>
                    </div>
                </div>

                {{-- Rata-rata nilai --}}
                <div class="row g-2 mt-1">
                    <div class="col-6">
                        <div class="rounded-2 p-2 bg-light text-center">
                            <div class="fw-bold text-warning small">{{ $period->avg_assignment }}</div>
                            <small class="text-muted" style="font-size:.65rem;">Rata Tugas</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="rounded-2 p-2 bg-light text-center">
                            <div class="fw-bold text-success small">{{ $period->avg_practical }}</div>
                            <small class="text-muted" style="font-size:.65rem;">Rata Praktik</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="card-footer bg-white border-top py-2">
                <a href="{{ route('admin.period-reports.show', $period) }}"
                   class="btn btn-outline-{{ $color }} btn-sm w-100">
                    <i class="fas fa-chart-bar me-1"></i>Lihat Detail Laporan
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
