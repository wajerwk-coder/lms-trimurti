@php
    $role       = Auth::user()->role ?? 'guest';
    $layout     = match($role) { 'guru' => 'layouts.guru', 'admin' => 'layouts.admin', default => 'layouts.siswa' };
    $indexRoute = match($role) { 'guru' => 'guru.jadwal-ujian.index', 'admin' => 'admin.exam-schedules.index', default => 'siswa.jadwal-ujian.index' };
    $dashRoute  = match($role) { 'guru' => 'guru.dashboard', 'admin' => 'admin.dashboard', default => 'siswa.dashboard' };
@endphp

@extends($layout)

@section('title', 'Jadwal Ujian')
@section('page-title', 'Jadwal Ujian')
@section('page-subtitle', 'Daftar jadwal ujian yang telah dipublikasikan.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route($dashRoute) }}">Beranda</a></li>
    <li class="breadcrumb-item active" aria-current="page">Jadwal Ujian</li>
@endsection

@push('css')
<style>
/* ── Stats ─────────────────────────────────────────── */
.jadwal-stat {
    border: none; border-radius: 14px; overflow: hidden;
    transition: transform .18s, box-shadow .18s;
}
.jadwal-stat:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(0,0,0,.09) !important; }
.jadwal-stat-icon {
    width: 44px; height: 44px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; color: #fff; flex-shrink: 0;
}

/* ── Cards ─────────────────────────────────────────── */
.jadwal-card {
    border: 1px solid #e8edf2 !important;
    border-radius: 14px !important;
    transition: transform .18s, box-shadow .18s;
    overflow: hidden;
}
.jadwal-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0,0,0,.10) !important;
}

/* ── Countdown ─────────────────────────────────────── */
.countdown-box {
    border-radius: 10px;
    padding: .5rem .75rem;
    display: flex; align-items: center; gap: .5rem;
}

/* ── Filter bar ────────────────────────────────────── */
.filter-bar {
    background: #fff; border: 1px solid #e8edf2;
    border-radius: 14px; padding: .875rem 1.25rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}

/* ── Table (admin/guru) ────────────────────────────── */
.jadwal-tbl th {
    font-size:.72rem; font-weight:700; letter-spacing:.05em;
    text-transform:uppercase; color:#94a3b8;
    background:#f8fafc; border-bottom:1px solid #e8edf2 !important;
}
.jadwal-tbl td { font-size:.85rem; vertical-align:middle; }
.jadwal-tbl tr:hover td { background:#f8fafc; }
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
    $upcoming = $schedules->getCollection()->filter(fn($s) =>
        str_contains(strtolower($s->status ?? ''), 'akan'))->count();
    $ongoing  = $schedules->getCollection()->filter(fn($s) =>
        strtolower($s->status ?? '') === 'berlangsung')->count();
    $done     = $schedules->getCollection()->filter(fn($s) =>
        strtolower($s->status ?? '') === 'selesai')->count();
@endphp

{{-- ══ STATS ════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    @foreach([
        ['from'=>'#7c3aed','to'=>'#6d28d9','icon'=>'fa-calendar-alt','val'=>$schedules->total(),'label'=>'Total Jadwal'],
        ['from'=>'#d97706','to'=>'#b45309','icon'=>'fa-clock',        'val'=>$upcoming,          'label'=>'Akan Datang'],
        ['from'=>'#16a34a','to'=>'#15803d','icon'=>'fa-play-circle',  'val'=>$ongoing,           'label'=>'Berlangsung'],
        ['from'=>'#64748b','to'=>'#475569','icon'=>'fa-check-circle', 'val'=>$done,              'label'=>'Selesai'],
    ] as $s)
    <div class="col-6 col-md-3">
        <div class="card jadwal-stat shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="jadwal-stat-icon"
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
        </div>
    </div>
    @endforeach
</div>

{{-- ══ FILTER ═══════════════════════════════════════════ --}}
<div class="filter-bar">
    <form method="GET" action="{{ route($indexRoute) }}" class="row g-2 align-items-end">
        <div class="col-md-{{ $role === 'siswa' ? '5' : '4' }}">
            <div class="input-group" style="border:1.5px solid #e2e8f0;border-radius:9px;overflow:hidden;">
                <span class="input-group-text border-0 bg-white ps-3">
                    <i class="fas fa-search text-muted" style="font-size:.8rem;"></i>
                </span>
                <input type="text" name="search" class="form-control border-0"
                       placeholder="Cari judul ujian…"
                       value="{{ request('search') }}"
                       style="box-shadow:none;">
            </div>
        </div>
        <div class="col-md-3">
            <select name="exam_type" class="form-select" style="border-radius:9px;border:1.5px solid #e2e8f0;">
                <option value="">Semua Tipe</option>
                @foreach(['uts'=>'UTS','uas'=>'UAS','quiz'=>'Quiz','praktikum'=>'Praktikum','lainnya'=>'Lainnya'] as $val => $lbl)
                    <option value="{{ $val }}" {{ request('exam_type') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        @if($role !== 'siswa')
        <div class="col-md-2">
            <select name="kelas_id" class="form-select" style="border-radius:9px;border:1.5px solid #e2e8f0;">
                <option value="">Semua Kelas</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                        {{ $k->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill" style="border-radius:9px;">
                <i class="fas fa-search me-1"></i>Cari
            </button>
            @if(request('search') || request('exam_type') || request('kelas_id'))
                <a href="{{ route($indexRoute) }}" class="btn btn-outline-secondary" style="border-radius:9px;">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </div>
    </form>
</div>

{{-- ══ TAMPILAN SISWA: CARD GRID ════════════════════════ --}}
@if($role === 'siswa')

<div class="row g-3">
    @forelse($schedules as $schedule)
    @php
        $examTypeMap = [
            'uts'       => ['label'=>'UTS',       'from'=>'#0891b2','to'=>'#0e7490','icon'=>'fa-file-alt'],
            'uas'       => ['label'=>'UAS',       'from'=>'#dc2626','to'=>'#b91c1c','icon'=>'fa-file-invoice'],
            'quiz'      => ['label'=>'Quiz',      'from'=>'#d97706','to'=>'#b45309','icon'=>'fa-question-circle'],
            'praktikum' => ['label'=>'Praktikum', 'from'=>'#16a34a','to'=>'#15803d','icon'=>'fa-flask'],
            'lainnya'   => ['label'=>'Ujian',     'from'=>'#7c3aed','to'=>'#6d28d9','icon'=>'fa-clipboard'],
        ];
        $et     = $examTypeMap[$schedule->exam_type] ?? $examTypeMap['lainnya'];
        $status = strtolower($schedule->status ?? '');

        // Status config
        [$sClr, $sBg, $sLabel] = match(true) {
            strtolower($status) === 'berlangsung'          => ['#16a34a', 'rgba(22,163,74,.09)',    'Berlangsung'],
            str_contains(strtolower($status), 'akan')      => ['#d97706', 'rgba(217,119,6,.09)',    'Akan Datang'],
            strtolower($status) === 'selesai'              => ['#64748b', 'rgba(100,116,139,.09)', 'Selesai'],
            default                                         => ['#94a3b8', 'rgba(148,163,184,.09)', $schedule->status ?? '—'],
        };

        // Hitung sisa waktu
        $startTime = $schedule->start_time;
        $diffDays  = $startTime->isFuture() ? now()->diffInDays($startTime) : null;
        $diffHours = $startTime->isFuture() ? now()->diffInHours($startTime) : null;
        $isToday   = $startTime->isToday();
        $isTomorrow= $startTime->isTomorrow();
    @endphp

    <div class="col-md-6 col-xl-4">
        <div class="card jadwal-card shadow-sm h-100">

            {{-- Top bar --}}
            <div style="height:5px;background:linear-gradient(90deg,{{ $et['from'] }},{{ $et['to'] }});"></div>

            <div class="card-body p-4">

                {{-- Header: icon + tipe + status --}}
                <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:48px;height:48px;background:linear-gradient(135deg,{{ $et['from'] }},{{ $et['to'] }});">
                        <i class="fas {{ $et['icon'] }} text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="fw-bold text-dark lh-sm mb-1"
                             style="font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                             title="{{ $schedule->title }}">
                            {{ $schedule->title }}
                        </div>
                        <div>
                            <span class="badge fw-semibold me-1"
                                  style="background:linear-gradient(135deg,{{ $et['from'] }},{{ $et['to'] }});color:#fff;border-radius:20px;font-size:.68rem;padding:.2rem .6rem;">
                                {{ $et['label'] }}
                            </span>
                        </div>
                    </div>
                    <span class="badge flex-shrink-0 fw-semibold"
                          style="background:{{ $sBg }};color:{{ $sClr }};border-radius:20px;font-size:.7rem;padding:.22rem .7rem;">
                        @if($sClr === '#16a34a')<span class="me-1" style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#16a34a;animation:pulse-dot 1.4s infinite;"></span>@endif
                        {{ $sLabel }}
                    </span>
                </div>

                {{-- Mata Pelajaran & Kelas --}}
                <div class="d-flex flex-column gap-1 mb-3" style="font-size:.8rem;">
                    <div class="text-muted">
                        <i class="fas fa-book me-1 opacity-75"></i>
                        {{ $schedule->subject?->name ?? '—' }}
                    </div>
                    <div class="text-muted">
                        <i class="fas fa-door-open me-1 opacity-75"></i>
                        {{ $schedule->kelas?->name ?? 'Semua Kelas' }}
                    </div>
                </div>

                {{-- Tanggal & Waktu --}}
                <div class="p-3 rounded-3 mb-3"
                     style="background:rgba(124,58,237,.06);border:1px solid rgba(124,58,237,.12);">
                    <div class="d-flex align-items-center gap-2 mb-1" style="font-size:.82rem;">
                        <i class="fas fa-calendar-day" style="color:#7c3aed;width:14px;"></i>
                        <span class="fw-semibold text-dark">
                            {{ $startTime->translatedFormat('l, d F Y') }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-1" style="font-size:.82rem;">
                        <i class="fas fa-clock" style="color:#7c3aed;width:14px;"></i>
                        <span class="text-dark">
                            {{ $startTime->format('H:i') }}
                            &ndash;
                            {{ $schedule->end_time->format('H:i') }} WIB
                        </span>
                        <span class="badge fw-semibold"
                              style="background:rgba(124,58,237,.1);color:#7c3aed;border-radius:20px;font-size:.68rem;">
                            {{ $schedule->duration_minutes }} mnt
                        </span>
                    </div>
                    @if($schedule->location)
                    <div class="d-flex align-items-center gap-2" style="font-size:.82rem;">
                        <i class="fas fa-map-marker-alt" style="color:#7c3aed;width:14px;"></i>
                        <span class="text-dark">{{ $schedule->location }}</span>
                    </div>
                    @endif
                </div>

                {{-- Countdown --}}
                @if($startTime->isFuture())
                @php
                    $urgentColor = ($diffHours !== null && $diffHours <= 24) ? '#dc2626' : ($isToday || $isTomorrow ? '#d97706' : '#0891b2');
                    $urgentBg    = ($diffHours !== null && $diffHours <= 24) ? 'rgba(220,38,38,.08)' : ($isToday || $isTomorrow ? 'rgba(217,119,6,.08)' : 'rgba(8,145,178,.08)');
                @endphp
                <div class="countdown-box"
                     style="background:{{ $urgentBg }};border:1px solid {{ $urgentColor }}22;">
                    <i class="fas {{ $diffHours !== null && $diffHours <= 24 ? 'fa-exclamation-triangle' : 'fa-hourglass-half' }}"
                       style="color:{{ $urgentColor }};font-size:.8rem;"></i>
                    <span class="fw-semibold" style="color:{{ $urgentColor }};font-size:.8rem;">
                        @if($isToday)
                            Hari ini! Pukul {{ $startTime->format('H:i') }}
                        @elseif($isTomorrow)
                            Besok, {{ $startTime->format('H:i') }}
                        @elseif($diffHours !== null && $diffHours <= 24)
                            {{ $diffHours }} jam lagi
                        @elseif($diffDays !== null && $diffDays <= 7)
                            {{ $diffDays }} hari lagi
                        @else
                            {{ $startTime->diffForHumans() }}
                        @endif
                    </span>
                </div>
                @elseif(strtolower($schedule->status ?? '') === 'berlangsung')
                <div class="countdown-box"
                     style="background:rgba(22,163,74,.08);border:1px solid rgba(22,163,74,.2);">
                    <span class="d-inline-block rounded-circle flex-shrink-0"
                          style="width:8px;height:8px;background:#16a34a;animation:pulse-dot 1.2s ease-in-out infinite;"></span>
                    <span class="fw-semibold" style="color:#16a34a;font-size:.8rem;">Sedang Berlangsung</span>
                </div>
                @else
                <div class="countdown-box"
                     style="background:rgba(100,116,139,.07);border:1px solid #e2e8f0;">
                    <i class="fas fa-check" style="color:#64748b;font-size:.75rem;"></i>
                    <span class="text-muted fw-semibold" style="font-size:.8rem;">Ujian telah selesai</span>
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-body text-center py-5">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                     style="width:72px;height:72px;background:rgba(124,58,237,.08);">
                    <i class="fas fa-calendar-times fa-2x" style="color:#7c3aed;opacity:.5;"></i>
                </div>
                <h5 class="fw-semibold text-muted mb-2">
                    {{ request('search') || request('exam_type') ? 'Tidak ditemukan' : 'Belum ada jadwal' }}
                </h5>
                <p class="text-muted small mb-3">
                    {{ request('search') || request('exam_type')
                        ? 'Coba ubah kata kunci atau filter.'
                        : 'Jadwal ujian akan muncul di sini saat dipublikasikan.' }}
                </p>
                @if(request('search') || request('exam_type'))
                    <a href="{{ route($indexRoute) }}"
                       class="btn btn-sm"
                       style="border-radius:8px;background:rgba(124,58,237,.1);color:#7c3aed;border:1px solid rgba(124,58,237,.2);">
                        <i class="fas fa-times me-1"></i>Hapus Filter
                    </a>
                @endif
            </div>
        </div>
    </div>
    @endforelse
</div>

{{-- ══ TAMPILAN GURU/ADMIN: TABEL ════════════════════════ --}}
@else

<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4"
         style="border-radius:14px 14px 0 0;border-bottom:1px solid #e8edf2;">
        <h6 class="mb-0 fw-bold">
            <i class="fas fa-calendar-alt me-2" style="color:#7c3aed;"></i>Daftar Jadwal Ujian
        </h6>
        <span class="badge fw-semibold"
              style="background:rgba(100,116,139,.1);color:#64748b;border-radius:20px;">
            {{ $schedules->total() }} jadwal
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table jadwal-tbl align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">Judul</th>
                        <th class="py-3">Tipe</th>
                        <th class="py-3">Mata Pelajaran</th>
                        <th class="py-3">Kelas</th>
                        <th class="py-3">Jadwal</th>
                        <th class="py-3">Lokasi</th>
                        <th class="text-center pe-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                    @php
                        $typeColors = ['uts'=>'#0891b2','uas'=>'#dc2626','quiz'=>'#d97706','praktikum'=>'#16a34a','lainnya'=>'#7c3aed'];
                        $tc         = $typeColors[$schedule->exam_type] ?? '#64748b';
                        $status2    = strtolower($schedule->status ?? '');
                        [$sClr2, $sLbl2] = match(true) {
                            strtolower($status2) === 'berlangsung'        => ['#16a34a','Berlangsung'],
                            str_contains(strtolower($status2), 'akan')    => ['#d97706','Akan Datang'],
                            strtolower($status2) === 'selesai'            => ['#64748b','Selesai'],
                            default => ['#94a3b8', $schedule->status ?? '—'],
                        };
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold text-dark">{{ $schedule->title }}</div>
                            @if($schedule->description)
                                <div class="text-muted" style="font-size:.75rem;">
                                    {{ Str::limit($schedule->description, 55) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge fw-semibold"
                                  style="background:{{ $tc }}22;color:{{ $tc }};border-radius:20px;font-size:.7rem;padding:.22rem .7rem;">
                                {{ strtoupper($schedule->exam_type) }}
                            </span>
                        </td>
                        <td class="text-muted" style="font-size:.82rem;">{{ $schedule->subject?->name ?? '—' }}</td>
                        <td class="text-muted" style="font-size:.82rem;">{{ $schedule->kelas?->name ?? 'Semua' }}</td>
                        <td>
                            <div style="font-size:.82rem;">
                                {{ $schedule->start_time->translatedFormat('d M Y') }}
                            </div>
                            <div class="text-muted" style="font-size:.76rem;">
                                {{ $schedule->start_time->format('H:i') }}–{{ $schedule->end_time->format('H:i') }}
                                ({{ $schedule->duration_minutes }} mnt)
                            </div>
                        </td>
                        <td class="text-muted" style="font-size:.82rem;">{{ $schedule->location ?? '—' }}</td>
                        <td class="text-center pe-4">
                            <span class="badge fw-semibold"
                                  style="background:{{ $sClr2 }}18;color:{{ $sClr2 }};border-radius:20px;font-size:.7rem;padding:.22rem .7rem;">
                                {{ $sLbl2 }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-times fa-2x opacity-25 mb-2 d-block"></i>
                            Belum ada jadwal ujian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($schedules->hasPages())
    <div class="card-footer bg-white px-4 py-3 d-flex align-items-center justify-content-between border-top">
        <small class="text-muted">
            {{ $schedules->firstItem() }}–{{ $schedules->lastItem() }}
            dari {{ number_format($schedules->total()) }}
        </small>
        {{ $schedules->appends(request()->query())->links() }}
    </div>
    @endif
</div>

@endif

{{-- Pagination siswa --}}
@if($role === 'siswa' && $schedules->hasPages())
<div class="d-flex align-items-center justify-content-between mt-4">
    <small class="text-muted">
        {{ $schedules->firstItem() }}–{{ $schedules->lastItem() }}
        dari {{ number_format($schedules->total()) }} jadwal
    </small>
    {{ $schedules->appends(request()->query())->links() }}
</div>
@endif

@endsection

@push('js')
<style>
@keyframes pulse-dot {
    0%, 100% { opacity:1; transform:scale(1); }
    50%       { opacity:.5; transform:scale(.8); }
}
</style>
@endpush
