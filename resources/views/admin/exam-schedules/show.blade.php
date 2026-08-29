@extends('layouts.admin')

@section('title', 'Detail Jadwal')
@section('page-title', 'Detail Jadwal Ujian')
@section('page-subtitle', $examSchedule->title . ' — ' . strtoupper($examSchedule->exam_type))

@section('page-actions')
    <a href="{{ route('admin.exam-schedules.edit', $examSchedule) }}" class="btn btn-warning btn-sm">
        <i class="fas fa-edit me-1"></i>Edit
    </a>
    @if(!$examSchedule->is_published)
        <form method="POST" action="{{ route('admin.exam-schedules.publish', $examSchedule) }}" class="d-inline ms-1">
            @csrf
            <button type="submit" class="btn btn-success btn-sm"
                    onclick="return confirm('Publikasikan jadwal ini? Notifikasi akan dikirim.')">
                <i class="fas fa-bell me-1"></i>Publikasikan
            </button>
        </form>
    @endif
    <a href="{{ route('admin.exam-schedules.index') }}" class="btn btn-outline-secondary btn-sm ms-1">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')
<div>
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
                            <div class="fw-medium">{{ $examSchedule->subject->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Kelas</label>
                            <div class="fw-medium">{{ $examSchedule->kelas->name ?? 'Semua Kelas' }}</div>
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
                            <div class="fw-medium">{{ $examSchedule->creator?->name ?? '—' }}</div>
                        </div>
                        @if($examSchedule->description)
                        <div class="col-12">
                            <label class="form-label text-muted">Deskripsi</label>
                            <div class="bg-light p-3 rounded">{{ $examSchedule->description }}</div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer border-top d-flex gap-2">
                    <a href="{{ route('admin.exam-schedules.edit', $examSchedule) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    @if(!$examSchedule->is_published)
                        <form method="POST" action="{{ route('admin.exam-schedules.publish', $examSchedule) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm"
                                    onclick="return confirm('Publikasikan jadwal ini? Notifikasi akan dikirim ke guru dan siswa.')">
                                <i class="fas fa-bell me-1"></i>Publikasikan
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('admin.exam-schedules.index') }}" class="btn btn-outline-secondary btn-sm ms-auto">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Status
                    </h6>
                </div>
                <div class="card-body">
                    @if($examSchedule->is_published)
                        <div class="alert alert-success d-flex align-items-center">
                            <i class="fas fa-check-circle me-2"></i>
                            <div>
                                <strong>Telah Dipublikasikan</strong>
                                <p class="mb-0 small">Notifikasi telah dikirim ke guru dan siswa terkait.</p>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <div>
                                <strong>Belum Dipublikasikan</strong>
                                <p class="mb-0 small">Jadwal belum dikirim ke guru dan siswa.</p>
                            </div>
                        </div>
                    @endif
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <strong>Waktu dibuat:</strong> {{ $examSchedule->created_at->format('d M Y H:i') }}<br>
                            <strong>Terakhir diperbarui:</strong> {{ $examSchedule->updated_at->format('d M Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Notification Info Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bell me-2"></i>Notifikasi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Penerima Notifikasi:</label>
                        <div class="small">
                            @if($examSchedule->kelas_id)
                                <i class="fas fa-users me-1"></i>Siswa dan guru kelas {{ $examSchedule->kelas->name }}
                            @else
                                <i class="fas fa-users me-1"></i>Semua siswa dan guru
                            @endif
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Judul Notifikasi:</label>
                        <div class="small text-muted">
                            "Jadwal {{ strtoupper($examSchedule->exam_type) }}: {{ $examSchedule->title }}"
                        </div>
                    </div>
                    
                    <div>
                        <label class="form-label">Isi Notifikasi:</label>
                        <div class="small text-muted">
                            "Jadwal {{ strtoupper($examSchedule->exam_type) }} untuk mata pelajaran {{ $examSchedule->subject->name ?? '—' }} akan dimulai pada {{ $examSchedule->start_time->format('d M Y H:i') }}{{ $examSchedule->location ? " di {$examSchedule->location}" : "" }}"
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions Card -->
            @if($examSchedule->is_published)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-cog me-2"></i>Aksi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Cetak
                        </button>
                        <button class="btn btn-outline-info" onclick="copyLink()">
                            <i class="fas fa-link me-2"></i>Salin Link
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('js')
<script>
function copyLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert('Link berhasil disalin!');
    });
}
</script>
@endpush
@endsection