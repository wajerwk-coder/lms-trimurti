@php
    $role      = Auth::user()->role ?? 'guest';
    $layout    = match($role) { 'guru' => 'layouts.guru', 'admin' => 'layouts.admin', default => 'layouts.siswa' };
    $backRoute = match($role) { 'guru' => 'guru.jadwal-ujian.index', 'admin' => 'admin.exam-schedules.index', default => 'siswa.jadwal-ujian.index' };
@endphp

@extends($layout)

@section('title', 'Detail Jadwal Ujian')
@section('page-title', 'Detail Jadwal Ujian')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route($backRoute) }}">Jadwal Ujian</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Detail</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-header bg-white border-bottom py-3 px-4"
                 style="border-radius:14px 14px 0 0;">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold">{{ $schedule->title }}</h5>
                    @php
                        $tc = ['uts'=>'info','uas'=>'danger','quiz'=>'warning','praktikum'=>'success'][$schedule->exam_type ?? ''] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $tc }} fs-6">
                        {{ strtoupper($schedule->exam_type ?? '—') }}
                    </span>
                </div>
            </div>
            <div class="card-body px-4 py-4">
                @if($schedule->description)
                    <p class="text-muted mb-4">{{ $schedule->description }}</p>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light">
                            <div class="text-muted small mb-1">Mata Pelajaran</div>
                            <div class="fw-semibold">{{ $schedule->subject?->name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light">
                            <div class="text-muted small mb-1">Kelas</div>
                            <div class="fw-semibold">{{ $schedule->kelas?->name ?? 'Semua Kelas' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light">
                            <div class="text-muted small mb-1">Tanggal</div>
                            <div class="fw-semibold">
                                {{ $schedule->start_time?->translatedFormat('l, d F Y') ?? '—' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light">
                            <div class="text-muted small mb-1">Waktu</div>
                            <div class="fw-semibold">
                                {{ $schedule->start_time?->format('H:i') ?? '—' }}
                                &ndash;
                                {{ $schedule->end_time?->format('H:i') ?? '—' }}
                                <span class="text-muted">({{ $schedule->duration_minutes }} menit)</span>
                            </div>
                        </div>
                    </div>
                    @if($schedule->location)
                    <div class="col-12">
                        <div class="p-3 rounded-3 bg-light">
                            <div class="text-muted small mb-1">Lokasi</div>
                            <div class="fw-semibold">{{ $schedule->location }}</div>
                        </div>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light">
                            <div class="text-muted small mb-1">Status</div>
                            @php
                                $sc = match(true) {
                                    str_contains(strtolower($schedule->status ?? ''), 'berlangsung') => 'success',
                                    str_contains(strtolower($schedule->status ?? ''), 'akan')        => 'warning',
                                    str_contains(strtolower($schedule->status ?? ''), 'selesai')     => 'secondary',
                                    default => 'light',
                                };
                            @endphp
                            <span class="badge bg-{{ $sc }} px-3 py-2">
                                {{ $schedule->status ?? '—' }}
                            </span>
                        </div>
                    </div>
                </div>

                <a href="{{ route($backRoute) }}"
                   class="btn btn-outline-secondary" style="border-radius:8px;">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
