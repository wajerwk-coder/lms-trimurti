@extends('layouts.siswa')

@section('title', 'Dashboard Siswa')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Selamat datang, ' . auth()->user()->name)

@push('css')
<style>
.hero-siswa {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #a21caf 100%);
    border-radius: 16px; overflow: hidden; position: relative;
}
.hero-siswa::before {
    content:''; position:absolute; top:-60px; right:-60px;
    width:220px; height:220px; background:rgba(255,255,255,.07); border-radius:50%;
    pointer-events: none; /* PENTING: pseudo-element tidak menghalangi klik */
    z-index: 0;
}
.hero-siswa::after {
    content:''; position:absolute; bottom:-50px; right:140px;
    width:150px; height:150px; background:rgba(255,255,255,.04); border-radius:50%;
    pointer-events: none; /* PENTING: pseudo-element tidak menghalangi klik */
    z-index: 0;
}
/* Pastikan konten hero berada di atas pseudo-elements */
.hero-siswa > * { position: relative; z-index: 1; }

/* ── Hero banner buttons — konsisten semua role ───────────────── */
.hero-siswa .btn-light,
.hero-siswa .btn-outline-light {
    background: rgba(255,255,255,.92) !important;
    color: #4f46e5 !important;
    border: none !important;
    font-weight: 600 !important;
    font-size: .82rem !important;
    padding: .38rem .9rem !important;
    border-radius: 8px !important;
    transition: background .15s, transform .15s, box-shadow .15s !important;
    box-shadow: 0 2px 6px rgba(0,0,0,.12);
}
.hero-siswa .btn-light:hover,
.hero-siswa .btn-outline-light:hover {
    background: #fff !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,.18);
}

.stat-card-s {
    border-radius: 14px; border: none;
    transition: transform .2s, box-shadow .2s;
}
.stat-card-s:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,.12) !important; }
.quick-btn-s {
    display: flex; align-items: center; gap: .75rem;
    padding: .85rem 1rem; border-radius: 12px;
    background: #f8fafc; border: 1.5px solid #e2e8f0;
    color: #334155; text-decoration: none;
    transition: all .2s; font-size: .85rem; font-weight: 500;
}
.quick-btn-s:hover {
    background: #eff6ff; border-color: #bfdbfe;
    color: #1d4ed8; transform: translateX(4px);
    text-decoration: none;
}
.quick-btn-s .qi {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.deadline-item {
    padding: .65rem .75rem; border-radius: 8px;
    border-left: 4px solid;
    transition: background .15s;
}
.deadline-item:hover { background: #f8fafc; }
</style>
@endpush

@section('content')

@php
    // Gunakan $siswaProfile dari controller (sudah di-pass via compact)
    // Tidak perlu query ulang
@endphp

{{-- ── Welcome Banner ─────────────────────────────────── --}}
<div class="hero-siswa p-4 mb-4">
    <div class="row align-items-center g-3">
        <div class="col-md-7">
            <div class="d-flex align-items-center gap-3 mb-2">
                @if(auth()->user()->photo)
                    <img src="{{ asset('storage/'.auth()->user()->photo) }}"
                         class="rounded-circle border border-3 border-white border-opacity-50 flex-shrink-0"
                         style="width:52px;height:52px;object-fit:cover;" alt="">
                @elseif($siswaProfile?->foto)
                    <img src="{{ asset('storage/'.$siswaProfile->foto) }}"
                         class="rounded-circle border border-3 border-white border-opacity-50 flex-shrink-0"
                         style="width:52px;height:52px;object-fit:cover;" alt="">
                @else
                    <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center
                                justify-content-center fw-bold text-white flex-shrink-0"
                         style="width:52px;height:52px;font-size:1.4rem;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div class="text-white">
                    <h4 class="fw-bold mb-0 lh-1">Halo, {{ auth()->user()->name }}! 👋</h4>
                    <small class="opacity-75">{{ now()->translatedFormat('l, d F Y') }}
                        @if($siswaProfile?->kelas?->name) · Kelas {{ $siswaProfile->kelas->name }} @endif
                    </small>
                </div>
            </div>
            <p class="text-white opacity-75 small mb-3">
                Terus semangat belajar dan raih prestasi terbaik kamu! 🎓
            </p>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('siswa.materials.index') }}" class="btn btn-light btn-sm fw-semibold">
                    <i class="fas fa-book me-1"></i>Materi
                </a>
                <a href="{{ route('siswa.assignments.index') }}" class="btn btn-light btn-sm fw-semibold">
                    <i class="fas fa-tasks me-1"></i>Tugas
                    @php
                        $pendingCount = $stats['pending_assignments'] ?? 0;
                    @endphp
                    @if($pendingCount > 0)
                        <span class="badge bg-danger ms-1" style="font-size:.65rem;vertical-align:middle;">{{ $pendingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('siswa.absensi.index') }}" class="btn btn-light btn-sm fw-semibold">
                    <i class="fas fa-calendar-check me-1"></i>Absensi
                </a>
            </div>
        </div>
        <div class="col-md-5 d-none d-md-block">
            <div class="row g-2">
                @foreach([
                    ['fa-book',          $stats['total_materials']      ?? 0, 'Materi'],
                    ['fa-tasks',         $stats['completed_assignments'] ?? 0, 'Tugas Selesai'],
                    ['fa-flask',         $stats['completed_practicals']  ?? 0, 'Praktikum'],
                    ['fa-calendar-check',$stats['attendance_percentage'] ?? 0, 'Kehadiran %'],
                ] as [$ic, $val, $lbl])
                <div class="col-6">
                    <div class="text-center py-2 rounded-3"
                         style="background:rgba(255,255,255,.15);backdrop-filter:blur(4px);">
                        <i class="fas {{ $ic }} text-white fa-sm mb-1 d-block" style="opacity:.8;"></i>
                        <div class="fw-bold text-white lh-1" style="font-size:1.2rem;">{{ $val }}</div>
                        <div class="text-white mt-1" style="font-size:.68rem;opacity:.8;">{{ $lbl }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ── Stats Cards ─────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @foreach([
        ['primary',  'fa-book',          $stats['total_materials']       ?? 0, 'Materi',         route('siswa.materials.index'),                              'Tersedia'],
        ['success',  'fa-tasks',         $stats['completed_assignments'] ?? 0, 'Tugas Selesai',  route('siswa.assignments.index', ['status'=>'submitted']),    'Dikumpulkan'],
        ['info',     'fa-flask',         $stats['completed_practicals']  ?? 0, 'Praktikum',      route('siswa.praktikum.index'),                               'Selesai'],
        ['warning',  'fa-chart-bar',     $stats['average_score']         ?? 0, 'Nilai Rata-rata',route('siswa.nilai.index'),                                   '/100'],
        ['danger',   'fa-calendar-check',$stats['attendance_percentage'] ?? 0, 'Kehadiran',      route('siswa.absensi.index'),                                 '%'],
        ['secondary','fa-clipboard',     $stats['pending_assignments']   ?? 0, 'Tugas Pending',  route('siswa.assignments.index', ['status'=>'pending']),      'Belum dikerjakan'],
    ] as [$color, $icon, $val, $label, $url, $sub])
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card stat-card-s shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="rounded-2 bg-{{ $color }} bg-opacity-10 p-2">
                        <i class="fas {{ $icon }} text-{{ $color }} fa-sm"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ number_format($val) }}</div>
                <div class="small fw-semibold text-muted">{{ $label }}</div>
                <div class="mt-1 pt-1 border-top">
                    <a href="{{ $url }}" class="text-{{ $color }} text-decoration-none" style="font-size:.7rem;">
                        {{ $sub }} →
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Quick Actions + Deadline ────────────────────────── --}}
<div class="row g-4 mb-4">

    {{-- Quick Actions --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-bolt me-2 text-warning"></i>Aksi Cepat</h6>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                @foreach([
                    [route('siswa.materials.index'),   'fa-book-open',      'bg-primary bg-opacity-10',  'text-primary',  'Materi Pelajaran',  'Baca materi dari guru'],
                    [route('siswa.assignments.index'), 'fa-tasks',          'bg-success bg-opacity-10',  'text-success',  'Kerjakan Tugas',    'Lihat & kumpulkan tugas'],
                    [route('siswa.praktikum.index'),   'fa-flask',          'bg-info bg-opacity-10',     'text-info',     'Praktikum',         'Ikuti sesi praktikum'],
                    [route('siswa.nilai.index'),       'fa-chart-bar',      'bg-warning bg-opacity-10',  'text-warning',  'Lihat Nilai',       'Cek nilai & rekap akademik'],
                    [route('siswa.absensi.index'),     'fa-calendar-check', 'bg-danger bg-opacity-10',   'text-danger',   'Absensi',           'Rekap kehadiran saya'],
                    [route('siswa.profile.edit'),      'fa-user-circle',    'bg-secondary bg-opacity-10','text-secondary','Profil Saya',       'Edit data & foto profil'],
                ] as [$url, $icon, $ibg, $itxt, $title, $sub])
                <a href="{{ $url }}" class="quick-btn-s">
                    <div class="qi {{ $ibg }}">
                        <i class="fas {{ $icon }} {{ $itxt }} fa-sm"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold lh-1 text-dark">{{ $title }}</div>
                        <div class="text-muted" style="font-size:.72rem;">{{ $sub }}</div>
                    </div>
                    <i class="fas fa-chevron-right text-muted" style="font-size:.6rem;"></i>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Deadline Mendatang --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <div>
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-calendar-alt me-2 text-danger"></i>Deadline Mendatang</h6>
                    <small class="text-muted">Tugas & praktikum yang harus segera dikerjakan</small>
                </div>
                <a href="{{ route('siswa.assignments.index') }}" class="btn btn-outline-danger btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body">
                @forelse($upcomingDeadlines ?? [] as $deadline)
                @php
                    $dl     = $deadline->due_date ?? null;
                    $days   = $dl ? now()->diffInDays($dl, false) : null;
                    $isPast = $dl ? $dl->isPast() : false;
                    $clr    = $isPast ? '#dc2626' : ($days !== null && $days <= 2 ? '#d97706' : '#3b82f6');
                    $badge  = $isPast ? 'danger' : ($days !== null && $days <= 2 ? 'warning' : 'primary');
                @endphp
                <div class="deadline-item mb-2" style="border-left-color:{{ $clr }};">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1 me-3">
                            <div class="fw-semibold text-dark" style="font-size:.88rem;">
                                {{ $deadline->title ?? '—' }}
                            </div>
                            <div class="text-muted small">
                                @if($deadline->subject?->name ?? null)
                                    <i class="fas fa-book me-1"></i>{{ $deadline->subject->name }}
                                @endif
                                @if($deadline->type ?? null)
                                    · <span class="badge bg-{{ ($deadline->type ?? '') === 'assignment' ? 'success' : 'info' }} bg-opacity-10 text-{{ ($deadline->type ?? '') === 'assignment' ? 'success' : 'info' }}" style="font-size:.65rem;">
                                        {{ ($deadline->type ?? '') === 'assignment' ? 'Tugas' : 'Praktikum' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <span class="badge bg-{{ $badge }}">
                                {{ $isPast ? 'Lewat' : ($dl instanceof \Carbon\Carbon ? $dl->diffForHumans() : '—') }}
                            </span>
                            <div class="text-muted mt-1" style="font-size:.7rem;">
                                {{ $dl instanceof \Carbon\Carbon ? $dl->format('d/m/Y H:i') : ($dl ? \Carbon\Carbon::parse($dl)->format('d/m/Y H:i') : '') }}
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-calendar-check fa-3x text-success opacity-25 mb-3 d-block"></i>
                    <h6 class="text-muted">Tidak ada deadline mendatang</h6>
                    <small>Semua tugas masih dalam jadwal yang aman.</small>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ── Materi Terbaru + Jadwal Ujian ──────────────────── --}}
<div class="row g-4 mb-4">

    {{-- Materi Terbaru --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-book me-2 text-primary"></i>Materi Terbaru</h6>
                <a href="{{ route('siswa.materials.index') }}" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @forelse($recentMaterials ?? [] as $material)
                <div class="d-flex align-items-start gap-3 px-4 py-3 border-bottom small">
                    <div class="rounded-2 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:36px;height:36px;">
                        <i class="fas fa-file-alt text-primary fa-sm"></i>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-semibold text-dark text-truncate">{{ $material->title ?? '—' }}</div>
                        <div class="text-muted" style="font-size:.75rem;">
                            {{ $material->subject?->name ?? '—' }} ·
                            {{ optional($material->created_at)->diffForHumans() ?? '' }}
                        </div>
                    </div>
                    <a href="{{ route('siswa.materials.show', $material->id) }}"
                       class="btn btn-outline-primary btn-sm flex-shrink-0" style="font-size:.72rem;padding:.2rem .5rem;">
                        Buka
                    </a>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-book fa-2x opacity-25 mb-2 d-block"></i>
                    <small>Belum ada materi baru.</small>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Jadwal Ujian --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-calendar-alt me-2 text-info"></i>Jadwal Ujian</h6>
            </div>
            <div class="card-body p-0">
                @php
                    try {
                        $upcomingExams = \App\Models\ExamSchedule::with(['subject','kelas'])
                            ->where('is_published', true)
                            ->where('start_time', '>', now())
                            ->orderBy('start_time')
                            ->take(5)
                            ->get();
                    } catch(\Throwable $e) {
                        $upcomingExams = collect();
                    }
                @endphp
                @forelse($upcomingExams as $exam)
                <div class="d-flex align-items-start gap-3 px-4 py-3 border-bottom small">
                    @php $tc = ['uts'=>'info','uas'=>'danger','quiz'=>'warning','praktikum'=>'success'][$exam->exam_type ?? ''] ?? 'secondary'; @endphp
                    <div class="rounded-2 bg-{{ $tc }} bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:36px;height:36px;">
                        <i class="fas fa-file-alt text-{{ $tc }} fa-sm"></i>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-semibold text-dark text-truncate">{{ $exam->title ?? '—' }}</div>
                        <div class="text-muted" style="font-size:.75rem;">
                            <span class="badge bg-{{ $tc }} bg-opacity-10 text-{{ $tc }}">{{ strtoupper($exam->exam_type ?? '') }}</span>
                            · {{ $exam->start_time->format('d M Y, H:i') }}
                        </div>
                    </div>
                    <span class="badge bg-{{ $tc }} flex-shrink-0">
                        {{ $exam->start_time->diffForHumans() }}
                    </span>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-calendar-times fa-2x opacity-25 mb-2 d-block"></i>
                    <small>Tidak ada jadwal ujian mendatang.</small>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ── Info Akademik ───────────────────────────────────── --}}
@if($siswaProfile)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-id-card me-2 text-success"></i>Informasi Akademik Saya</h6>
    </div>
    <div class="card-body">
        <div class="row g-3 small">
            @foreach([
                ['NIS',          $siswaProfile->nis          ?? '—'],
                ['NISN',         $siswaProfile->nisn         ?? '—'],
                ['Kelas',        $siswaProfile->kelas?->name ?? '—'],
                ['Jurusan',      $siswaProfile->major        ?? '—'],
                ['Tahun Ajaran', $siswaProfile->tahun_ajaran ?? '—'],
                ['Status',       ucfirst($siswaProfile->status ?? 'aktif')],
            ] as [$label, $val])
            <div class="col-6 col-md-4 col-lg-2">
                <div class="text-center p-2 rounded-2 bg-light">
                    <div class="text-muted" style="font-size:.7rem;">{{ $label }}</div>
                    <div class="fw-semibold text-dark">{{ $val }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Animate stat counters
    document.querySelectorAll('.h3.fw-bold').forEach(function (el) {
        const target = parseInt(el.textContent.replace(/[^0-9]/g, '')) || 0;
        if (target === 0) return;
        let cur = 0; const step = Math.ceil(target / 25);
        const t = setInterval(function () {
            cur = Math.min(cur + step, target);
            el.textContent = cur.toLocaleString('id-ID');
            if (cur >= target) clearInterval(t);
        }, 30);
    });
});
</script>
@endpush
