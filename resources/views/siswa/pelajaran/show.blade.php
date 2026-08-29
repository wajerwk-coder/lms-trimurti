@extends('layouts.siswa')

@section('title', $subject->name)
@section('page-title', $subject->name)
@section('page-subtitle', ucfirst($subject->type ?? 'Mata Pelajaran') . ($subject->code ? ' · ' . $subject->code : ''))

@section('page-actions')
    <a href="{{ route('siswa.pelajaran.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Semua Pelajaran
    </a>
@endsection

@push('css')
<style>
/* Type colours via PHP — no BS variable dependency */
.pj-card {
    border: 1px solid #e8edf2 !important;
    border-radius: 12px !important;
    margin-bottom: .75rem;
    transition: box-shadow .15s;
}
.pj-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.07) !important; }

/* Custom nav pills */
.pj-nav .nav-link {
    color: #64748b;
    font-size: .875rem;
    border-radius: 8px;
    padding: .45rem 1.1rem;
    border: 1.5px solid transparent;
    font-weight: 600;
    transition: all .15s;
}
.pj-nav .nav-link:hover { background: #f1f5f9; color: #334155; }
.pj-nav .nav-link.active {
    background: #7c3aed;
    color: #fff;
    border-color: #7c3aed;
}
.badge-pill {
    border-radius: 20px;
    font-size: .65rem;
    font-weight: 600;
    padding: .18rem .55rem;
}
.min-w-0 { min-width: 0; }
.progress-xs { height: 5px; border-radius: 3px; }
.info-row {
    display: flex; align-items: center; gap: .6rem;
    padding: .45rem 0; border-bottom: 1px solid #f1f5f9;
    font-size: .84rem;
}
.info-row:last-child { border-bottom: none; }
.info-icon {
    width: 26px; height: 26px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: .65rem;
}
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:12px;">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@php
    $typeMap = [
        'teori'     => ['color'=>'#3b82f6','bg'=>'rgba(59,130,246,.09)',  'icon'=>'fa-chalkboard-teacher','label'=>'Teori'],
        'praktikum' => ['color'=>'#d97706','bg'=>'rgba(217,119,6,.09)',   'icon'=>'fa-flask',             'label'=>'Praktikum'],
        'campuran'  => ['color'=>'#16a34a','bg'=>'rgba(22,163,74,.09)',   'icon'=>'fa-layer-group',       'label'=>'Campuran'],
    ];
    $tm = $typeMap[$subject->type ?? ''] ?? ['color'=>'#7c3aed','bg'=>'rgba(124,58,237,.09)','icon'=>'fa-book','label'=>'Umum'];
@endphp

<div class="row g-4">

    {{-- ═══ KIRI ═══════════════════════════════════════════════ --}}
    <div class="col-lg-8">

        {{-- Hero --}}
        <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="border-radius:14px;">
            <div style="height:5px;background:{{ $tm['color'] }};"></div>
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:58px;height:58px;background:{{ $tm['bg'] }};">
                        <i class="fas {{ $tm['icon'] }} fa-lg" style="color:{{ $tm['color'] }};"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <h4 class="fw-bold mb-2">{{ $subject->name }}</h4>
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                            <span class="badge-pill"
                                  style="background:{{ $tm['bg'] }};color:{{ $tm['color'] }};">
                                {{ $tm['label'] }}
                            </span>
                            @if($subject->code)
                                <span class="badge bg-light text-muted border"
                                      style="border-radius:20px;font-size:.68rem;">
                                    {{ $subject->code }}
                                </span>
                            @endif
                            @if($subject->sks)
                                <span class="badge bg-light text-muted border"
                                      style="border-radius:20px;font-size:.68rem;">
                                    {{ $subject->sks }} SKS
                                </span>
                            @endif
                        </div>
                        @if($subject->description)
                            <p class="text-muted small mb-0 lh-lg">{{ $subject->description }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats bar --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
            <div class="card-body py-3">
                <div class="row g-0 text-center">
                    @foreach([
                        ['#3b82f6','fa-file-alt',  $materials->count(),   'Materi'],
                        ['#16a34a','fa-tasks',     $assignments->count(), 'Tugas'],
                        ['#d97706','fa-flask',     $practicals->count(),  'Praktikum'],
                    ] as [$clr, $ic, $cnt, $lbl])
                    <div class="col {{ !$loop->last ? 'border-end' : '' }} py-1">
                        <div class="fw-bold mb-0" style="font-size:1.4rem;color:{{ $clr }};">{{ $cnt }}</div>
                        <div class="text-muted small">
                            <i class="fas {{ $ic }} me-1" style="color:{{ $clr }};opacity:.6;font-size:.7rem;"></i>{{ $lbl }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <ul class="nav pj-nav gap-1 mb-3">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-materi">
                    <i class="fas fa-file-alt me-1"></i>Materi
                    @if($materials->count())
                        <span class="ms-1 badge rounded-pill"
                              style="background:rgba(59,130,246,.15);color:#3b82f6;font-size:.65rem;">
                            {{ $materials->count() }}
                        </span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-tugas">
                    <i class="fas fa-tasks me-1"></i>Tugas
                    @if($assignments->count())
                        <span class="ms-1 badge rounded-pill"
                              style="background:rgba(22,163,74,.15);color:#16a34a;font-size:.65rem;">
                            {{ $assignments->count() }}
                        </span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-praktik">
                    <i class="fas fa-flask me-1"></i>Praktikum
                    @if($practicals->count())
                        <span class="ms-1 badge rounded-pill"
                              style="background:rgba(217,119,6,.15);color:#d97706;font-size:.65rem;">
                            {{ $practicals->count() }}
                        </span>
                    @endif
                </button>
            </li>
        </ul>

        <div class="tab-content">

            {{-- Tab Materi --}}
            <div class="tab-pane fade show active" id="tab-materi">
                @forelse($materials as $material)
                @php
                    $ext  = $material->file_url ? strtoupper(pathinfo($material->file_url, PATHINFO_EXTENSION)) : null;
                    $extClr = match($ext) {
                        'PDF'         => '#dc2626',
                        'DOC','DOCX'  => '#3b82f6',
                        'PPT','PPTX'  => '#ea580c',
                        'XLS','XLSX'  => '#16a34a',
                        default       => '#64748b',
                    };
                @endphp
                <div class="pj-card card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-start gap-3 p-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:42px;height:42px;background:rgba(59,130,246,.09);">
                            <i class="fas fa-file-alt" style="color:#3b82f6;"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate" style="font-size:.88rem;">
                                {{ $material->title }}
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                <span class="text-muted" style="font-size:.75rem;">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ optional($material->published_at)->format('d M Y') ?? '—' }}
                                </span>
                                @if($ext)
                                    <span class="badge-pill"
                                          style="background:rgba({{ implode(',', sscanf(ltrim($extClr,'#'),'%02x%02x%02x')) ?? '100,116,139' }},.12);color:{{ $extClr }};">
                                        {{ $ext }}
                                    </span>
                                @endif
                                @if($material->video_url)
                                    <span class="badge-pill"
                                          style="background:rgba(220,38,38,.1);color:#dc2626;">
                                        <i class="fas fa-video me-1"></i>Video
                                    </span>
                                @endif
                            </div>
                            @if($material->content)
                                <p class="text-muted mt-1 mb-0"
                                   style="font-size:.78rem;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                    {{ strip_tags($material->content) }}
                                </p>
                            @endif
                        </div>
                        <div class="d-flex flex-column gap-1 flex-shrink-0">
                            <a href="{{ route('siswa.materials.show', $material->id) }}"
                               class="btn btn-sm btn-outline-primary" style="border-radius:7px;font-size:.75rem;">
                                <i class="fas fa-eye me-1"></i>Lihat
                            </a>
                            @if($material->file_url)
                                <a href="{{ route('siswa.materials.download', $material->id) }}"
                                   class="btn btn-sm btn-success" style="border-radius:7px;font-size:.75rem;">
                                    <i class="fas fa-download me-1"></i>Unduh
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-file-alt fa-2x opacity-25 mb-2 d-block"></i>
                    <p class="mb-0">Belum ada materi tersedia.</p>
                </div>
                @endforelse
            </div>

            {{-- Tab Tugas --}}
            <div class="tab-pane fade" id="tab-tugas">
                @forelse($assignments as $assignment)
                @php
                    $sub     = $assignment->submissions->first();
                    $dueDate = $assignment->due_date;
                    $isPast  = $dueDate && $dueDate->isPast();
                    $urgent  = $dueDate && !$isPast && $dueDate->diffInHours(now()) <= 24;

                    if ($sub) {
                        $sClr   = $sub->score !== null ? '#16a34a' : '#0891b2';
                        $sBg    = $sub->score !== null ? 'rgba(22,163,74,.09)' : 'rgba(8,145,178,.09)';
                        $sLabel = $sub->score !== null ? 'Dinilai: ' . $sub->score : 'Menunggu Nilai';
                        $sIcon  = $sub->score !== null ? 'fa-star' : 'fa-clock';
                    } elseif ($isPast && !$assignment->allow_late) {
                        $sClr = '#dc2626'; $sBg = 'rgba(220,38,38,.09)';
                        $sLabel = 'Terlambat'; $sIcon = 'fa-times-circle';
                    } elseif ($urgent) {
                        $sClr = '#d97706'; $sBg = 'rgba(217,119,6,.09)';
                        $sLabel = 'Segera Kumpul'; $sIcon = 'fa-exclamation-triangle';
                    } else {
                        $sClr = '#64748b'; $sBg = 'rgba(100,116,139,.08)';
                        $sLabel = 'Belum Dikumpulkan'; $sIcon = 'fa-inbox';
                    }
                @endphp
                <div class="pj-card card border-0 shadow-sm"
                     style="border-left: 4px solid {{ $sClr }} !important;">
                    <div class="card-body d-flex align-items-start gap-3 p-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:42px;height:42px;background:{{ $sBg }};">
                            <i class="fas {{ $sIcon }}" style="color:{{ $sClr }};"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate" style="font-size:.88rem;">
                                {{ $assignment->title }}
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                <span class="text-muted {{ $isPast ? '' : '' }}"
                                      style="font-size:.75rem;{{ $isPast ? 'color:#dc2626 !important;' : '' }}">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $dueDate ? $dueDate->format('d M Y, H:i') : 'Tidak ada deadline' }}
                                    @if($dueDate && !$isPast)
                                        <span style="color:#0891b2;">({{ $dueDate->diffForHumans() }})</span>
                                    @endif
                                </span>
                                <span class="badge-pill" style="background:{{ $sBg }};color:{{ $sClr }};">
                                    {{ $sLabel }}
                                </span>
                                @if($assignment->allow_late && $isPast && !$sub)
                                    <span class="badge-pill"
                                          style="background:rgba(8,145,178,.1);color:#0891b2;">
                                        Terlambat Boleh
                                    </span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('siswa.assignments.show', $assignment->id) }}"
                           class="btn btn-sm flex-shrink-0"
                           style="border-radius:7px;font-size:.75rem;background:{{ $sub ? 'rgba(22,163,74,.1)' : $tm['color'] }};color:{{ $sub ? '#16a34a' : '#fff' }};border:{{ $sub ? '1px solid rgba(22,163,74,.3)' : 'none' }};">
                            @if(!$sub && !$isPast)
                                <i class="fas fa-paper-plane me-1"></i>Kumpulkan
                            @else
                                <i class="fas fa-eye me-1"></i>Detail
                            @endif
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-tasks fa-2x opacity-25 mb-2 d-block"></i>
                    <p class="mb-0">Belum ada tugas tersedia.</p>
                </div>
                @endforelse
            </div>

            {{-- Tab Praktikum --}}
            <div class="tab-pane fade" id="tab-praktik">
                @forelse($practicals as $practical)
                @php
                    $score    = $practical->scores->first();
                    $dueDate  = $practical->due_date;
                    $isPast   = $dueDate && $dueDate->isPast();
                    $hasScore = $score && $score->score !== null;
                    $scVal    = $hasScore ? (float)$score->score : 0;
                    $pct      = $hasScore ? min(100, $scVal) : 0;
                    $sClr     = $hasScore
                        ? ($scVal >= 80 ? '#16a34a' : ($scVal >= 60 ? '#d97706' : '#dc2626'))
                        : '#94a3b8';
                @endphp
                <div class="pj-card card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-start gap-3 p-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:42px;height:42px;background:rgba(217,119,6,.09);">
                            <i class="fas fa-flask" style="color:#d97706;"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate" style="font-size:.88rem;">
                                {{ $practical->title }}
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                <span class="text-muted" style="font-size:.75rem;{{ $isPast ? 'color:#dc2626 !important;' : '' }}">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $dueDate ? $dueDate->format('d M Y') : '—' }}
                                </span>
                                @if($hasScore)
                                    <span class="badge-pill"
                                          style="background:{{ $sClr === '#16a34a' ? 'rgba(22,163,74,.1)' : ($sClr === '#d97706' ? 'rgba(217,119,6,.1)' : 'rgba(220,38,38,.1)') }};color:{{ $sClr }};">
                                        <i class="fas fa-star me-1" style="font-size:.6rem;"></i>
                                        Nilai: {{ number_format($scVal, 0) }}
                                    </span>
                                @else
                                    <span class="badge-pill"
                                          style="background:rgba(148,163,184,.1);color:#94a3b8;">
                                        Belum Dinilai
                                    </span>
                                @endif
                            </div>
                            @if($hasScore)
                            <div class="mt-2 d-flex align-items-center gap-2">
                                <div class="progress progress-xs flex-grow-1">
                                    <div class="progress-bar"
                                         style="width:{{ $pct }}%;background:{{ $sClr }};"></div>
                                </div>
                                <small class="flex-shrink-0 text-muted" style="font-size:.68rem;">
                                    {{ number_format($pct, 0) }}%
                                </small>
                            </div>
                            @endif
                        </div>
                        <a href="{{ route('siswa.praktikum.show', $practical->id) }}"
                           class="btn btn-sm flex-shrink-0"
                           style="border-radius:7px;font-size:.75rem;background:rgba(217,119,6,.1);color:#d97706;border:1px solid rgba(217,119,6,.25);">
                            <i class="fas fa-eye me-1"></i>Detail
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-flask fa-2x opacity-25 mb-2 d-block"></i>
                    <p class="mb-0">Belum ada praktikum tersedia.</p>
                </div>
                @endforelse
            </div>

        </div>{{-- /tab-content --}}
    </div>

    {{-- ═══ KANAN ════════════════════════════════════════════════ --}}
    <div class="col-lg-4">

        {{-- Info Mata Pelajaran --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
            <div class="card-header bg-white border-bottom py-3 px-4"
                 style="border-radius:14px 14px 0 0;">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-info-circle me-2" style="color:#7c3aed;"></i>Info Pelajaran
                </h6>
            </div>
            <div class="card-body px-4 py-3">
                @foreach([
                    ['fa-book',        'rgba(124,58,237,.09)', '#7c3aed', 'Nama',  $subject->name],
                    ['fa-tag',         'rgba(59,130,246,.09)', '#3b82f6', 'Kode',  $subject->code ?? '—'],
                    ['fa-layer-group', 'rgba(22,163,74,.09)', '#16a34a',  'Tipe',  ucfirst($subject->type ?? 'Umum')],
                    ['fa-clock',       'rgba(217,119,6,.09)', '#d97706',  'SKS',   $subject->sks ?? '—'],
                ] as [$ic, $ibg, $iclr, $label, $val])
                <div class="info-row">
                    <div class="info-icon" style="background:{{ $ibg }};">
                        <i class="fas {{ $ic }}" style="color:{{ $iclr }};"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:.67rem;letter-spacing:.04em;text-transform:uppercase;">{{ $label }}</div>
                        <div class="fw-semibold" style="font-size:.84rem;">{{ $val }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Progress --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
            <div class="card-header bg-white border-bottom py-3 px-4"
                 style="border-radius:14px 14px 0 0;">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-chart-pie me-2" style="color:#16a34a;"></i>Progress Anda
                </h6>
            </div>
            <div class="card-body px-4 py-3">
                @php
                    $submittedCount = $assignments->filter(fn($a) => $a->submissions->isNotEmpty())->count();
                    $assignTotal    = $assignments->count();
                    $gradedCount    = $practicals->filter(fn($p) => $p->scores->isNotEmpty() && $p->scores->first()?->score !== null)->count();
                    $practTotal     = $practicals->count();
                    $assignPct      = $assignTotal > 0 ? round($submittedCount / $assignTotal * 100) : 0;
                    $practPct       = $practTotal  > 0 ? round($gradedCount / $practTotal * 100) : 0;
                @endphp

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1" style="font-size:.8rem;">
                        <span class="text-muted">Tugas Terkumpul</span>
                        <span class="fw-semibold" style="color:#16a34a;">{{ $submittedCount }}/{{ $assignTotal }}</span>
                    </div>
                    <div class="progress progress-xs">
                        <div class="progress-bar" style="width:{{ $assignPct }}%;background:#16a34a;"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1" style="font-size:.8rem;">
                        <span class="text-muted">Praktikum Dinilai</span>
                        <span class="fw-semibold" style="color:#d97706;">{{ $gradedCount }}/{{ $practTotal }}</span>
                    </div>
                    <div class="progress progress-xs">
                        <div class="progress-bar" style="width:{{ $practPct }}%;background:#d97706;"></div>
                    </div>
                </div>

                @php
                    $scoreVals = $practicals->filter(fn($p) => $p->scores->isNotEmpty() && $p->scores->first()?->score !== null)->map(fn($p) => (float)$p->scores->first()->score);
                    $avgScore  = $scoreVals->count() > 0 ? round($scoreVals->avg(), 1) : null;
                    $avgClr    = $avgScore !== null ? ($avgScore >= 80 ? '#16a34a' : ($avgScore >= 60 ? '#d97706' : '#dc2626')) : '#94a3b8';
                @endphp
                @if($avgScore !== null)
                <div class="mt-3 pt-3 border-top text-center">
                    <div class="text-muted mb-1" style="font-size:.75rem;">Rata-rata Nilai Praktikum</div>
                    <div class="fw-black" style="font-size:2rem;color:{{ $avgClr }};line-height:1;">{{ $avgScore }}</div>
                    <div class="text-muted" style="font-size:.72rem;">dari 100</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Tugas urgent --}}
        @php
            $urgentAssignments = $assignments->filter(function($a) {
                return !$a->submissions->isNotEmpty()
                    && $a->due_date
                    && $a->due_date->isFuture()
                    && $a->due_date->diffInDays(now()) <= 3;
            });
        @endphp
        @if($urgentAssignments->isNotEmpty())
        <div class="card border-0 shadow-sm" style="border-radius:14px;border-left:4px solid #d97706 !important;">
            <div class="card-header bg-white border-bottom py-2 px-4" style="border-radius:14px 14px 0 0;">
                <h6 class="mb-0 fw-semibold" style="font-size:.82rem;color:#d97706;">
                    <i class="fas fa-exclamation-triangle me-2"></i>Tugas Segera Dikumpulkan
                </h6>
            </div>
            <div class="card-body py-2 px-4">
                @foreach($urgentAssignments->take(3) as $ua)
                <div class="d-flex align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <i class="fas fa-circle flex-shrink-0" style="color:#d97706;font-size:.4rem;"></i>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-truncate" style="font-size:.82rem;">{{ $ua->title }}</div>
                        <div class="text-muted" style="font-size:.7rem;">{{ $ua->due_date->diffForHumans() }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

@endsection
