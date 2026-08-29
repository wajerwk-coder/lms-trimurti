@extends('layouts.guru')

@section('title', 'Detail Pengumpulan Tugas')
@section('page-title', 'Detail Pengumpulan')
@section('page-subtitle', 'Lihat dan nilai tugas yang dikumpulkan siswa.')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('guru.submissions.index') }}">Pengumpulan Tugas</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Detail</li>
@endsection

@push('css')
<style>
.detail-card {
    border: 1px solid #e8edf2 !important;
    border-radius: 14px !important;
    overflow: hidden;
}
.detail-card .card-header {
    background: #f8fafc !important;
    border-bottom: 1px solid #e8edf2 !important;
    padding: .875rem 1.25rem;
}
.section-label {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: #94a3b8;
    margin-bottom: .5rem;
}
.info-row {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    padding: .55rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: .85rem;
}
.info-row:last-child { border-bottom: none; }
.info-row .info-key {
    width: 130px;
    flex-shrink: 0;
    color: #64748b;
    font-weight: 500;
}
.info-row .info-val { color: #1e293b; font-weight: 500; }

/* Score circle */
.score-circle {
    width: 80px; height: 80px;
    border-radius: 50%;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    border: 3px solid;
    margin: 0 auto;
}

/* Timeline */
.tl { list-style: none; padding: 0; margin: 0; position: relative; }
.tl::before {
    content: '';
    position: absolute;
    left: 14px; top: 0; bottom: 0;
    width: 2px;
    background: #e8edf2;
}
.tl-item { display: flex; gap: .75rem; position: relative; padding-bottom: 1.25rem; }
.tl-item:last-child { padding-bottom: 0; }
.tl-dot {
    width: 28px; height: 28px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: .7rem; color: #fff;
    position: relative; z-index: 1;
}
.tl-content { flex: 1; padding-top: 3px; }
.tl-title { font-size: .83rem; font-weight: 600; color: #1e293b; margin-bottom: 2px; }
.tl-time  { font-size: .72rem; color: #94a3b8; }

/* File attachment */
.file-attach {
    display: flex; align-items: center; gap: .75rem;
    padding: .75rem 1rem;
    border: 1.5px dashed #cbd5e1;
    border-radius: 10px;
    background: #f8fafc;
    transition: border-color .15s, background .15s;
}
.file-attach:hover { border-color: #3b82f6; background: #eff6ff; }
.file-attach-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}

/* Quick action buttons */
.action-btn {
    display: flex; align-items: center; gap: .6rem;
    padding: .6rem .9rem;
    border-radius: 10px;
    font-size: .83rem; font-weight: 500;
    text-decoration: none !important;
    transition: background .15s, transform .12s;
    border: 1.5px solid transparent;
}
.action-btn:hover { transform: translateX(2px); }
</style>
@endpush

@section('content')

@php
    $isGraded    = !is_null($submission->score);
    $assignment  = $submission->assignment;
    $siswa       = $submission->siswa;
    $siswaName   = $siswa?->name ?? '—';
    $initial     = strtoupper(substr($siswaName, 0, 1));
    $colors      = ['#0891b2','#7c3aed','#16a34a','#d97706','#dc2626','#0f766e'];
    $avatarBg    = $colors[abs(crc32($siswaName)) % count($colors)];
    $siswaProfile = \App\Models\Siswa::where('user_id', $siswa?->id)->with('kelas')->first();

    $score    = (float) ($submission->score ?? 0);
    $grade    = match(true) { $score >= 90 => 'A', $score >= 80 => 'B', $score >= 70 => 'C', $score >= 60 => 'D', default => 'E' };
    $scoreBorder = match(true) { $score >= 80 => '#16a34a', $score >= 60 => '#d97706', default => '#dc2626' };
    $scoreText   = match(true) { $score >= 80 => 'text-success', $score >= 60 => 'text-warning', default => 'text-danger' };

    $ext = strtolower(pathinfo($submission->file_path ?? '', PATHINFO_EXTENSION));
    [$fileIcon, $fileBg, $fileColor] = match(true) {
        in_array($ext, ['pdf'])          => ['fa-file-pdf',        '#fee2e2','#dc2626'],
        in_array($ext, ['doc','docx'])   => ['fa-file-word',       '#dbeafe','#3b82f6'],
        in_array($ext, ['ppt','pptx'])   => ['fa-file-powerpoint', '#fff7ed','#ea580c'],
        in_array($ext, ['xls','xlsx'])   => ['fa-file-excel',      '#dcfce7','#16a34a'],
        in_array($ext, ['zip','rar'])    => ['fa-file-archive',    '#f3e8ff','#7c3aed'],
        in_array($ext, ['jpg','jpeg','png','gif']) => ['fa-file-image','#fdf4ff','#a21caf'],
        default                          => ['fa-file-alt',        '#f1f5f9','#64748b'],
    };
    $isLate = $submission->is_late ?? false;
@endphp

{{-- Flash --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:12px;">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    {{-- ══ LEFT COLUMN ════════════════════════════════════════════════ --}}
    <div class="col-lg-8">

        {{-- Student info --}}
        <div class="card detail-card shadow-sm mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="fw-semibold" style="font-size:.88rem;">
                    <i class="fas fa-user me-2 text-primary"></i>Informasi Siswa
                </div>
                @if($isGraded)
                    <span class="badge" style="background:#dcfce7;color:#16a34a;border-radius:20px;font-size:.72rem;padding:.25rem .75rem;">
                        <i class="fas fa-check me-1"></i>Sudah Dinilai
                    </span>
                @else
                    <span class="badge" style="background:#fef9c3;color:#a16207;border-radius:20px;font-size:.72rem;padding:.25rem .75rem;">
                        <i class="fas fa-clock me-1"></i>Belum Dinilai
                    </span>
                @endif
            </div>
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                         style="width:52px;height:52px;font-size:1.3rem;background:{{ $avatarBg }};">
                        {{ $initial }}
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">{{ $siswaName }}</h5>
                        <div class="text-muted" style="font-size:.82rem;">
                            {{ $siswa?->email ?? '—' }}
                        </div>
                        @if($siswaProfile?->kelas)
                            <span class="badge mt-1"
                                  style="background:#e0f2fe;color:#0891b2;border-radius:20px;font-size:.7rem;">
                                {{ $siswaProfile->kelas->name }}
                            </span>
                        @endif
                        @if($siswaProfile?->nis)
                            <span class="badge mt-1"
                                  style="background:#f3e8ff;color:#7c3aed;border-radius:20px;font-size:.7rem;">
                                NIS: {{ $siswaProfile->nis }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="section-label">Detail Pengumpulan</div>
                        <div class="info-row">
                            <span class="info-key">Dikumpulkan</span>
                            <span class="info-val">
                                {{ $submission->submitted_at?->format('d M Y, H:i') ?? '—' }}
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Status</span>
                            <span class="info-val">
                                @if($isLate)
                                    <span class="badge" style="background:#fee2e2;color:#dc2626;border-radius:20px;font-size:.7rem;">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Terlambat
                                    </span>
                                @else
                                    <span class="badge" style="background:#dcfce7;color:#16a34a;border-radius:20px;font-size:.7rem;">
                                        <i class="fas fa-check me-1"></i>Tepat Waktu
                                    </span>
                                @endif
                            </span>
                        </div>
                        @if($isGraded)
                        <div class="info-row">
                            <span class="info-key">Dinilai Pada</span>
                            <span class="info-val">
                                {{ $submission->graded_at?->format('d M Y, H:i') ?? '—' }}
                            </span>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="section-label">Informasi Tugas</div>
                        <div class="info-row">
                            <span class="info-key">Judul</span>
                            <span class="info-val">{{ $assignment?->title ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Mata Pelajaran</span>
                            <span class="info-val">{{ $assignment?->subject?->name ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Deadline</span>
                            <span class="info-val">
                                {{ $assignment?->due_date?->format('d M Y, H:i') ?? '—' }}
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Nilai Maks</span>
                            <span class="info-val">{{ $assignment?->max_score ?? 100 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submission content --}}
        <div class="card detail-card shadow-sm mb-4">
            <div class="card-header">
                <div class="fw-semibold" style="font-size:.88rem;">
                    <i class="fas fa-file-alt me-2 text-info"></i>Isi Pengumpulan
                </div>
            </div>
            <div class="card-body py-3">

                {{-- Text content --}}
                @if($submission->submission_text ?? $submission->content)
                    <div class="section-label">Jawaban / Teks</div>
                    <div class="border rounded-3 p-3 mb-3 bg-white"
                         style="font-size:.85rem;line-height:1.7;min-height:80px;white-space:pre-wrap;">
                        {{ $submission->submission_text ?? $submission->content }}
                    </div>
                @endif

                {{-- File attachment --}}
                @if($submission->file_path)
                    <div class="section-label">File Lampiran</div>
                    <div class="file-attach mb-3">
                        <div class="file-attach-icon"
                             style="background:{{ $fileBg }};">
                            <i class="fas {{ $fileIcon }}" style="color:{{ $fileColor }};"></i>
                        </div>
                        <div class="flex-grow-1" style="min-width:0;">
                            <div class="fw-semibold text-dark text-truncate" style="font-size:.85rem;">
                                {{ $submission->file_name ?? basename($submission->file_path) }}
                            </div>
                            @if($submission->file_size)
                                <div class="text-muted" style="font-size:.72rem;">
                                    {{ number_format($submission->file_size / 1024, 1) }} KB
                                </div>
                            @endif
                        </div>
                        <a href="{{ Storage::url($submission->file_path) }}"
                           class="btn btn-sm btn-primary flex-shrink-0"
                           style="border-radius:8px;" target="_blank" download>
                            <i class="fas fa-download me-1"></i>Unduh
                        </a>
                    </div>
                @endif

                @if(!($submission->submission_text ?? $submission->content) && !$submission->file_path)
                    <div class="text-center py-4 text-muted">
                        <div class="rounded-circle bg-secondary bg-opacity-10 d-inline-flex align-items-center
                                     justify-content-center mb-2"
                             style="width:48px;height:48px;">
                            <i class="fas fa-inbox text-secondary"></i>
                        </div>
                        <div class="small">Tidak ada konten yang dikumpulkan.</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Feedback --}}
        @if($isGraded && $submission->feedback)
        <div class="card detail-card shadow-sm">
            <div class="card-header">
                <div class="fw-semibold" style="font-size:.88rem;">
                    <i class="fas fa-comment-dots me-2 text-success"></i>Feedback Guru
                </div>
            </div>
            <div class="card-body py-3">
                <div class="rounded-3 p-3"
                     style="background:#f0fdf4;border:1px solid #bbf7d0;font-size:.85rem;line-height:1.7;">
                    {{ $submission->feedback }}
                </div>
                @if($submission->graded_at)
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-clock me-1"></i>Dinilai {{ $submission->graded_at->diffForHumans() }}
                    </small>
                @endif
            </div>
        </div>
        @endif

        {{-- Grade form (if not graded) --}}
        @if(!$isGraded)
        <div class="card detail-card shadow-sm mt-4">
            <div class="card-header">
                <div class="fw-semibold" style="font-size:.88rem;">
                    <i class="fas fa-star me-2 text-warning"></i>Beri Nilai
                </div>
            </div>
            <div class="card-body py-3">
                <form method="POST" action="{{ route('guru.submissions.grade', $submission->id) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">
                                Nilai <span class="text-danger">*</span>
                                <span class="text-muted fw-normal">(0 – {{ $assignment?->max_score ?? 100 }})</span>
                            </label>
                            <input type="number" name="score" class="form-control @error('score') is-invalid @enderror"
                                   min="0" max="{{ $assignment?->max_score ?? 100 }}"
                                   placeholder="Masukkan nilai..." required
                                   value="{{ old('score') }}"
                                   style="border-radius:8px;">
                            @error('score')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">
                                Feedback <span class="text-muted fw-normal">(opsional)</span>
                            </label>
                            <textarea name="feedback" class="form-control" rows="3"
                                      placeholder="Berikan catatan atau feedback untuk siswa..."
                                      style="border-radius:8px;resize:none;">{{ old('feedback') }}</textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success"
                                    style="border-radius:8px;">
                                <i class="fas fa-check me-2"></i>Simpan Nilai
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif

    </div>

    {{-- ══ RIGHT COLUMN ═══════════════════════════════════════════════ --}}
    <div class="col-lg-4">

        {{-- Score card --}}
        @if($isGraded)
        <div class="card detail-card shadow-sm mb-4">
            <div class="card-header">
                <div class="fw-semibold" style="font-size:.88rem;">
                    <i class="fas fa-chart-bar me-2 text-primary"></i>Hasil Penilaian
                </div>
            </div>
            <div class="card-body text-center py-4">
                <div class="score-circle mx-auto mb-3"
                     style="border-color:{{ $scoreBorder }};">
                    <div class="fw-black {{ $scoreText }}" style="font-size:1.75rem;line-height:1;">
                        {{ number_format($score, 0) }}
                    </div>
                    <div class="text-muted" style="font-size:.65rem;">/ {{ $assignment?->max_score ?? 100 }}</div>
                </div>
                <div class="fw-bold fs-4 {{ $scoreText }} mb-1">Grade: {{ $grade }}</div>
                <div class="text-muted small">
                    {{ $score >= 80 ? 'Sangat Baik' : ($score >= 70 ? 'Baik' : ($score >= 60 ? 'Cukup' : 'Perlu Perbaikan')) }}
                </div>
            </div>
        </div>
        @endif

        {{-- Quick Actions --}}
        <div class="card detail-card shadow-sm mb-4">
            <div class="card-header">
                <div class="fw-semibold" style="font-size:.88rem;">
                    <i class="fas fa-bolt me-2 text-warning"></i>Aksi Cepat
                </div>
            </div>
            <div class="card-body d-flex flex-column gap-2 pt-3">
                @if(!$isGraded)
                    <a href="{{ route('guru.penilaian.edit', $submission->id) }}"
                       class="action-btn text-decoration-none"
                       style="background:#fef9c3;border-color:#fde047;color:#a16207;">
                        <i class="fas fa-star"></i>
                        <span>Beri Nilai via Penilaian</span>
                    </a>
                @else
                    <a href="{{ route('guru.penilaian.edit', $submission->id) }}"
                       class="action-btn text-decoration-none"
                       style="background:#dcfce7;border-color:#86efac;color:#16a34a;">
                        <i class="fas fa-edit"></i>
                        <span>Edit Nilai</span>
                    </a>
                @endif

                @if($submission->file_path)
                    <a href="{{ Storage::url($submission->file_path) }}"
                       class="action-btn text-decoration-none"
                       style="background:#dbeafe;border-color:#93c5fd;color:#1d4ed8;"
                       target="_blank" download>
                        <i class="fas fa-download"></i>
                        <span>Unduh File Tugas</span>
                    </a>
                @endif

                <a href="{{ route('guru.submissions.index') }}"
                   class="action-btn text-decoration-none"
                   style="background:#f1f5f9;border-color:#cbd5e1;color:#475569;">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali ke Daftar</span>
                </a>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="card detail-card shadow-sm">
            <div class="card-header">
                <div class="fw-semibold" style="font-size:.88rem;">
                    <i class="fas fa-history me-2 text-secondary"></i>Timeline
                </div>
            </div>
            <div class="card-body py-3">
                <ul class="tl">
                    <li class="tl-item">
                        <div class="tl-dot" style="background:#3b82f6;">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="tl-content">
                            <div class="tl-title">Tugas Dibuat</div>
                            <div class="tl-time">
                                {{ $assignment?->created_at?->format('d M Y') ?? '—' }}
                            </div>
                        </div>
                    </li>
                    @if($submission->submitted_at)
                    <li class="tl-item">
                        <div class="tl-dot" style="background:{{ $isLate ? '#dc2626' : '#0891b2' }};">
                            <i class="fas fa-upload" style="font-size:.6rem;"></i>
                        </div>
                        <div class="tl-content">
                            <div class="tl-title">
                                Dikumpulkan
                                @if($isLate)
                                    <span class="badge" style="font-size:.62rem;background:#fee2e2;color:#dc2626;border-radius:10px;padding:.1rem .4rem;">
                                        Terlambat
                                    </span>
                                @endif
                            </div>
                            <div class="tl-time">
                                {{ $submission->submitted_at->format('d M Y, H:i') }}
                                <span class="ms-1">({{ $submission->submitted_at->diffForHumans() }})</span>
                            </div>
                        </div>
                    </li>
                    @endif
                    @if($submission->graded_at)
                    <li class="tl-item">
                        <div class="tl-dot" style="background:#16a34a;">
                            <i class="fas fa-check" style="font-size:.6rem;"></i>
                        </div>
                        <div class="tl-content">
                            <div class="tl-title">Dinilai</div>
                            <div class="tl-time">
                                {{ $submission->graded_at->format('d M Y, H:i') }}
                            </div>
                            <div class="tl-time">
                                Nilai: <strong class="{{ $scoreText }}">{{ number_format($score, 0) }}</strong>
                                (Grade {{ $grade }})
                            </div>
                        </div>
                    </li>
                    @else
                    <li class="tl-item">
                        <div class="tl-dot" style="background:#d97706;">
                            <i class="fas fa-clock" style="font-size:.6rem;"></i>
                        </div>
                        <div class="tl-content">
                            <div class="tl-title">Menunggu Penilaian</div>
                            <div class="tl-time">Belum dinilai</div>
                        </div>
                    </li>
                    @endif
                </ul>
            </div>
        </div>

    </div>
</div>

@endsection
