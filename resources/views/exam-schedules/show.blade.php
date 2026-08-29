@extends('layouts.base')

@section('title', 'Detail Jadwal Ujian')
@section('page-title', 'Detail Jadwal Ujian')
@section('page-subtitle', 'Informasi lengkap jadwal ujian')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
    <li class="breadcrumb-item active" aria-current="page">Jadwal Ujian</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="row">
        <div class="col-lg-8">
            <!-- Main Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-check me-2"></i>{{ $examSchedule->title }}
                    </h5>
                    <span class="badge bg-light text-primary">
                        {{ strtoupper($examSchedule->exam_type) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Mata Pelajaran</label>
                            <div class="fw-medium">{{ $examSchedule->subject->nama ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Kelas</label>
                            <div class="fw-medium">{{ $examSchedule->kelas->nama ?? 'Semua Kelas' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Waktu Mulai</label>
                            <div class="fw-medium">
                                <i class="fas fa-calendar me-1"></i>{{ $examSchedule->start_time->format('d M Y H:i') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Waktu Selesai</label>
                            <div class="fw-medium">
                                <i class="fas fa-calendar me-1"></i>{{ $examSchedule->end_time->format('d M Y H:i') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Durasi</label>
                            <div class="fw-medium">{{ $examSchedule->duration_formatted }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Lokasi</label>
                            <div class="fw-medium">{{ $examSchedule->location ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Status</label>
                            <div>
                                <span class="badge bg-{{ $examSchedule->status_color }}">
                                    {{ $examSchedule->status }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Dibuat oleh</label>
                            <div class="fw-medium">{{ $examSchedule->creator->name }}</div>
                        </div>
                        @if($examSchedule->description)
                        <div class="col-12">
                            <label class="form-label text-muted">Deskripsi</label>
                            <div class="bg-light p-3 rounded">{{ $examSchedule->description }}</div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex gap-2">
                        <a href="{{ route('siswa.assignments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Cetak Jadwal
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Status Ujian
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>
                            <strong>{{ $examSchedule->status }}</strong>
                            <p class="mb-0 small">
                                @if($examSchedule->status == 'Akan Datang')
                                    Ujian akan dimulai dalam {{ $examSchedule->start_time->diffForHumans() }}
                                @elseif($examSchedule->status == 'Sedang Berlangsung')
                                    Ujian sedang berlangsung, selesaikan sebelum {{ $examSchedule->end_time->format('H:i') }}
                                @else
                                    Ujian telah selesai
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <strong>Waktu dibuat:</strong> {{ $examSchedule->created_at->format('d M Y H:i') }}<br>
                            <strong>Terakhir diperbarui:</strong> {{ $examSchedule->updated_at->format('d M Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Actions Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light text-dark">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-cog me-2"></i>Aksi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Cetak Jadwal
                        </button>
                        <button class="btn btn-outline-info" onclick="addToCalendar()">
                            <i class="fas fa-calendar-plus me-2"></i>Tambah ke Kalender
                        </button>
                        <button class="btn btn-outline-success" onclick="shareSchedule()">
                            <i class="fas fa-share-alt me-2"></i>Bagikan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addToCalendar() {
    const title = '{{ $examSchedule->title }}';
    const start = '{{ $examSchedule->start_time->format('Y-m-d\TH:i:s') }}';
    const end = '{{ $examSchedule->end_time->format('Y-m-d\TH:i:s') }}';
    const location = '{{ $examSchedule->location ?? '' }}';
    const description = '{{ $examSchedule->description ?? '' }}';
    
    const googleCalendarUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(title)}&dates=${start.replace(/[-:]/g, '')}/${end.replace(/[-:]/g, '')}&details=${encodeURIComponent(description)}&location=${encodeURIComponent(location)}`;
    
    window.open(googleCalendarUrl, '_blank');
}

function shareSchedule() {
    const url = window.location.href;
    const title = '{{ $examSchedule->title }}';
    const text = `Jadwal ${title} - {{ $examSchedule->start_time->format('d M Y H:i') }}`;
    
    if (navigator.share) {
        navigator.share({
            title: title,
            text: text,
            url: url
        });
    } else {
        navigator.clipboard.writeText(`${text} - ${url}`).then(() => {
            alert('Link berhasil disalin!');
        });
    }
}
</script>
@endsection