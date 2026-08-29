@extends('layouts.siswa')

@section('title', 'Daftar Tugas')
@section('page-title', 'Daftar Tugas')
@section('page-subtitle', 'Tugas yang diberikan guru untuk Anda.')

@push('css')
<style>
.asgn-stat {
    border: none; border-radius: 14px; overflow: hidden;
    transition: transform .18s, box-shadow .18s;
    cursor: pointer; text-decoration: none !important;
}
.asgn-stat:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(0,0,0,.09) !important; }
.asgn-stat-icon {
    width: 44px; height: 44px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; color: #fff; flex-shrink: 0;
}
.asgn-card {
    border: 1px solid #e8edf2 !important;
    border-radius: 14px !important;
    transition: transform .18s, box-shadow .18s;
    overflow: hidden;
}
.asgn-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(0,0,0,.09) !important;
}
.filter-bar {
    background: #fff; border: 1px solid #e8edf2;
    border-radius: 14px; padding: .875rem 1.25rem;
    margin-bottom: 1.25rem; box-shadow: 0 2px 8px rgba(0,0,0,.04);
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

{{-- ══ STATS ════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    @php
    $statItems = [
        ['from'=>'#7c3aed','to'=>'#6d28d9','icon'=>'fa-tasks',        'val'=>$totalAll,  'label'=>'Total Tugas',   'filter'=>''],
        ['from'=>'#16a34a','to'=>'#15803d','icon'=>'fa-check-circle', 'val'=>$submitted, 'label'=>'Terkumpul',     'filter'=>'submitted'],
        ['from'=>'#0891b2','to'=>'#0e7490','icon'=>'fa-star',         'val'=>$graded,    'label'=>'Sudah Dinilai', 'filter'=>'graded'],
        ['from'=>'#dc2626','to'=>'#b91c1c','icon'=>'fa-clock',        'val'=>$overdue,   'label'=>'Terlambat',     'filter'=>'overdue'],
    ];
    @endphp
    @foreach($statItems as $s)
    <div class="col-6 col-md-3">
        <a href="{{ route('siswa.assignments.index', array_merge(request()->query(), ['status'=>$s['filter']])) }}"
           class="card asgn-stat shadow-sm h-100 d-block"
           style="{{ $status === $s['filter'] ? 'box-shadow:0 0 0 2.5px '.$s['from'].' !important;' : '' }}">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="asgn-stat-icon"
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
        </a>
    </div>
    @endforeach
</div>

{{-- ══ FILTER ═══════════════════════════════════════════════ --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('siswa.assignments.index') }}"
          class="row g-2 align-items-center">
        {{-- Status tabs --}}
        <div class="col-12 col-md-8">
            <div class="d-flex gap-1 flex-wrap">
                @foreach([
                    ''          => ['Semua',         '#7c3aed'],
                    'pending'   => ['Belum Kumpul',   '#0891b2'],
                    'submitted' => ['Sudah Kumpul',   '#16a34a'],
                    'graded'    => ['Sudah Dinilai',  '#d97706'],
                    'overdue'   => ['Terlambat',      '#dc2626'],
                ] as $val => [$label, $clr])
                @php $isActive = $status === $val; @endphp
                <a href="{{ route('siswa.assignments.index', array_merge(request()->query(), ['status'=>$val, 'page'=>1])) }}"
                   class="btn btn-sm fw-semibold"
                   style="border-radius:20px;
                          background:{{ $isActive ? $clr : '#f1f5f9' }};
                          color:{{ $isActive ? '#fff' : '#64748b' }};
                          border:none;
                          font-size:.78rem;">
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
                       placeholder="Cari tugas…" value="{{ $search }}"
                       style="box-shadow:none;">
            </div>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100" style="border-radius:9px;">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>
</div>

{{-- ══ DAFTAR TUGAS ══════════════════════════════════════════ --}}
<div class="row g-3">
    @forelse($assignments as $assignment)
    @php
        $submission  = $assignment->submissions->first();
        $dueDate     = $assignment->due_date;
        $isPast      = $dueDate && $dueDate->isPast();
        $urgentHours = $dueDate && !$isPast && $dueDate->diffInHours(now()) <= 24;
        $isSubmitted = !is_null($submission);
        $isGraded    = $isSubmitted && $submission->score !== null;
        $isOverdue   = $isPast && !$isSubmitted && !$assignment->allow_late;
        $allowLate   = $isPast && !$isSubmitted && $assignment->allow_late;

        // Status config
        [$sClr, $sBg, $sLabel, $sIcon] = match(true) {
            $isGraded    => ['#16a34a', 'rgba(22,163,74,.09)',   'Dinilai',        'fa-star'],
            $isSubmitted => ['#0891b2', 'rgba(8,145,178,.09)',   'Terkumpul',      'fa-check'],
            $isOverdue   => ['#dc2626', 'rgba(220,38,38,.09)',   'Terlambat',      'fa-times-circle'],
            $allowLate   => ['#d97706', 'rgba(217,119,6,.09)',   'Boleh Terlambat','fa-clock'],
            $urgentHours => ['#d97706', 'rgba(217,119,6,.09)',   'Segera!',        'fa-exclamation-triangle'],
            default      => ['#64748b', 'rgba(100,116,139,.09)', 'Belum Kumpul',   'fa-inbox'],
        };

        $maxScore  = $assignment->max_score ?? 100;
        $pct       = $isGraded ? min(100, ($submission->score / $maxScore) * 100) : 0;
        $scoreClr  = $pct >= 80 ? '#16a34a' : ($pct >= 60 ? '#d97706' : '#dc2626');
    @endphp

    <div class="col-12 col-lg-6">
        <div class="card asgn-card shadow-sm h-100">

            {{-- Left accent bar --}}
            <div style="position:absolute;top:0;left:0;bottom:0;width:4px;background:{{ $sClr }};border-radius:14px 0 0 14px;"></div>

            <div class="card-body p-4 ps-5">

                {{-- Title + badge --}}
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <h6 class="fw-bold mb-0 lh-sm flex-grow-1" style="font-size:.92rem;">
                        {{ $assignment->title }}
                    </h6>
                    <span class="badge flex-shrink-0 fw-semibold"
                          style="background:{{ $sBg }};color:{{ $sClr }};border-radius:20px;font-size:.7rem;padding:.22rem .7rem;white-space:nowrap;">
                        <i class="fas {{ $sIcon }} me-1" style="font-size:.6rem;"></i>{{ $sLabel }}
                    </span>
                </div>

                {{-- Subject badge --}}
                @if($assignment->subject)
                    <div class="mb-2">
                        <span class="badge fw-semibold"
                              style="background:rgba(124,58,237,.1);color:#7c3aed;border-radius:20px;font-size:.7rem;padding:.18rem .6rem;">
                            <i class="fas fa-book me-1"></i>{{ $assignment->subject->name }}
                        </span>
                    </div>
                @endif

                {{-- Description preview --}}
                @if($assignment->description)
                    <p class="text-muted mb-3 lh-sm"
                       style="font-size:.8rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                        {{ strip_tags($assignment->description) }}
                    </p>
                @endif

                {{-- Deadline --}}
                <div class="d-flex align-items-center justify-content-between"
                     style="font-size:.78rem;">
                    <div class="d-flex align-items-center gap-1
                                {{ $isOverdue ? '' : 'text-muted' }}"
                         style="{{ $isOverdue ? 'color:#dc2626;font-weight:600;' : '' }}">
                        <i class="fas fa-clock"></i>
                        @if($dueDate)
                            <span>{{ $dueDate->format('d M Y, H:i') }}</span>
                            @if(!$isPast)
                                <span style="color:#0891b2;">({{ $dueDate->diffForHumans() }})</span>
                            @else
                                <span style="color:#dc2626;">({{ $dueDate->diffForHumans() }})</span>
                            @endif
                        @else
                            <span>Tanpa deadline</span>
                        @endif
                    </div>
                    <span class="text-muted">Maks. {{ $maxScore }} poin</span>
                </div>

                {{-- Score bar --}}
                @if($isGraded)
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-1"
                             style="font-size:.78rem;">
                            <span class="text-muted">Nilai Anda</span>
                            <span class="fw-bold" style="color:{{ $scoreClr }};">
                                {{ $submission->score }}/{{ $maxScore }}
                                <span class="text-muted fw-normal">({{ number_format($pct, 0) }}%)</span>
                            </span>
                        </div>
                        <div class="progress progress-xs">
                            <div class="progress-bar"
                                 style="width:{{ $pct }}%;background:{{ $scoreClr }};"></div>
                        </div>
                        @if($submission->feedback)
                            <div class="mt-2 p-2 rounded-2 small text-muted"
                                 style="background:#f8fafc;font-size:.76rem;">
                                <i class="fas fa-comment-dots me-1"></i>
                                {{ Str::limit($submission->feedback, 90) }}
                            </div>
                        @endif
                    </div>
                @elseif($isSubmitted)
                    <div class="mt-3 pt-3 border-top d-flex align-items-center gap-1"
                         style="font-size:.76rem;color:#0891b2;">
                        <i class="fas fa-check-circle"></i>
                        <span>Dikumpulkan {{ $submission->submitted_at?->format('d M Y H:i') ?? '' }}</span>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="card-footer bg-transparent border-top px-4 py-3 ps-5">
                <div class="d-flex gap-2">
                    <a href="{{ route('siswa.assignments.show', $assignment->id) }}"
                       class="btn btn-sm flex-fill"
                       style="border-radius:8px;border:1.5px solid {{ $sClr }};color:{{ $sClr }};background:transparent;">
                        <i class="fas fa-eye me-1"></i>Detail
                    </a>
                    @if(!$isSubmitted && ($dueDate?->isFuture() || $assignment->allow_late))
                        <a href="{{ route('siswa.assignments.show', $assignment->id) }}"
                           class="btn btn-sm flex-fill"
                           style="border-radius:8px;background:{{ $allowLate ? '#d97706' : '#16a34a' }};color:#fff;border:none;">
                            <i class="fas fa-paper-plane me-1"></i>
                            {{ $allowLate ? 'Kumpul (Terlambat)' : 'Kumpulkan' }}
                        </a>
                    @elseif($isSubmitted && !$isGraded)
                        <span class="btn btn-sm flex-fill disabled"
                              style="border-radius:8px;background:#f1f5f9;color:#94a3b8;border:none;">
                            <i class="fas fa-hourglass-half me-1"></i>Menunggu Nilai
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-body text-center py-5">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                     style="width:72px;height:72px;background:rgba(124,58,237,.08);">
                    <i class="fas fa-tasks fa-2x" style="color:#7c3aed;opacity:.6;"></i>
                </div>
                <h5 class="fw-semibold text-muted mb-2">
                    {{ ($status || $search) ? 'Tidak ada tugas yang cocok' : 'Belum ada tugas' }}
                </h5>
                <p class="text-muted small mb-3">
                    {{ ($status || $search)
                        ? 'Coba ubah filter atau kata kunci pencarian.'
                        : 'Tugas dari guru akan muncul di sini.' }}
                </p>
                @if($status || $search)
                    <a href="{{ route('siswa.assignments.index') }}"
                       class="btn btn-outline-primary btn-sm" style="border-radius:8px;">
                        <i class="fas fa-times me-1"></i>Hapus Filter
                    </a>
                @endif
            </div>
        </div>
    </div>
    @endforelse
</div>

{{-- ══ PAGINATION ═══════════════════════════════════════════ --}}
@if($assignments->hasPages())
<div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2 mt-4">
    <small class="text-muted">
        Menampilkan {{ $assignments->firstItem() }}–{{ $assignments->lastItem() }}
        dari {{ number_format($assignments->total()) }} tugas
    </small>
    {{ $assignments->appends(request()->query())->links() }}
</div>
@endif

@endsection
