@extends('layouts.siswa')

@section('title', 'Praktikum')
@section('page-title', 'Praktikum')
@section('page-subtitle', 'Daftar sesi praktikum dari guru untuk kelas Anda.')

@push('css')
<style>
/* ── Stats ─────────────────────────────────────────── */
.prak-stat {
    border: none; border-radius: 14px; overflow: hidden;
    transition: transform .18s, box-shadow .18s;
    text-decoration: none !important;
}
.prak-stat:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(0,0,0,.09) !important; }
.prak-stat-icon {
    width: 44px; height: 44px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; color: #fff; flex-shrink: 0;
}

/* ── Praktikum cards ────────────────────────────────── */
.prak-card {
    border: 1px solid #e8edf2 !important;
    border-radius: 14px !important;
    transition: transform .18s, box-shadow .18s;
    overflow: hidden;
}
.prak-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0,0,0,.10) !important;
}

/* ── Filter bar ─────────────────────────────────────── */
.filter-bar {
    background: #fff; border: 1px solid #e8edf2;
    border-radius: 14px; padding: .875rem 1.25rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}
.progress-xs { height: 6px; border-radius: 3px; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:12px;">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:12px;">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ══ STATS ════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    @php
    $statItems = [
        ['from'=>'#d97706','to'=>'#b45309','icon'=>'fa-flask',        'val'=>$totalAll,      'label'=>'Total Praktikum', 'filter'=>''],
        ['from'=>'#16a34a','to'=>'#15803d','icon'=>'fa-star',         'val'=>$gradedCount,   'label'=>'Sudah Dinilai',   'filter'=>'graded'],
        ['from'=>'#0891b2','to'=>'#0e7490','icon'=>'fa-calendar-alt', 'val'=>$upcomingCount, 'label'=>'Akan Datang',     'filter'=>'upcoming'],
        ['from'=>'#7c3aed','to'=>'#6d28d9','icon'=>'fa-chart-line',   'val'=>$averageScore,  'label'=>'Rata-rata Nilai', 'filter'=>''],
    ];
    @endphp
    @foreach($statItems as $s)
    <div class="col-6 col-md-3">
        @if($s['filter'])
        <a href="{{ route('siswa.praktikum.index', array_merge(request()->query(), ['status'=>$s['filter']])) }}"
           class="card prak-stat shadow-sm h-100 d-block"
           style="{{ $status === $s['filter'] ? 'box-shadow:0 0 0 2.5px '.$s['from'].' !important;' : '' }}">
        @else
        <div class="card prak-stat shadow-sm h-100">
        @endif
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="prak-stat-icon"
                     style="background:linear-gradient(135deg,{{ $s['from'] }},{{ $s['to'] }});">
                    <i class="fas {{ $s['icon'] }}"></i>
                </div>
                <div>
                    <div class="fw-black text-dark" style="font-size:1.55rem;line-height:1;letter-spacing:-.5px;">
                        {{ $s['val'] }}
                    </div>
                    <div class="fw-semibold text-dark" style="font-size:.78rem;">{{ $s['label'] }}</div>
                </div>
            </div>
            <div style="height:3px;background:linear-gradient(90deg,{{ $s['from'] }},{{ $s['to'] }});"></div>
        @if($s['filter'])</a>@else</div>@endif
    </div>
    @endforeach
</div>

{{-- ══ FILTER ════════════════════════════════════════════ --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('siswa.praktikum.index') }}"
          class="row g-2 align-items-center">
        {{-- Status pills --}}
        <div class="col-12 col-md-8">
            <div class="d-flex gap-1 flex-wrap">
                @foreach([
                    ''         => ['Semua',         '#d97706'],
                    'upcoming' => ['Akan Datang',   '#0891b2'],
                    'past'     => ['Sudah Lewat',   '#64748b'],
                    'graded'   => ['Sudah Dinilai', '#16a34a'],
                    'ungraded' => ['Belum Dinilai', '#dc2626'],
                ] as $val => [$label, $clr])
                @php $isActive = $status === $val; @endphp
                <a href="{{ route('siswa.praktikum.index', array_merge(request()->query(), ['status'=>$val, 'page'=>1])) }}"
                   class="btn btn-sm fw-semibold"
                   style="border-radius:20px;
                          background:{{ $isActive ? $clr : '#f1f5f9' }};
                          color:{{ $isActive ? '#fff' : '#64748b' }};
                          border:none;font-size:.78rem;">
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </div>
        {{-- Search --}}
        <div class="col-md-3">
            <div class="input-group" style="border:1.5px solid #e2e8f0;border-radius:9px;overflow:hidden;">
                <span class="input-group-text border-0 bg-white ps-3">
                    <i class="fas fa-search text-muted" style="font-size:.8rem;"></i>
                </span>
                <input type="text" name="search" class="form-control border-0"
                       placeholder="Cari praktikum…" value="{{ $search }}"
                       style="box-shadow:none;">
            </div>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn w-100 fw-semibold"
                    style="border-radius:9px;background:#d97706;color:#fff;border:none;">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>
</div>

{{-- ══ GRID PRAKTIKUM ════════════════════════════════════ --}}
<div class="row g-3">
    @forelse($practicals as $practical)
    @php
        $dueDate     = $practical->due_date;
        $isPast      = $dueDate && $dueDate->isPast();
        $isToday     = $dueDate && $dueDate->isToday();
        $urgentHours = $dueDate && !$isPast && $dueDate->diffInHours(now()) <= 24;

        // Score summary (criteria_id IS NULL = nilai akhir)
        $scoreRecord = $practical->scores->first();
        $isGraded    = $scoreRecord && $scoreRecord->score !== null;
        $scoreVal    = $isGraded ? (float)$scoreRecord->score : null;
        $maxScore    = $practical->max_score ?? 100;
        $pct         = $isGraded ? min(100, ($scoreVal / $maxScore) * 100) : 0;
        $grade       = $pct >= 90 ? 'A' : ($pct >= 80 ? 'B' : ($pct >= 70 ? 'C' : ($pct >= 60 ? 'D' : 'E')));

        $scoreClr    = $pct >= 80 ? '#16a34a' : ($pct >= 60 ? '#d97706' : '#dc2626');
        $scoreBg     = $pct >= 80 ? 'rgba(22,163,74,.09)' : ($pct >= 60 ? 'rgba(217,119,6,.09)' : 'rgba(220,38,38,.09)');

        // Status config
        [$sClr, $sBg, $sLabel, $sIcon] = match(true) {
            $isGraded    => ['#16a34a', 'rgba(22,163,74,.09)',   'Sudah Dinilai', 'fa-star'],
            $isToday     => ['#d97706', 'rgba(217,119,6,.09)',   'Hari Ini!',     'fa-exclamation-circle'],
            $urgentHours => ['#d97706', 'rgba(217,119,6,.09)',   'Segera',        'fa-exclamation-triangle'],
            $isPast      => ['#64748b', 'rgba(100,116,139,.09)', 'Sudah Lewat',   'fa-check-circle'],
            default      => ['#0891b2', 'rgba(8,145,178,.09)',   'Akan Datang',   'fa-calendar-alt'],
        };
    @endphp

    <div class="col-md-6 col-xl-4">
        <div class="card prak-card shadow-sm h-100">

            {{-- Top bar --}}
            <div style="height:4px;background:{{ $sClr }};"></div>

            <div class="card-body p-4">

                {{-- Title + badge --}}
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <h6 class="fw-bold mb-0 lh-sm flex-grow-1" style="font-size:.92rem;">
                        {{ $practical->title }}
                    </h6>
                    <span class="badge flex-shrink-0 fw-semibold"
                          style="background:{{ $sBg }};color:{{ $sClr }};border-radius:20px;
                                 font-size:.7rem;padding:.22rem .7rem;white-space:nowrap;">
                        <i class="fas {{ $sIcon }} me-1" style="font-size:.6rem;"></i>{{ $sLabel }}
                    </span>
                </div>

                {{-- Subject badge --}}
                @if($practical->subject)
                    <div class="mb-2">
                        <span class="badge fw-semibold"
                              style="background:rgba(217,119,6,.1);color:#d97706;border-radius:20px;font-size:.7rem;padding:.18rem .6rem;">
                            <i class="fas fa-flask me-1"></i>{{ $practical->subject->name }}
                        </span>
                    </div>
                @endif

                {{-- Description --}}
                @if($practical->description)
                    <p class="text-muted mb-3 lh-sm"
                       style="font-size:.8rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                        {{ strip_tags($practical->description) }}
                    </p>
                @endif

                {{-- Meta info --}}
                <div class="d-flex flex-column gap-1 mb-3" style="font-size:.78rem;">
                    <div class="text-muted">
                        <i class="fas fa-user-tie me-1 opacity-75"></i>
                        {{ $practical->guru?->name ?? '—' }}
                    </div>
                    <div class="{{ $isPast ? '' : 'text-muted' }}"
                         style="{{ $isPast && !$isGraded ? 'color:#dc2626;' : '' }}">
                        <i class="fas fa-clock me-1 opacity-75"></i>
                        @if($dueDate)
                            {{ $dueDate->format('d M Y, H:i') }}
                            <span style="color:{{ $isPast ? '#dc2626' : '#0891b2' }};">
                                ({{ $dueDate->diffForHumans() }})
                            </span>
                        @else
                            Tidak ada batas waktu
                        @endif
                    </div>
                    @if($practical->kelas)
                        <div class="text-muted">
                            <i class="fas fa-door-open me-1 opacity-75"></i>
                            {{ $practical->kelas->name }}
                        </div>
                    @endif
                </div>

                {{-- Score / Status --}}
                @if($isGraded)
                    <div class="p-3 rounded-3"
                         style="background:{{ $scoreBg }};border:1px solid {{ $scoreClr }}33;">
                        <div class="d-flex justify-content-between align-items-center mb-2"
                             style="font-size:.78rem;">
                            <span class="fw-semibold" style="color:{{ $scoreClr }};">
                                <i class="fas fa-star me-1"></i>Nilai Anda
                            </span>
                            <span class="fw-black" style="font-size:1.1rem;color:{{ $scoreClr }};">
                                {{ number_format($scoreVal, 0) }}
                                <span class="fw-normal text-muted" style="font-size:.78rem;">/{{ $maxScore }}</span>
                            </span>
                        </div>
                        <div class="progress progress-xs mb-2">
                            <div class="progress-bar"
                                 style="width:{{ $pct }}%;background:{{ $scoreClr }};"></div>
                        </div>
                        <div class="d-flex justify-content-between" style="font-size:.72rem;">
                            <span class="text-muted">{{ number_format($pct, 0) }}%</span>
                            <span class="badge fw-semibold"
                                  style="background:{{ $scoreClr }};color:#fff;border-radius:20px;">
                                Grade {{ $grade }}
                            </span>
                        </div>
                        @if($scoreRecord->feedback && !str_starts_with(trim($scoreRecord->feedback), '{'))
                            <div class="mt-2 pt-2 border-top small text-muted"
                                 style="border-color:{{ $scoreClr }}33 !important;font-size:.73rem;">
                                <i class="fas fa-comment-dots me-1"></i>
                                {{ Str::limit($scoreRecord->feedback, 80) }}
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-2 rounded-3 text-center"
                         style="background:rgba(100,116,139,.07);border:1px solid #e8edf2;font-size:.78rem;color:#94a3b8;">
                        <i class="fas fa-hourglass-half me-1"></i>Belum dinilai
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="card-footer bg-transparent border-top px-4 py-3">
                <a href="{{ route('siswa.praktikum.show', $practical->id) }}"
                   class="btn w-100 fw-semibold"
                   style="border-radius:10px;background:{{ $sClr }};color:#fff;border:none;">
                    <i class="fas fa-eye me-2"></i>Lihat Detail
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-body text-center py-5">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                     style="width:72px;height:72px;background:rgba(217,119,6,.08);">
                    <i class="fas fa-flask fa-2x" style="color:#d97706;opacity:.6;"></i>
                </div>
                <h5 class="fw-semibold text-muted mb-2">
                    {{ ($status || $search) ? 'Tidak ada praktikum yang cocok' : 'Belum ada praktikum' }}
                </h5>
                <p class="text-muted small mb-3">
                    {{ ($status || $search)
                        ? 'Coba ubah filter atau kata kunci pencarian.'
                        : 'Praktikum dari guru akan muncul di sini.' }}
                </p>
                @if($status || $search)
                    <a href="{{ route('siswa.praktikum.index') }}"
                       class="btn btn-sm fw-semibold"
                       style="border-radius:8px;background:rgba(217,119,6,.1);color:#d97706;border:1px solid rgba(217,119,6,.25);">
                        <i class="fas fa-times me-1"></i>Hapus Filter
                    </a>
                @endif
            </div>
        </div>
    </div>
    @endforelse
</div>

{{-- ══ PAGINATION ═══════════════════════════════════════════ --}}
@if($practicals->hasPages())
<div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2 mt-4">
    <small class="text-muted">
        Menampilkan {{ $practicals->firstItem() }}–{{ $practicals->lastItem() }}
        dari {{ number_format($practicals->total()) }} praktikum
    </small>
    {{ $practicals->appends(request()->query())->links() }}
</div>
@endif

@endsection
