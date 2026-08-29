@extends('layouts.guru')

@section('title', 'Detail Absensi')
@section('page-title', 'Detail Absensi')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('guru.absensi.index') }}">Absensi</a></li>
    <li class="breadcrumb-item active" aria-current="page">Detail</li>
@endsection

@section('page-actions')
    <a href="{{ route('guru.absensi.edit', $absensi->id) }}" class="btn btn-outline-primary btn-sm me-2">
        <i class="fas fa-edit me-1"></i>Edit
    </a>
    <a href="{{ route('guru.absensi.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-header bg-white border-bottom py-3 px-4" style="border-radius:14px 14px 0 0;">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-calendar-check me-2 text-teal"></i>Detail Absensi
                    </h6>
                    @php
                        $statusColors = [
                            'hadir' => 'success',
                            'izin'  => 'warning',
                            'sakit' => 'info',
                            'alpha' => 'danger',
                        ];
                        $sc = $statusColors[$absensi->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $sc }} px-3 py-2 text-uppercase">
                        {{ $absensi->status }}
                    </span>
                </div>
            </div>
            <div class="card-body px-4 py-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light">
                            <div class="text-muted small mb-1">Siswa</div>
                            <div class="fw-semibold">{{ $absensi->siswa?->name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light">
                            <div class="text-muted small mb-1">Tanggal</div>
                            <div class="fw-semibold">
                                {{ \Carbon\Carbon::parse($absensi->date ?? $absensi->tanggal)->translatedFormat('l, d F Y') }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light">
                            <div class="text-muted small mb-1">Kelas</div>
                            <div class="fw-semibold">{{ $absensi->kelas?->name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light">
                            <div class="text-muted small mb-1">Mata Pelajaran</div>
                            <div class="fw-semibold">{{ $absensi->subject?->name ?? '—' }}</div>
                        </div>
                    </div>
                    @if($absensi->note ?? $absensi->keterangan)
                    <div class="col-12">
                        <div class="p-3 rounded-3 bg-light">
                            <div class="text-muted small mb-1">Keterangan</div>
                            <div class="fw-semibold">{{ $absensi->note ?? $absensi->keterangan }}</div>
                        </div>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light">
                            <div class="text-muted small mb-1">Dicatat Oleh</div>
                            <div class="fw-semibold">{{ $absensi->recorder?->name ?? $absensi->createdBy?->name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light">
                            <div class="text-muted small mb-1">Waktu Input</div>
                            <div class="fw-semibold">{{ $absensi->created_at?->format('d M Y, H:i') ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
