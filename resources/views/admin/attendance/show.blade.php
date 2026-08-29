@extends('layouts.admin')

@section('title', 'Detail Absensi')
@section('page-title', 'Detail Absensi')
@section('page-subtitle', 'Informasi lengkap data kehadiran siswa.')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.attendance.edit', $attendance) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>
@endsection

@section('content')

<div class="row g-4">
    {{-- Informasi Absensi --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-check me-2"></i>Informasi Absensi</h6>
            </div>
            <div class="card-body">
                <dl class="row g-0">
                    <dt class="col-sm-4 text-muted small py-2 border-bottom">Nama Siswa</dt>
                    <dd class="col-sm-8 fw-semibold py-2 border-bottom">
                        {{ $attendance->siswa->name ?? '—' }}
                    </dd>

                    <dt class="col-sm-4 text-muted small py-2 border-bottom">NIS</dt>
                    <dd class="col-sm-8 py-2 border-bottom">{{ $attendance->siswa->nis ?? '—' }}</dd>

                    <dt class="col-sm-4 text-muted small py-2 border-bottom">Kelas</dt>
                    <dd class="col-sm-8 py-2 border-bottom">{{ $attendance->kelas->name ?? '—' }}</dd>

                    <dt class="col-sm-4 text-muted small py-2 border-bottom">Mata Pelajaran</dt>
                    <dd class="col-sm-8 py-2 border-bottom">{{ $attendance->subject->name ?? '—' }}</dd>

                    <dt class="col-sm-4 text-muted small py-2 border-bottom">Tanggal</dt>
                    <dd class="col-sm-8 py-2 border-bottom">
                        {{ \Carbon\Carbon::parse($attendance->date)->translatedFormat('l, d F Y') }}
                    </dd>

                    <dt class="col-sm-4 text-muted small py-2 border-bottom">Status</dt>
                    <dd class="col-sm-8 py-2 border-bottom">
                        @php
                            $statusBadge = match($attendance->status) {
                                'hadir'  => 'success',
                                'izin'   => 'info',
                                'sakit'  => 'warning',
                                'alpha'  => 'danger',
                                default  => 'secondary',
                            };
                            $statusIcon = match($attendance->status) {
                                'hadir'  => 'check-circle',
                                'izin'   => 'info-circle',
                                'sakit'  => 'heartbeat',
                                'alpha'  => 'times-circle',
                                default  => 'circle',
                            };
                        @endphp
                        <span class="badge bg-{{ $statusBadge }} px-3 py-2">
                            <i class="fas fa-{{ $statusIcon }} me-1"></i>{{ ucfirst($attendance->status) }}
                        </span>
                    </dd>

                    <dt class="col-sm-4 text-muted small py-2 border-bottom">Catatan</dt>
                    <dd class="col-sm-8 py-2 border-bottom">{{ $attendance->note ?? '—' }}</dd>

                    <dt class="col-sm-4 text-muted small py-2 border-bottom">Dicatat Oleh</dt>
                    <dd class="col-sm-8 py-2 border-bottom">{{ $attendance->recorder?->name ?? '—' }}</dd>

                    <dt class="col-sm-4 text-muted small py-2">Waktu Input</dt>
                    <dd class="col-sm-8 py-2">{{ $attendance->created_at->format('d/m/Y H:i') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Sidebar Aksi --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-light border-bottom">
                <h6 class="mb-0 fw-semibold">Aksi</h6>
            </div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('admin.attendance.edit', $attendance) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i>Edit Absensi
                </a>
                <button type="button" class="btn btn-danger"
                        onclick="if(confirm('Hapus data absensi ini?')) document.getElementById('deleteForm').submit()">
                    <i class="fas fa-trash me-2"></i>Hapus Absensi
                </button>
                <form id="deleteForm" action="{{ route('admin.attendance.destroy', $attendance) }}" method="POST">
                    @csrf @method('DELETE')
                </form>
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-list me-2"></i>Daftar Absensi
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light border-bottom">
                <h6 class="mb-0 fw-semibold">Keterangan Status</h6>
            </div>
            <div class="card-body small">
                <div class="d-flex align-items-center mb-2">
                    <span class="badge bg-success me-2 px-2">Hadir</span>
                    <span class="text-muted">Siswa hadir tepat waktu</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <span class="badge bg-info me-2 px-2">Izin</span>
                    <span class="text-muted">Tidak hadir dengan izin resmi</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <span class="badge bg-warning text-dark me-2 px-2">Sakit</span>
                    <span class="text-muted">Tidak hadir karena sakit</span>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-danger me-2 px-2">Alpha</span>
                    <span class="text-muted">Tidak hadir tanpa keterangan</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection