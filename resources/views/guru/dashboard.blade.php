@extends('layouts.guru')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Guru')
@section('page-subtitle', 'Selamat datang, ' . auth()->user()->name)

@push('css')
<style>
/* ── Hero ─────────────────────────────────────────────────────────── */
.hero-guru {
    background: linear-gradient(135deg, #0f766e 0%, #0891b2 55%, #1d4ed8 100%);
    border-radius: 20px;
    overflow: hidden;
    position: relative;
}
.hero-guru::before {
    content: '';
    position: absolute; top: -80px; right: -60px;
    width: 260px; height: 260px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
    pointer-events: none;
}
.hero-guru::after {
    content: '';
    position: absolute; bottom: -50px; right: 160px;
    width: 180px; height: 180px;
    background: rgba(255,255,255,.04);
    border-radius: 50%;
    pointer-events: none;
}
.hero-guru > * { position: relative; z-index: 1; }

.hero-avatar {
    width: 58px; height: 58px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255,255,255,.35);
    flex-shrink: 0;
}
.hero-avatar-fallback {
    width: 58px; height: 58px;
    border-radius: 50%;
    background: rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; font-weight: 700; color: #fff;
    flex-shrink: 0;
    border: 3px solid rgba(255,255,255,.3);
}
.hero-btn {
    background: rgba(255,255,255,.18) !important;
    color: #fff !important;
    border: 1.5px solid rgba(255,255,255,.35) !important;
    font-weight: 600 !important;
    font-size: .8rem !important;
    padding: .35rem .85rem !important;
    border-radius: 9px !important;
    transition: background .15s, transform .12s !important;
    backdrop-filter: blur(4px);
    text-decoration: none;
    display: inline-flex; align-items: center; gap: .35rem;
}
.hero-btn:hover {
    background: rgba(255,255,255,.28) !important;
    transform: translateY(-1px);
    color: #fff !important;
}
.hero-mini {
    background: rgba(255,255,255,.12);
    border-radius: 12px;
    text-align: center;
    padding: .7rem .5rem;
    backdrop-filter: blur(4px);
}

/* ── Stat cards ───────────────────────────────────────────────────── */
.stat-card {
    border: none;
    border-radius: 16px;
    transition: transform .2s, box-shadow .2s;
    overflow: hidden;
}
.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 36px rgba(0,0,0,.12) !important;
}
.stat-icon {
    width: 48px; height: 48px;
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; color: #fff;
    flex-shrink: 0;
}
.stat-val {
    font-size: 1.85rem;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -.5px;
}
.stat-accent { height: 3px; }

/* ── Section card header ──────────────────────────────────────────── */
.sec-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: .9rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
}
.sec-header h6 { margin: 0; font-weight: 700; font-size: .9rem; }

/* ── Quick action list ────────────────────────────────────────────── */
.qa-item {
    display: flex; align-items: center; gap: .75rem;
    padding: .6rem 1rem;
    border-radius: 10px;
    text-decoration: none !important;
    color: #334155;
    transition: background .13s, transform .13s;
    font-size: .83rem;
}
.qa-item:hover {
    background: #f0f9ff;
    color: #0f766e;
    transform: translateX(3px);
}
.qa-icon {
    width: 36px; height: 36px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: .8rem;
}

/* ── Submission row ───────────────────────────────────────────────── */
.sub-row {
    display: flex; align-items: center; gap: .75rem;
    padding: .65rem 1.25rem;
    border-bottom: 1px solid #f8fafc;
    transition: background .1s;
}
.sub-row:hover { background: #f8fafc; }
.sub-row:last-child { border-bottom: none; }
.sub-av {
    width: 38px; height: 38px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .85rem; color: #fff;
    flex-shrink: 0;
}

/* ── Deadline item ────────────────────────────────────────────────── */
.dl-item {
    display: flex; align-items: center; gap: .75rem;
    padding: .6rem .75rem;
    border-radius: 10px;
    border-left: 4px solid;
    background: #f8fafc;
    margin-bottom: .45rem;
    transition: transform .12s;
}
.dl-item:last-child { margin-bottom: 0; }
.dl-item:hover { transform: translateX(3px); background: #f1f5f9; }

/* ── Jadwal ujian table ───────────────────────────────────────────── */
.exam-tbl th {
    font-size: .72rem; font-weight: 700; color: #94a3b8;
    letter-spacing: .05em; text-transform: uppercase;
    padding: .65rem 1rem;
    background: #f8fafc;
    border-bottom: 1px solid #e8edf2 !important;
}
.exam-tbl td { font-size: .83rem; vertical-align: middle; padding: .7rem 1rem; }
.exam-tbl tr:hover td { background: #f8fafc; }

/* ── Aktivitas list ───────────────────────────────────────────────── */
.act-row {
    display: flex; align-items: flex-start; gap: .65rem;
    padding: .6rem 1.25rem;
    border-bottom: 1px solid #f8fafc;
    font-size: .82rem;
}
.act-row:last-child { border-bottom: none; }
.act-dot {
    width: 28px; height: 28px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; margin-top: 2px;
}

/* ── Progress bar ─────────────────────────────────────────────────── */
.prog-thin { height: 5px; border-radius: 3px; }
</style>
@endpush

@section('content')
@php
    $guruProfile = auth()->user()->guruProfile;
    $heroPhoto   = auth()->user()->photo ?? auth()->user()->photo_url ?? null;
    $initHero    = strtoupper(substr(auth()->user()->name, 0, 1));
    $now         = \Carbon\Carbon::now();
    $greeting    = match(true) {
        $now->hour < 11 => 'Selamat Pagi',
        $now->hour < 15 => 'Selamat Siang',
        $now->hour < 18 => 'Selamat Sore',
        default         => 'Selamat Malam',
    };
@endphp

{{-- ══ HERO BANNER ══════════════════════════════════════════════════════ --}}
<div class="hero-guru p-4 pb-3 mb-4">
    <div class="row align-items-center g-3">

        {{-- Kiri: info guru + tombol aksi --}}
        <div class="col-md-7">
            <div class="d-flex align-items-center gap-3 mb-3">
                @if($heroPhoto)
                    <img src="{{ $heroPhoto }}" class="hero-avatar" alt=""
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="hero-avatar-fallback" style="display:none;">{{ $initHero }}</div>
                @else
                    <div class="hero-avatar-fallback">{{ $initHero }}</div>
                @endif
                <div class="text-white">
                    <div style="font-size:.75rem;opacity:.65;margin-bottom:.15rem;">
                        <i class="fas fa-calendar-day me-1"></i>
                        {{ now()->translatedFormat('l, d F Y') }}
                    </div>
                    <div class="fw-bold" style="font-size:1.25rem;line-height:1.2;">
                        {{ $greeting }}, {{ explode(' ', auth()->user()->name)[0] }}! 👋
                    </div>
                    @if($guruProfile?->mata_pelajaran)
                        <div style="font-size:.77rem;opacity:.7;margin-top:.25rem;">
                            <i class="fas fa-chalkboard-teacher me-1"></i>
                            {{ $guruProfile->mata_pelajaran }}
                        </div>
                    @endif
                </div>
            </div>

            <p class="text-white mb-3" style="font-size:.83rem;opacity:.75;max-width:400px;">
                Pantau perkembangan siswa, kelola materi, dan catat kehadiran dengan mudah.
            </p>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('guru.absensi.create') }}" class="hero-btn">
                    <i class="fas fa-clipboard-check"></i>Input Absensi
                </a>
                <a href="{{ route('guru.materials.create') }}" class="hero-btn">
                    <i class="fas fa-book-open"></i>Upload Materi
                </a>
                <a href="{{ route('guru.assignments.create') }}" class="hero-btn">
                    <i class="fas fa-tasks"></i>Buat Tugas
                </a>
                <a href="{{ route('guru.penilaian.nilai-kriteria') }}" class="hero-btn">
                    <i class="fas fa-star"></i>Nilai SOP
                </a>
            </div>
        </div>

        {{-- Kanan: mini stats 2×2 --}}
        <div class="col-md-5 d-none d-md-block">
            <div class="row g-2">
                @foreach([
                    ['fa-book',    $stats['total_materials']   ?? 0, 'Materi'],
                    ['fa-tasks',   $stats['total_assignments']  ?? 0, 'Tugas'],
                    ['fa-star',    $stats['pending_grading']    ?? 0, 'Perlu Dinilai'],
                    ['fa-users',   $stats['total_students']     ?? 0, 'Total Siswa'],
                ] as [$ic, $val, $lbl])
                <div class="col-6">
                    <div class="hero-mini">
                        <i class="fas {{ $ic }} text-white fa-sm mb-1 d-block" style="opacity:.7;"></i>
                        <div class="fw-bold text-white lh-1 mb-1"
                             style="font-size:1.45rem;">{{ number_format($val) }}</div>
                        <div class="text-white" style="font-size:.67rem;opacity:.65;">{{ $lbl }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ══ STAT CARDS ════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    @php
    $cards = [
        ['from'=>'#0f766e','to'=>'#0891b2','icon'=>'fa-book-open',      'val'=>$stats['total_materials']   ?? 0, 'label'=>'Total Materi',     'sub'=>'Materi diunggah',     'url'=>route('guru.materials.index')],
        ['from'=>'#059669','to'=>'#10b981','icon'=>'fa-tasks',           'val'=>$stats['total_assignments']  ?? 0, 'label'=>'Total Tugas',      'sub'=>'Tugas dibuat',        'url'=>route('guru.assignments.index')],
        ['from'=>'#7c3aed','to'=>'#a21caf','icon'=>'fa-flask',           'val'=>$stats['total_practicals']   ?? 0, 'label'=>'Praktikum',        'sub'=>'Sesi praktikum',      'url'=>route('guru.praktikum.index')],
        ['from'=>'#d97706','to'=>'#f59e0b','icon'=>'fa-star',            'val'=>$stats['pending_grading']    ?? 0, 'label'=>'Perlu Dinilai',    'sub'=>'Menunggu penilaian',  'url'=>route('guru.penilaian.index')],
        ['from'=>'#dc2626','to'=>'#ef4444','icon'=>'fa-clipboard-check', 'val'=>$stats['today_attendance']   ?? 0, 'label'=>'Absensi Hari Ini', 'sub'=>'Siswa tercatat',      'url'=>route('guru.absensi.index')],
        ['from'=>'#1d4ed8','to'=>'#3b82f6','icon'=>'fa-users',           'val'=>$stats['total_students']     ?? 0, 'label'=>'Total Siswa',      'sub'=>'Siswa aktif',         'url'=>route('guru.absensi.index')],
    ];
    @endphp

    @foreach($cards as $c)
    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ $c['url'] }}" class="text-decoration-none d-block h-100">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="stat-icon mb-3"
                         style="background:linear-gradient(135deg,{{ $c['from'] }},{{ $c['to'] }});">
                        <i class="fas {{ $c['icon'] }}"></i>
                    </div>
                    <div class="stat-val text-dark mb-1">{{ number_format($c['val']) }}</div>
                    <div class="fw-semibold text-dark mb-1" style="font-size:.79rem;">{{ $c['label'] }}</div>
                    <div class="text-muted" style="font-size:.69rem;">{{ $c['sub'] }}</div>
                </div>
                <div class="stat-accent"
                     style="background:linear-gradient(90deg,{{ $c['from'] }},{{ $c['to'] }});"></div>
            </div>
        </a>
    </div>
    @endforeach
</div>

{{-- ══ BARIS 1: AKSI CEPAT + PERLU DINILAI ══════════════════════════════ --}}
<div class="row g-4 mb-4">

    {{-- Aksi Cepat --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
            <div class="sec-header">
                <h6><i class="fas fa-bolt me-2 text-warning"></i>Aksi Cepat</h6>
            </div>
            <div class="card-body p-2">
                @foreach([
                    [route('guru.absensi.create'),           'fa-clipboard-check', '#fef2f2', '#ef4444', 'Input Absensi',      'Catat kehadiran hari ini'],
                    [route('guru.materials.create'),         'fa-book-open',       '#eff6ff', '#3b82f6', 'Upload Materi',      'Bagikan materi baru'],
                    [route('guru.assignments.create'),       'fa-tasks',           '#f0fdf4', '#22c55e', 'Buat Tugas',         'Buat tugas untuk siswa'],
                    [route('guru.praktikum.create'),         'fa-flask',           '#fefce8', '#eab308', 'Buat Praktikum',     'Tambah sesi praktikum'],
                    [route('guru.penilaian.nilai-kriteria'), 'fa-clipboard-list',  '#fdf4ff', '#a855f7', 'Penilaian SOP',      'Nilai praktikum via checklist'],
                    [route('guru.laporan.index'),            'fa-chart-bar',       '#f0fdfa', '#14b8a6', 'Lihat Laporan',      'Rekap nilai & absensi'],
                ] as [$url, $icon, $ibg, $iclr, $title, $sub])
                <a href="{{ $url }}" class="qa-item">
                    <div class="qa-icon" style="background:{{ $ibg }};">
                        <i class="fas {{ $icon }}" style="color:{{ $iclr }};"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold lh-1 mb-1" style="font-size:.84rem;">{{ $title }}</div>
                        <div class="text-muted" style="font-size:.71rem;">{{ $sub }}</div>
                    </div>
                    <i class="fas fa-chevron-right text-muted" style="font-size:.55rem;"></i>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Perlu Dinilai --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
            <div class="sec-header">
                <h6>
                    <i class="fas fa-star me-2 text-warning"></i>Perlu Dinilai
                    @if(($stats['pending_grading'] ?? 0) > 0)
                        <span class="badge bg-warning text-dark ms-1"
                              style="font-size:.68rem;">{{ $stats['pending_grading'] }}</span>
                    @endif
                </h6>
                <a href="{{ route('guru.penilaian.index') }}"
                   class="btn btn-sm btn-outline-warning"
                   style="font-size:.74rem;border-radius:8px;">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body p-0" style="max-height:360px;overflow-y:auto;">
                @forelse($recentSubmissions ?? [] as $sub)
                @php
                    $sn  = $sub->siswa?->name ?? 'Siswa';
                    $ini = strtoupper(substr($sn, 0, 1));
                    $colors = ['#0891b2','#7c3aed','#16a34a','#d97706','#dc2626'];
                    $clr = $colors[abs(crc32($sn)) % count($colors)];
                    $t   = $sub->submitted_at ?? $sub->created_at;
                @endphp
                <div class="sub-row">
                    <div class="sub-av" style="background:{{ $clr }};">{{ $ini }}</div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="fw-semibold text-truncate" style="font-size:.85rem;">{{ $sn }}</div>
                        <div class="text-muted text-truncate" style="font-size:.74rem;">
                            {{ $sub->assignment?->title ?? '—' }}
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0 ms-2">
                        <div class="text-muted mb-1" style="font-size:.68rem;">
                            {{ $t?->diffForHumans() }}
                        </div>
                        <a href="{{ route('guru.penilaian.edit', $sub->id) }}?type=assignment"
                           class="btn btn-warning btn-sm"
                           style="font-size:.7rem;padding:.22rem .55rem;border-radius:6px;">
                            <i class="fas fa-pen me-1"></i>Nilai
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex
                                align-items-center justify-content-center mb-3"
                         style="width:60px;height:60px;">
                        <i class="fas fa-check text-success fa-lg"></i>
                    </div>
                    <div class="fw-semibold text-muted mb-1" style="font-size:.9rem;">Semua sudah dinilai!</div>
                    <div class="text-muted small">Tidak ada tugas yang menunggu penilaian.</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ══ BARIS 2: DEADLINE + AKTIVITAS TERBARU ════════════════════════════ --}}
<div class="row g-4 mb-4">

    {{-- Deadline Mendatang --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
            <div class="sec-header">
                <h6><i class="fas fa-calendar-alt me-2 text-danger"></i>Deadline Tugas</h6>
                <a href="{{ route('guru.assignments.index') }}"
                   class="btn btn-sm btn-outline-danger"
                   style="font-size:.74rem;border-radius:8px;">
                    Semua Tugas
                </a>
            </div>
            <div class="card-body pt-3 pb-2">
                @forelse($upcomingDeadlines ?? [] as $dl)
                @php
                    $due    = $dl->due_date;
                    $isPast = $due?->isPast();
                    $isSoon = $due && !$isPast && $due->diffInDays(now()) <= 2;
                    $clr    = $isPast ? '#dc2626' : ($isSoon ? '#d97706' : '#3b82f6');
                    $badge  = $isPast ? 'danger' : ($isSoon ? 'warning' : 'primary');
                @endphp
                <div class="dl-item" style="border-left-color:{{ $clr }};">
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="fw-semibold text-truncate mb-1"
                             style="font-size:.85rem;">{{ $dl->title ?? '—' }}</div>
                        <div class="text-muted" style="font-size:.72rem;">
                            <i class="fas fa-book-open me-1 opacity-50"></i>
                            {{ $dl->subject?->name ?? '—' }}
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0 ms-2">
                        <span class="badge bg-{{ $badge }} mb-1 d-block" style="font-size:.68rem;">
                            {{ $due?->diffForHumans() }}
                        </span>
                        <div class="text-muted" style="font-size:.67rem;">
                            {{ $due?->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex
                                align-items-center justify-content-center mb-2"
                         style="width:48px;height:48px;">
                        <i class="fas fa-calendar-check text-success"></i>
                    </div>
                    <div class="text-muted small">Tidak ada deadline mendatang.</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Aktivitas Terbaru --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
            <div class="sec-header">
                <h6><i class="fas fa-history me-2" style="color:#64748b;"></i>Aktivitas Terbaru</h6>
            </div>
            <div class="card-body p-0" style="max-height:300px;overflow-y:auto;">
                @php
                    {{-- Bangun aktivitas dari data nyata (materi + tugas terbaru) --}}
                    $activities = collect();
                    foreach (($recentMaterials ?? collect())->take(3) as $m) {
                        $activities->push([
                            'icon'  => 'fa-book-open',
                            'color' => '#3b82f6',
                            'bg'    => '#eff6ff',
                            'text'  => 'Materi <strong>' . \Illuminate\Support\Str::limit($m->title, 30) . '</strong> ditambahkan',
                            'time'  => $m->created_at?->diffForHumans(),
                        ]);
                    }
                    foreach (($recentAssignments ?? collect())->take(3) as $a) {
                        $activities->push([
                            'icon'  => 'fa-tasks',
                            'color' => '#22c55e',
                            'bg'    => '#f0fdf4',
                            'text'  => 'Tugas <strong>' . \Illuminate\Support\Str::limit($a->title, 30) . '</strong> dibuat',
                            'time'  => $a->created_at?->diffForHumans(),
                        ]);
                    }
                    $activities = $activities->sortByDesc('time')->values();
                @endphp

                @forelse($activities as $act)
                <div class="act-row">
                    <div class="act-dot" style="background:{{ $act['bg'] }};">
                        <i class="fas {{ $act['icon'] }}" style="color:{{ $act['color'] }};font-size:.6rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-dark lh-sm" style="font-size:.82rem;">{!! $act['text'] !!}</div>
                        <div class="text-muted mt-1" style="font-size:.69rem;">{{ $act['time'] }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-inline-flex
                                align-items-center justify-content-center mb-2"
                         style="width:48px;height:48px;">
                        <i class="fas fa-inbox text-secondary"></i>
                    </div>
                    <div class="text-muted small">Belum ada aktivitas.</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ══ BARIS 3: MATERI TERPOPULER + JADWAL UJIAN ═══════════════════════ --}}
<div class="row g-4 mb-4">

    {{-- Materi Terpopuler --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
            <div class="sec-header">
                <h6><i class="fas fa-fire me-2 text-danger"></i>Materi Terpopuler</h6>
                <a href="{{ route('guru.materials.index') }}"
                   class="btn btn-sm btn-outline-secondary"
                   style="font-size:.74rem;border-radius:8px;">Semua</a>
            </div>
            <div class="card-body p-3">
                @forelse($topMaterials ?? [] as $mat)
                @php $pct = min(100, ($mat->downloads_count / max(1, ($topMaterials->max('downloads_count') ?: 1))) * 100); @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div class="fw-semibold text-truncate me-2" style="font-size:.83rem;max-width:240px;"
                             title="{{ $mat->title }}">
                            {{ $mat->title }}
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary flex-shrink-0"
                              style="font-size:.67rem;">
                            <i class="fas fa-download me-1"></i>{{ $mat->downloads_count ?? 0 }}
                        </span>
                    </div>
                    <div class="progress prog-thin">
                        <div class="progress-bar bg-primary"
                             style="width:{{ $pct }}%;border-radius:3px;"></div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-book fa-2x mb-2 opacity-25 d-block"></i>
                    <small>Belum ada materi.</small>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Jadwal Ujian --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
            <div class="sec-header">
                <h6><i class="fas fa-calendar-alt me-2 text-primary"></i>Jadwal Ujian Mendatang</h6>
                <a href="{{ route('guru.jadwal-ujian.index') }}"
                   class="btn btn-sm btn-outline-primary"
                   style="font-size:.74rem;border-radius:8px;">Semua</a>
            </div>
            <div class="card-body p-0">
                @if(($upcomingExams ?? collect())->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-calendar-times fa-2x mb-2 opacity-25 d-block"></i>
                    <small>Tidak ada jadwal ujian mendatang.</small>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table exam-tbl mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Ujian</th>
                                <th class="text-center" style="width:80px;">Tipe</th>
                                <th style="width:130px;">Jadwal</th>
                                <th style="width:120px;">Kelas</th>
                                <th class="text-center pe-4" style="width:90px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingExams->take(5) as $exam)
                            @php
                                $tc = ['uts'=>'info','uas'=>'danger','quiz'=>'warning','praktikum'=>'success'][$exam->exam_type ?? ''] ?? 'secondary';
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold text-truncate"
                                         style="max-width:200px;"
                                         title="{{ $exam->title ?? '' }}">
                                        {{ $exam->title ?? '—' }}
                                    </div>
                                    <div class="text-muted" style="font-size:.72rem;">
                                        {{ $exam->subject?->name ?? '—' }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $tc }}" style="font-size:.7rem;">
                                        {{ strtoupper($exam->exam_type ?? '—') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-medium" style="font-size:.82rem;">
                                        {{ $exam->start_time?->format('d M Y') ?? '—' }}
                                    </div>
                                    <div class="text-muted" style="font-size:.7rem;">
                                        {{ $exam->start_time?->format('H:i') ?? '' }} WIB
                                    </div>
                                </td>
                                <td class="text-muted" style="font-size:.81rem;">
                                    {{ $exam->kelas?->name ?? 'Semua Kelas' }}
                                </td>
                                <td class="text-center pe-4">
                                    @php $published = $exam->is_published ?? false; @endphp
                                    <span class="badge {{ $published ? 'bg-success' : 'bg-secondary' }}"
                                          style="font-size:.68rem;">
                                        {{ $published ? 'Aktif' : 'Draft' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Animasi counter stat cards ─────────────────────────────────────
    document.querySelectorAll('.stat-val').forEach(function (el) {
        const target = parseInt(el.textContent.replace(/[.,\s]/g, '')) || 0;
        if (target === 0) return;
        let cur  = 0;
        const step = Math.max(1, Math.ceil(target / 30));
        const t = setInterval(function () {
            cur = Math.min(cur + step, target);
            el.textContent = cur.toLocaleString('id-ID');
            if (cur >= target) clearInterval(t);
        }, 28);
    });

    // ── Animasi progress bar materi ────────────────────────────────────
    document.querySelectorAll('.prog-thin .progress-bar').forEach(function (bar) {
        const w = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => { bar.style.transition = 'width .6s ease'; bar.style.width = w; }, 200);
    });
});
</script>
@endpush
