@extends('layouts.siswa')

@section('title', 'Detail Absensi')
@section('page-title', 'Detail Absensi')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('siswa.absensi.index') }}">Absensi</a></li>
    <li class="breadcrumb-item active" aria-current="page">Detail</li>
@endsection

@section('page-actions')
    <a href="{{ route('siswa.absensi.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')
@php
    $statusColors = ['hadir'=>'success','izin'=>'warning','sakit'=>'info','alpha'=>'danger'];
    $sc = $statusColors[$attendance->status] ?? 'secondary';
    $statusLabel = ['hadir'=>'Hadir','izin'=>'Izin','sakit'=>'Sakit','alpha'=>'Tidak Hadir'][$attendance->status] ?? $attendance->status;
@endphp

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-header bg-white border-bottom py-3 px-4" style="border-radius:14px 14px 0 0;">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-check me-2 text-primary"></i>Detail Kehadiran</h6>
                    <span class="badge bg-{{ $sc }} px-3 py-2">{{ $statusLabel }}</span>
                </div>
            </div>
            <div class="card-body px-4 py-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small mb-1">Tanggal</div>
                            <div class="fw-semibold">
                                {{ \Carbon\Carbon::parse($attendance->date ?? $attendance->tanggal)->translatedFormat('l, d F Y') }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small mb-1">Mata Pelajaran</div>
                            <div class="fw-semibold">{{ $attendance->subject?->name ?? '—' }}</div>
                        </div>
                    </div>
                    @if($attendance->note ?? $attendance->keterangan)
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small mb-1">Keterangan</div>
                            <div class="fw-semibold">{{ $attendance->note ?? $attendance->keterangan }}</div>
                        </div>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small mb-1">Dicatat Oleh</div>
                            <div class="fw-semibold">{{ $attendance->recorder?->name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small mb-1">Waktu Input</div>
                            <div class="fw-semibold">{{ $attendance->created_at?->format('d M Y, H:i') ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
