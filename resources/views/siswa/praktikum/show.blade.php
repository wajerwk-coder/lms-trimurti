@extends('layouts.siswa')

@section('title', $practical->title)
@section('page-title', 'Detail Praktikum')
@section('page-subtitle', $practical->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('siswa.praktikum.index') }}">Praktikum</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($practical->title, 30) }}</li>
@endsection

@push('css')
<style>
.info-card { border:1px solid #e8edf2!important;border-radius:12px!important; }
.info-row { display:flex;align-items:flex-start;gap:.75rem;padding:.55rem 0;border-bottom:1px solid #f1f5f9;font-size:.85rem; }
.info-row:last-child { border-bottom:none; }
.info-key { width:140px;flex-shrink:0;color:#64748b;font-weight:500; }
.sop-item { padding:.5rem .75rem;border-radius:8px;border:1px solid #e2e8f0;margin-bottom:.4rem;background:#f8fafc;display:flex;align-items:center;gap:.6rem; }
.sop-item.checked { background:#dcfce7;border-color:#86efac; }
.kriteria-card { border:1px solid #e8edf2;border-radius:12px;overflow:hidden;margin-bottom:1rem; }
.kriteria-header { padding:.75rem 1rem;background:#f8fafc;border-bottom:1px solid #e8edf2;display:flex;align-items:center;justify-content:space-between; }
</style>
@endpush

@section('content')

@php
    $isGraded      = $scores->isNotEmpty();
    $summaryScore  = $scores->whereNull('criteria_id')->first();
    $detailScores  = $scores->whereNotNull('criteria_id');
    $finalScore    = $summaryScore?->score ?? ($isGraded ? round($scores->avg('score'), 1) : null);
    $grade         = match(true) {
        $finalScore === null => '—',
        $finalScore >= 90    => 'A', $finalScore >= 80 => 'B',
        $finalScore >= 70    => 'C', $finalScore >= 60 => 'D',
        default              => 'E',
    };
    $gradeColor    = match($grade) {
        'A'     => '#16a34a', 'B' => '#3b82f6',
        'C'     => '#d97706', 'D' => '#ef4444',
        default => '#94a3b8',
    };
    $katColors = ['persiapan'=>'info','pelaksanaan'=>'primary','hasil'=>'success','sikap'=>'warning'];
@endphp

<div class="row g-4">

    {{-- ── KIRI: Detail Praktikum ──────────────────────────────── --}}
    <div class="col-lg-8">

        {{-- Info utama --}}
        <div class="card info-card shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4" style="border-radius:12px 12px 0 0;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:42px;height:42px;background:linear-gradient(135deg,#7c3aed,#db2777);">
                        <i class="fas fa-flask text-white"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">{{ $practical->title }}</h5>
                        <div class="text-muted small">{{ $practical->subject?->name ?? '—' }}</div>
                    </div>
                </div>
            </div>
            <div class="card-body px-4 py-3">
                <div class="info-row">
                    <span class="info-key">Guru</span>
                    <span class="fw-semibold">{{ $practical->guru?->name ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Kelas</span>
                    <span class="fw-semibold">{{ $practical->kelas?->name ?? 'Semua Kelas' }}</span>
                </div>
                @if($practical->due_date)
                <div class="info-row">
                    <span class="info-key">Batas Waktu</span>
                    <span class="fw-semibold {{ $practical->due_date->isPast() ? 'text-danger' : '' }}">
                        {{ $practical->due_date->translatedFormat('d F Y') }}
                    </span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-key">Status Nilai</span>
                    <span>
                        @if($isGraded)
                            <span class="badge" style="background:#dcfce7;color:#16a34a;border-radius:20px;padding:.2rem .65rem;">
                                <i class="fas fa-check me-1"></i>Sudah Dinilai
                            </span>
                        @else
                            <span class="badge" style="background:#fef9c3;color:#a16207;border-radius:20px;padding:.2rem .65rem;">
                                <i class="fas fa-clock me-1"></i>Belum Dinilai
                            </span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Deskripsi & Instruksi --}}
        @if($practical->description)
        <div class="card info-card shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4" style="border-radius:12px 12px 0 0;">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2 text-info"></i>Deskripsi</h6>
            </div>
            <div class="card-body px-4 py-3" style="font-size:.875rem;line-height:1.7;color:#374151;">
                {!! nl2br(e($practical->description)) !!}
            </div>
        </div>
        @endif

        @if($practical->instructions)
        <div class="card info-card shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4" style="border-radius:12px 12px 0 0;">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-list-ol me-2 text-primary"></i>Instruksi</h6>
            </div>
            <div class="card-body px-4 py-3" style="font-size:.875rem;line-height:1.7;color:#374151;">
                {!! nl2br(e($practical->instructions)) !!}
            </div>
        </div>
        @endif

        {{-- Detail penilaian per kriteria --}}
        @if($detailScores->isNotEmpty())
        <h6 class="fw-semibold mb-3"><i class="fas fa-clipboard-check me-2 text-success"></i>Detail Penilaian per Kriteria</h6>
        @foreach($detailScores->groupBy('criteria.kategori') as $kategori => $group)
        @php $kColor = $katColors[$kategori] ?? 'secondary'; @endphp
        <div class="kriteria-card shadow-sm">
            <div class="kriteria-header">
                <span class="fw-semibold text-{{ $kColor }}">{{ ucfirst($kategori ?? 'Lainnya') }}</span>
                <span class="badge bg-{{ $kColor }}">{{ count($group) }} kriteria</span>
            </div>
            <div class="card-body p-3">
                @foreach($group as $sc)
                @php
                    $sopData   = is_string($sc->feedback) ? (json_decode($sc->feedback, true) ?? []) : [];
                    $checkedSop= $sopData['checked_sop'] ?? [];
                    $totalSop  = $sopData['total_sop']   ?? 0;
                    $kName     = $sopData['kriteria_name'] ?? $sc->criteria?->name ?? 'Kriteria';
                    $scVal     = (float) $sc->score;
                @endphp
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <div>
                        <div class="fw-semibold" style="font-size:.85rem;">{{ $kName }}</div>
                        @if($totalSop > 0)
                            <div class="text-muted" style="font-size:.73rem;">
                                {{ count($checkedSop) }}/{{ $totalSop }} SOP terpenuhi
                            </div>
                        @endif
                    </div>
                    <span class="fw-bold" style="font-size:.95rem;color:{{ $scVal >= 80 ? '#16a34a' : ($scVal >= 60 ? '#d97706' : '#dc2626') }};">
                        {{ number_format($scVal, 0) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
        @endif

    </div>

    {{-- ── KANAN: Nilai ─────────────────────────────────────────── --}}
    <div class="col-lg-4">

        {{-- Nilai card --}}
        @if($isGraded && $finalScore !== null)
        <div class="card info-card shadow-sm mb-4 text-center">
            <div class="card-header bg-white border-bottom py-3 px-4" style="border-radius:12px 12px 0 0;">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-star me-2 text-warning"></i>Nilai Kamu</h6>
            </div>
            <div class="card-body py-4">
                <div class="rounded-circle d-inline-flex flex-column align-items-center justify-content-center mb-3"
                     style="width:90px;height:90px;border:4px solid {{ $gradeColor }};">
                    <div class="fw-black" style="font-size:2rem;line-height:1;color:{{ $gradeColor }};">
                        {{ number_format($finalScore, 0) }}
                    </div>
                    <div style="font-size:.65rem;color:#94a3b8;">/100</div>
                </div>
                <div class="fw-bold fs-4" style="color:{{ $gradeColor }};">Grade {{ $grade }}</div>
                <div class="text-muted small">
                    {{ $finalScore >= 80 ? 'Sangat Baik' : ($finalScore >= 70 ? 'Baik' : ($finalScore >= 60 ? 'Cukup' : 'Perlu Perbaikan')) }}
                </div>
                @if($summaryScore?->feedback && !str_starts_with($summaryScore->feedback, '{'))
                    <div class="mt-3 p-3 rounded-3 bg-light text-start" style="font-size:.82rem;">
                        <div class="text-muted small mb-1">Catatan Guru:</div>
                        {{ $summaryScore->feedback }}
                    </div>
                @endif
            </div>
        </div>
        @else
        <div class="card info-card shadow-sm mb-4 text-center">
            <div class="card-body py-4 text-muted">
                <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:64px;height:64px;">
                    <i class="fas fa-clock text-warning fa-xl"></i>
                </div>
                <h6 class="text-muted mb-1">Belum Dinilai</h6>
                <p class="small mb-0">Nilai akan muncul setelah guru melakukan penilaian.</p>
            </div>
        </div>
        @endif

        {{-- Kembali --}}
        <a href="{{ route('siswa.praktikum.index') }}"
           class="btn btn-outline-secondary w-100" style="border-radius:10px;">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
        </a>
    </div>

</div>

@endsection
