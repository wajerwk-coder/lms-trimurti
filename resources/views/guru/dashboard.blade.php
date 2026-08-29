@extends('layouts.guru')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Selamat datang, ' . auth()->user()->name)

@push('css')
<style>
/* ── Hero ─────────────────────────────────────────────────────── */
.hero-guru {
    background: linear-gradient(135deg, #0f766e 0%, #0891b2 55%, #1d4ed8 100%);
    border-radius: 18px;
    overflow: hidden;
    position: relative;
}
.hero-guru::before {
    content: '';
    position: absolute; top: -80px; right: -80px;
    width: 260px; height: 260px;
    background: rgba(255,255,255,.07);
    border-radius: 50%;
}
.hero-guru::after {
    content: '';
    position: absolute; bottom: -60px; right: 140px;
    width: 180px; height: 180px;
    background: rgba(255,255,255,.04);
    border-radius: 50%;
}
.hero-mini-card {
    background: rgba(255,255,255,.14);
    border-radius: 12px;
    text-align: center;
    padding: .65rem .5rem;
    backdrop-filter: blur(4px);
}

/* ── Stat cards ───────────────────────────────────────────────── */
.stat-card {
    border: none;
    border-radius: 14px;
    transition: transform .2s, box-shadow .2s;
    overflow: hidden;
    position: relative;
}
.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 32px rgba(0,0,0,.11) !important;
}
.stat-card .stat-icon {
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.stat-card .stat-val {
    font-size: 1.75rem;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -.5px;
}

/* ── Quick action buttons ─────────────────────────────────────── */
.quick-btn {
    display: flex;
    align-items: center;
    gap: .7rem;
    padding: .7rem .9rem;
    border-radius: 12px;
    background: #f8fafc;
    border: 1.5px solid #e8edf2;
    color: #334155;
    text-decoration: none !important;
    transition: background .15s, border-color .15s, transform .15s;
    font-size: .83rem;
    font-weight: 500;
}
.quick-btn:hover {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #1d4ed8;
    transform: translateX(3px);
}
.quick-btn .q-icon {
    width: 36px; height: 36px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

/* ── Submission row ───────────────────────────────────────────── */
.submission-row {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .65rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    transition: background .12s;
}
.submission-row:last-child { border-bottom: none; }
.submission-row:hover { background: #f8fafc; }

/* ── Deadline card ────────────────────────────────────────────── */
.deadline-item {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .6rem .75rem;
    border-radius: 10px;
    border-left: 4px solid;
    background: #f8fafc;
    margin-bottom: .5rem;
    transition: transform .12s;
}
.deadline-item:hover { transform: translateX(3px); }
.deadline-item:last-child { margin-bottom: 0; }

/* ── Exam table ───────────────────────────────────────────────── */
.exam-table th { font-size: .75rem; font-weight: 600; color: #94a3b8; letter-spacing: .04em; }
.exam-table td { font-size: .83rem; vertical-align: middle; }

/* ── Section header ───────────────────────────────────────────── */
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .875rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
}
.section-header h6 { margin: 0; font-weight: 700; font-size: .9rem; }
</style>
@endpush

@section('content')
@php $guruProfile = auth()->user()->guruProfile; @endphp

{{-- ══ HERO BANNER ════════════════════════════════════════════════ --}}
<div class="hero-guru p-4 mb-4">
    <div class="row align-items-center g-3">

        {{-- Kiri: sapaan + tombol aksi --}}
        <div class="col-md-7">
            <div class="d-flex align-items-center gap-3 mb-3">
                @if(auth()->user()->photo)
                    <img src="{{ asset('storage/'.auth()->user()->photo) }}"
                         class="rounded-circle border border-3 border-white border-opacity-40 flex-shrink-0"
                         style="width:54px;height:54px;object-fit:cover;" alt="">
                @else
                    <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center
                                justify-content-center fw-bold text-white flex-shrink-0"
                         style="width:54px;height:54px;font-size:1.4rem;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div class="text-white">
                    <div style="font-size:.78rem;opacity:.7;margin-bottom:.15rem;">
                        {{ now()->translatedFormat('l, d F Y') }}
                    </div>
                    <h4 class="fw-bold mb-0">Halo, {{ auth()->user()->name }}! 👋</h4>
                    @if($guruProfile?->mata_pelajaran)
                        <div style="font-size:.78rem;opacity:.75;margin-top:.2rem;">
                            <i class="fas fa-book-open me-1"></i>{{ $guruProfile->mata_pelajaran }}
                        </div>
                    @endif
                </div>
            </div>
            <p class="text-white mb-3" style="font-size:.84rem;opacity:.8;max-width:420px;">
                Pantau kemajuan siswa, kelola materi, dan catat kehadiran dengan mudah.
            </p>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('guru.absensi.create') }}"
                   class="btn btn-light btn-sm fw-semibold shadow-sm">
                    <i class="fas fa-clipboard-check me-1"></i> Input Absensi
                </a>
                <a href="{{ route('guru.materials.create') }}"
                   class="btn btn-outline-light btn-sm">
                    <i class="fas fa-book-open me-1"></i> Upload Materi
                </a>
                <a href="{{ route('guru.assignments.create') }}"
                   class="btn btn-outline-light btn-sm">
                    <i class="fas fa-tasks me-1"></i> Buat Tugas
                </a>
            </div>
        </div>

        {{-- Kanan: mini stats --}}
        <div class="col-md-5 d-none d-md-block">
            <div class="row g-2">
                @foreach([
                    ['fa-book',           $stats['total_materials']   ?? 0, 'Materi'],
                    ['fa-tasks',          $stats['total_assignments']  ?? 0, 'Tugas'],
                    ['fa-users',          $stats['total_students']     ?? 0, 'Siswa'],
                    ['fa-star',           $stats['pending_grading']    ?? 0, 'Perlu Dinilai'],
                ] as [$ic, $val, $lbl])
                <div class="col-6">
                    <div class="hero-mini-card">
                        <i class="fas {{ $ic }} text-white opacity-75 fa-sm mb-1 d-block"></i>
                        <div class="fw-bold text-white fs-5 lh-1">{{ number_format($val) }}</div>
                        <div class="text-white mt-1" style="font-size:.68rem;opacity:.75;">{{ $lbl }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ══ STAT CARDS ══════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    @php
    $cards = [
        ['from'=>'#0f766e','to'=>'#0891b2','icon'=>'fa-book',           'val'=>$stats['total_materials']  ?? 0,'label'=>'Total Materi',      'sub'=>'Materi diunggah',     'url'=>route('guru.materials.index')],
        ['from'=>'#059669','to'=>'#10b981','icon'=>'fa-tasks',          'val'=>$stats['total_assignments'] ?? 0,'label'=>'Total Tugas',       'sub'=>'Tugas dibuat',        'url'=>route('guru.assignments.index')],
        ['from'=>'#7c3aed','to'=>'#a21caf','icon'=>'fa-flask',          'val'=>$stats['total_practicals']  ?? 0,'label'=>'Praktikum',         'sub'=>'Sesi praktikum',      'url'=>route('guru.praktikum.index')],
        ['from'=>'#d97706','to'=>'#f59e0b','icon'=>'fa-star',           'val'=>$stats['pending_grading']   ?? 0,'label'=>'Perlu Dinilai',     'sub'=>'Menunggu penilaian',  'url'=>route('guru.penilaian.index')],
        ['from'=>'#dc2626','to'=>'#ef4444','icon'=>'fa-clipboard-check','val'=>$stats['today_attendance']  ?? 0,'label'=>'Absensi Hari Ini',  'sub'=>'Siswa tercatat',      'url'=>route('guru.absensi.index')],
        ['from'=>'#1d4ed8','to'=>'#3b82f6','icon'=>'fa-users',          'val'=>$stats['total_students']    ?? 0,'label'=>'Total Siswa',       'sub'=>'Siswa aktif',         'url'=>route('guru.absensi.index')],
    ];
    @endphp

    @foreach($cards as $c)
    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ $c['url'] }}" class="text-decoration-none">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="stat-icon mb-3"
                         style="background:linear-gradient(135deg,{{ $c['from'] }},{{ $c['to'] }});color:#fff;">
                        <i class="fas {{ $c['icon'] }}"></i>
                    </div>
                    <div class="stat-val text-dark mb-1">{{ number_format($c['val']) }}</div>
                    <div class="fw-semibold text-dark" style="font-size:.8rem;">{{ $c['label'] }}</div>
                    <div class="text-muted mt-1" style="font-size:.7rem;">{{ $c['sub'] }}</div>
                </div>
                {{-- accent bar --}}
                <div style="height:3px;background:linear-gradient(90deg,{{ $c['from'] }},{{ $c['to'] }});"></div>
            </div>
        </a>
    </div>
    @endforeach
</div>

{{-- ══ QUICK ACTIONS + SUBMISSIONS ════════════════════════════════ --}}
<div class="row g-4 mb-4">

    {{-- Aksi Cepat --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
            <div class="section-header">
                <h6><i class="fas fa-bolt me-2 text-warning"></i>Aksi Cepat</h6>
            </div>
            <div class="card-body d-flex flex-column gap-2 pt-3">
                @foreach([
                    [route('guru.absensi.create'),     'fa-clipboard-check','#fee2e2','#ef4444','Input Absensi',   'Catat kehadiran hari ini'],
                    [route('guru.materials.create'),   'fa-book-open',      '#dbeafe','#3b82f6','Upload Materi',   'Bagikan materi baru'],
                    [route('guru.assignments.create'), 'fa-tasks',          '#dcfce7','#22c55e','Buat Tugas',      'Buat tugas untuk siswa'],
                    [route('guru.praktikum.create'),   'fa-flask',          '#fef9c3','#eab308','Buat Praktikum',  'Tambah sesi praktikum'],
                    [route('guru.penilaian.index'),    'fa-star',           '#f3e8ff','#a855f7','Beri Penilaian',  'Nilai tugas / praktikum'],
                    [route('guru.laporan.index'),      'fa-chart-bar',      '#f0fdfa','#14b8a6','Lihat Laporan',   'Rekap nilai & absensi'],
                ] as [$url, $icon, $ibg, $iclr, $title, $sub])
                <a href="{{ $url }}" class="quick-btn">
                    <div class="q-icon" style="background:{{ $ibg }};">
                        <i class="fas {{ $icon }}" style="color:{{ $iclr }};font-size:.82rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark lh-1 mb-1">{{ $title }}</div>
                        <div class="text-muted" style="font-size:.71rem;">{{ $sub }}</div>
                    </div>
                    <i class="fas fa-chevron-right text-muted" style="font-size:.58rem;"></i>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Perlu Dinilai --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
            <div class="section-header">
                <h6><i class="fas fa-star me-2 text-warning"></i>Perlu Dinilai</h6>
                <a href="{{ route('guru.penilaian.index') }}"
                   class="btn btn-sm btn-outline-warning" style="font-size:.75rem;">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                @forelse($recentSubmissions ?? [] as $sub)
                @php
                    $siswaName = $sub->siswa?->name ?? 'Siswa';
                    $judul     = $sub->assignment?->title ?? '—';
                    $subTime   = $sub->submitted_at ?? $sub->created_at;
                    $initials  = strtoupper(substr($siswaName, 0, 1));
                @endphp
                <div class="submission-row">
                    <div class="rounded-circle d-flex align-items-center justify-content-center
                                fw-bold text-white flex-shrink-0"
                         style="width:38px;height:38px;font-size:.85rem;
                                background:linear-gradient(135deg,#0891b2,#3b82f6);">
                        {{ $initials }}
                    </div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="fw-semibold text-dark text-truncate" style="font-size:.84rem;">
                            {{ $siswaName }}
                        </div>
                        <div class="text-muted text-truncate" style="font-size:.74rem;">
                            {{ $judul }}
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="text-muted mb-1" style="font-size:.7rem;">
                            {{ $subTime?->diffForHumans() }}
                        </div>
                        <a href="{{ route('guru.penilaian.edit', $sub->id) }}"
                           class="btn btn-warning btn-sm"
                           style="font-size:.72rem;padding:.2rem .55rem;border-radius:6px;">
                            <i class="fas fa-pen me-1"></i>Nilai
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex
                                align-items-center justify-content-center mb-3"
                         style="width:60px;height:60px;">
                        <i class="fas fa-check text-success fa-lg"></i>
                    </div>
                    <h6 class="text-muted mb-1">Semua sudah dinilai!</h6>
                    <small>Tidak ada tugas yang menunggu penilaian.</small>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ══ DEADLINE + AKTIVITAS ════════════════════════════════════════ --}}
<div class="row g-4 mb-4">

    {{-- Deadline Mendatang --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
            <div class="section-header">
                <h6><i class="fas fa-calendar-alt me-2 text-danger"></i>Deadline Mendatang</h6>
                <a href="{{ route('guru.assignments.index') }}"
                   class="btn btn-sm btn-outline-danger" style="font-size:.75rem;">
                    Semua Tugas
                </a>
            </div>
            <div class="card-body pt-3">
                @forelse($upcomingDeadlines ?? [] as $dl)
                @php
                    $due     = $dl->due_date;
                    $isPast  = $due?->isPast();
                    $isSoon  = $due && !$isPast && $due->diffInDays(now()) <= 2;
                    $clr     = $isPast ? '#dc2626' : ($isSoon ? '#d97706' : '#3b82f6');
                    $badge   = $isPast ? 'danger' : ($isSoon ? 'warning' : 'primary');
                @endphp
                <div class="deadline-item" style="border-left-color:{{ $clr }};">
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="fw-semibold text-truncate" style="font-size:.85rem;">
                            {{ $dl->title ?? '—' }}
                        </div>
                        <div class="text-muted" style="font-size:.73rem;">
                            {{ $dl->subject?->name ?? '—' }}
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <span class="badge bg-{{ $badge }} mb-1 d-block">
                            {{ $due?->diffForHumans() }}
                        </span>
                        <div class="text-muted" style="font-size:.68rem;">
                            {{ $due?->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex
                                align-items-center justify-content-center mb-2"
                         style="width:48px;height:48px;">
                        <i class="fas fa-calendar-check text-success"></i>
                    </div>
                    <div class="small">Tidak ada deadline mendatang.</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Aktivitas Terbaru --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
            <div class="section-header">
                <h6><i class="fas fa-history me-2 text-secondary"></i>Aktivitas Terbaru</h6>
            </div>
            <div class="card-body p-0">
                @forelse($recentActivities ?? [] as $activity)
                @php
                    $desc    = is_array($activity) ? ($activity['description'] ?? '—') : ($activity->description ?? '—');
                    $actTime = is_array($activity)
                        ? optional(\Carbon\Carbon::parse($activity['created_at'] ?? null))->diffForHumans()
                        : optional($activity->created_at)->diffForHumans();
                @endphp
                <div class="d-flex align-items-start gap-2 px-4 py-2 border-bottom small">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center
                                justify-content-center flex-shrink-0 mt-1"
                         style="width:28px;height:28px;">
                        <i class="fas fa-bell text-primary" style="font-size:.6rem;"></i>
                    </div>
                    <div>
                        <div class="text-dark lh-sm">{{ $desc }}</div>
                        <div class="text-muted" style="font-size:.7rem;">{{ $actTime }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-inline-flex
                                align-items-center justify-content-center mb-2"
                         style="width:48px;height:48px;">
                        <i class="fas fa-inbox text-secondary"></i>
                    </div>
                    <div class="small">Belum ada aktivitas.</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ══ JADWAL UJIAN ════════════════════════════════════════════════ --}}
@if(($upcomingExams ?? collect())->count() > 0)
<div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
    <div class="section-header">
        <h6><i class="fas fa-calendar-alt me-2 text-primary"></i>Jadwal Ujian Mendatang</h6>
        <a href="{{ route('guru.jadwal-ujian.index') }}"
           class="btn btn-sm btn-outline-primary" style="font-size:.75rem;">
            Lihat Semua
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table exam-table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3">UJIAN</th>
                        <th class="text-center py-3">TIPE</th>
                        <th class="py-3">JADWAL</th>
                        <th class="py-3">KELAS</th>
                        <th class="text-center pe-4 py-3">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($upcomingExams->take(5) as $exam)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="fw-semibold text-dark">{{ $exam->title ?? '—' }}</div>
                            <small class="text-muted">
                                {{ $exam->subject?->name ?? $exam->subject?->nama ?? '—' }}
                            </small>
                        </td>
                        <td class="text-center py-3">
                            @php
                                $tc = ['uts'=>'info','uas'=>'danger','quiz'=>'warning','praktikum'=>'success'][$exam->exam_type ?? ''] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $tc }}">
                                {{ strtoupper($exam->exam_type ?? '—') }}
                            </span>
                        </td>
                        <td class="py-3">
                            <div class="fw-medium">{{ $exam->start_time?->format('d M Y') ?? '—' }}</div>
                            <small class="text-muted">
                                {{ $exam->start_time?->format('H:i') ?? '' }} WIB
                            </small>
                        </td>
                        <td class="text-muted py-3">{{ $exam->kelas?->name ?? 'Semua Kelas' }}</td>
                        <td class="text-center pe-4 py-3">
                            <span class="badge bg-{{ $exam->status_color ?? 'secondary' }}">
                                {{ ucfirst($exam->status ?? '—') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Animate stat counters
    document.querySelectorAll('.stat-val').forEach(function (el) {
        const raw    = el.textContent.replace(/[.,]/g, '').trim();
        const target = parseInt(raw) || 0;
        if (target === 0) return;
        let cur = 0;
        const step = Math.max(1, Math.ceil(target / 30));
        const t = setInterval(function () {
            cur = Math.min(cur + step, target);
            el.textContent = cur.toLocaleString('id-ID');
            if (cur >= target) clearInterval(t);
        }, 28);
    });
});
</script>
@endpush
