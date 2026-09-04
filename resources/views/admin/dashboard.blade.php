@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan sistem LMS SMK Kesehatan Trimurti Husada')

@push('css')
<style>
/* ── Dashboard Specific Styles ──────────────────────────────── */
.welcome-card {
    background: linear-gradient(135deg, #1e40af 0%, #4f46e5 50%, #7c3aed 100%);
    border-radius: 16px;
    overflow: hidden;
    position: relative;
}
.welcome-card::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,.07);
    border-radius: 50%;
    pointer-events: none; /* tidak menghalangi klik */
    z-index: 0;
}
.welcome-card::after {
    content: '';
    position: absolute;
    bottom: -80px; right: 80px;
    width: 160px; height: 160px;
    background: rgba(255,255,255,.05);
    border-radius: 50%;
    pointer-events: none; /* tidak menghalangi klik */
    z-index: 0;
}
/* konten welcome-card selalu di atas pseudo-elements */
.welcome-card > * { position: relative; z-index: 1; }

/* ── Hero banner buttons — konsisten semua role ───────────────── */
.welcome-card .btn-light,
.welcome-card .btn-outline-light {
    background: rgba(255,255,255,.92) !important;
    color: #1e40af !important;
    border: none !important;
    font-weight: 600 !important;
    font-size: .82rem !important;
    padding: .38rem .9rem !important;
    border-radius: 8px !important;
    transition: background .15s, transform .15s, box-shadow .15s !important;
    box-shadow: 0 2px 6px rgba(0,0,0,.12);
}
.welcome-card .btn-light:hover,
.welcome-card .btn-outline-light:hover {
    background: #fff !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,.18);
}

.stat-card {
    border-radius: 14px;
    border: none;
    transition: transform .2s ease, box-shadow .2s ease;
}
.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0,0,0,.12) !important;
}
.stat-icon {
    width: 52px; height: 52px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.quick-btn {
    display: flex; align-items: center; gap: .65rem;
    padding: .6rem .9rem;
    border-radius: 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #334155;
    text-decoration: none;
    font-size: .82rem;
    font-weight: 500;
    transition: all .2s;
}
.quick-btn:hover {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #1d4ed8;
    transform: translateX(3px);
}
.quick-btn .qi {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: .82rem; flex-shrink: 0;
}
.activity-dot {
    width: 8px; height: 8px;
    border-radius: 50%; flex-shrink: 0;
}
.progress-label {
    display: flex; justify-content: space-between;
    font-size: .75rem; margin-bottom: .25rem;
}
.donut-ring {
    transform: rotate(-90deg);
    transform-origin: 50% 50%;
}
</style>
@endpush

@section('content')

{{-- ══════════════════════════════════════════════
     WELCOME BANNER
═══════════════════════════════════════════════ --}}
<div class="welcome-card p-4 mb-4">
    <div class="row align-items-center g-3">
        <div class="col-md-7">
            <div class="d-flex align-items-center gap-3 mb-2">
                @php $heroPhoto = auth()->user()->photo_url; @endphp
                <img src="{{ $heroPhoto }}"
                     class="rounded-circle border border-3 border-white border-opacity-40 flex-shrink-0"
                     style="width:50px;height:50px;object-fit:cover;"
                     alt="{{ auth()->user()->name }}"
                     onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
                <div class="rounded-circle bg-white bg-opacity-20 d-none align-items-center justify-content-center flex-shrink-0"
                     style="width:50px;height:50px;">
                    <i class="fas fa-user-shield text-white fa-lg"></i>
                </div>
                <div class="text-white">
                    <h4 class="fw-bold mb-0 lh-1">Halo, {{ Auth::user()->name }}! 👋</h4>
                    <small class="opacity-75">{{ now()->translatedFormat('l, d F Y — H:i') }} WIB</small>
                </div>
            </div>
            <p class="text-white opacity-85 mb-3 small mb-0">
                Selamat datang di panel administrasi. Pantau aktivitas, kelola pengguna, dan monitor perkembangan pembelajaran.
            </p>
            <div class="d-flex gap-2 flex-wrap mt-3">
                <a href="{{ route('admin.users.create.admin') }}" class="btn btn-light btn-sm fw-semibold">
                    <i class="fas fa-user-plus me-1"></i>Tambah Admin
                </a>
                <a href="{{ route('admin.users.create.guru') }}" class="btn btn-light btn-sm fw-semibold">
                    <i class="fas fa-chalkboard-teacher me-1"></i>Tambah Guru
                </a>
                <a href="{{ route('admin.users.create.siswa') }}" class="btn btn-light btn-sm fw-semibold">
                    <i class="fas fa-user-graduate me-1"></i>Tambah Siswa
                </a>
            </div>
        </div>
        <div class="col-md-5 d-none d-md-block">
            <div class="row g-2">
                @foreach([
                    ['fa-users',          $stats['total_users']     ?? 0, 'Total Pengguna', 'rgba(255,255,255,.18)'],
                    ['fa-user-graduate',  $stats['total_siswa']     ?? 0, 'Siswa',           'rgba(255,255,255,.18)'],
                    ['fa-chalkboard-teacher', $stats['total_guru']  ?? 0, 'Guru',            'rgba(255,255,255,.18)'],
                    ['fa-book',           $stats['total_materials'] ?? 0, 'Materi',          'rgba(255,255,255,.18)'],
                ] as [$ic, $val, $lbl, $bg])
                <div class="col-6">
                    <div class="rounded-3 p-2 text-white text-center" style="background:{{ $bg }};">
                        <i class="fas {{ $ic }} fa-sm mb-1 d-block opacity-75"></i>
                        <div class="fw-bold fs-5 lh-1">{{ number_format($val) }}</div>
                        <div style="font-size:.7rem;opacity:.8;">{{ $lbl }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     STATS CARDS
═══════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Total Pengguna --}}
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon bg-primary bg-opacity-10">
                        <i class="fas fa-users text-primary"></i>
                    </div>
                    @if(($stats['new_users_today'] ?? 0) > 0)
                    <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.65rem;">
                        +{{ $stats['new_users_today'] }} hari ini
                    </span>
                    @endif
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ number_format($stats['total_users'] ?? 0) }}</div>
                <div class="small fw-semibold text-muted">Total Pengguna</div>
                <div class="mt-2 pt-2 border-top">
                    <div class="d-flex justify-content-between" style="font-size:.7rem;color:#94a3b8;">
                        <span>Aktif: {{ $stats['active_users'] ?? '—' }}</span>
                        <a href="{{ route('admin.users.index') }}" class="text-primary text-decoration-none">Lihat →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Siswa --}}
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon bg-success bg-opacity-10">
                        <i class="fas fa-user-graduate text-success"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ number_format($stats['total_siswa'] ?? 0) }}</div>
                <div class="small fw-semibold text-muted">Total Siswa</div>
                <div class="mt-2 pt-2 border-top">
                    <div class="d-flex justify-content-between" style="font-size:.7rem;color:#94a3b8;">
                        <span>Aktif belajar</span>
                        <a href="{{ route('admin.users.siswa') }}" class="text-success text-decoration-none">Lihat →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Guru --}}
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon bg-warning bg-opacity-10">
                        <i class="fas fa-chalkboard-teacher text-warning"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ number_format($stats['total_guru'] ?? 0) }}</div>
                <div class="small fw-semibold text-muted">Total Guru</div>
                <div class="mt-2 pt-2 border-top">
                    <div class="d-flex justify-content-between" style="font-size:.7rem;color:#94a3b8;">
                        <span>Pengajar aktif</span>
                        <a href="{{ route('admin.users.guru') }}" class="text-warning text-decoration-none">Lihat →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Materi --}}
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon bg-info bg-opacity-10">
                        <i class="fas fa-book text-info"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ number_format($stats['total_materials'] ?? 0) }}</div>
                <div class="small fw-semibold text-muted">Materi</div>
                <div class="mt-2 pt-2 border-top">
                    <div class="d-flex justify-content-between" style="font-size:.7rem;color:#94a3b8;">
                        <span>Tersedia</span>
                        <a href="{{ route('admin.materials.index') }}" class="text-info text-decoration-none">Lihat →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tugas --}}
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon bg-danger bg-opacity-10">
                        <i class="fas fa-tasks text-danger"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ number_format($stats['total_assignments'] ?? 0) }}</div>
                <div class="small fw-semibold text-muted">Tugas</div>
                <div class="mt-2 pt-2 border-top">
                    <div class="d-flex justify-content-between" style="font-size:.7rem;color:#94a3b8;">
                        <span>Dibuat guru</span>
                        <a href="{{ route('admin.assignments.index') }}" class="text-danger text-decoration-none">Lihat →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Praktikum --}}
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon bg-secondary bg-opacity-10">
                        <i class="fas fa-flask text-secondary"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">{{ number_format($stats['total_practicals'] ?? 0) }}</div>
                <div class="small fw-semibold text-muted">Praktikum</div>
                <div class="mt-2 pt-2 border-top">
                    <div class="d-flex justify-content-between" style="font-size:.7rem;color:#94a3b8;">
                        <span>Terdaftar</span>
                        <a href="{{ route('admin.practicals.index') }}" class="text-secondary text-decoration-none">Lihat →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /row stats --}}

{{-- ══════════════════════════════════════════════
     CHARTS ROW
═══════════════════════════════════════════════ --}}
<div class="row g-4 mb-4">

    {{-- Line Chart --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <div>
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-chart-line me-2 text-primary"></i>Statistik 6 Bulan Terakhir
                    </h6>
                    <small class="text-muted">Pengguna baru, materi, dan tugas</small>
                </div>
                <div class="d-flex gap-3 small">
                    <span class="d-flex align-items-center gap-1 text-muted">
                        <span class="rounded-circle bg-primary d-inline-block" style="width:8px;height:8px;"></span>User
                    </span>
                    <span class="d-flex align-items-center gap-1 text-muted">
                        <span class="rounded-circle bg-success d-inline-block" style="width:8px;height:8px;"></span>Materi
                    </span>
                    <span class="d-flex align-items-center gap-1 text-muted">
                        <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#f59e0b;"></span>Tugas
                    </span>
                </div>
            </div>
            <div class="card-body" style="min-height:240px;">
                <canvas id="monthlyChart" style="max-height:220px;"></canvas>
            </div>
        </div>
    </div>

    {{-- Donut + Attendance --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-chart-pie me-2 text-indigo"></i>Distribusi Pengguna
                </h6>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center gap-2">
                <canvas id="userDistributionChart" style="max-height:160px;max-width:160px;"></canvas>
                <div class="d-flex gap-3 small mt-1">
                    @foreach([
                        ['bg-primary',  'Admin', $stats['total_admin'] ?? 0],
                        ['bg-info',     'Guru',  $stats['total_guru']  ?? 0],
                        ['bg-success',  'Siswa', $stats['total_siswa'] ?? 0],
                    ] as [$bg, $lbl, $cnt])
                    <div class="text-center">
                        <div class="d-flex align-items-center gap-1 text-muted justify-content-center">
                            <span class="rounded-circle {{ $bg }} d-inline-block" style="width:8px;height:8px;"></span>
                            <span>{{ $lbl }}</span>
                        </div>
                        <div class="fw-bold text-dark">{{ $cnt }}</div>
                    </div>
                    @endforeach
                </div>

                {{-- Divider --}}
                <hr class="w-100 my-2">

                {{-- Attendance mini --}}
                <div class="w-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="fw-semibold text-muted"><i class="fas fa-calendar-check me-1 text-success"></i>Kehadiran Bulan Ini</small>
                        <span class="badge bg-success bg-opacity-10 text-success fw-semibold">
                            {{ ($attendanceData['attendance_rate'] ?? 0) }}%
                        </span>
                    </div>
                    @php $rate = $attendanceData['attendance_rate'] ?? 0; @endphp
                    <div class="progress mb-2" style="height:6px;border-radius:4px;">
                        <div class="progress-bar bg-{{ $rate >= 80 ? 'success' : ($rate >= 60 ? 'warning' : 'danger') }}"
                             style="width:{{ $rate }}%"></div>
                    </div>
                    <div class="row g-1 text-center" style="font-size:.72rem;">
                        @foreach([
                            ['success','Hadir', $attendanceData['hadir'] ?? 0],
                            ['info',   'Izin',  $attendanceData['izin']  ?? 0],
                            ['warning','Sakit', $attendanceData['sakit'] ?? 0],
                            ['danger', 'Alpa',  $attendanceData['alpha'] ?? 0],
                        ] as [$c,$l,$v])
                        <div class="col-3">
                            <div class="rounded-2 py-1 bg-{{ $c }} bg-opacity-10">
                                <div class="fw-bold text-{{ $c }}">{{ $v }}</div>
                                <div class="text-muted" style="font-size:.65rem;">{{ $l }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>{{-- /row charts --}}

{{-- ══════════════════════════════════════════════
     QUICK ACTIONS + RECENT USERS
═══════════════════════════════════════════════ --}}
<div class="row g-4 mb-4">

    {{-- Quick Actions --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-bolt me-2 text-warning"></i>Aksi Cepat</h6>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                @foreach([
                    [route('admin.users.create.admin'),         'fa-user-shield',          'bg-danger bg-opacity-10',   'text-danger',   'Tambah Admin',        'Buat akun administrator'],
                    [route('admin.users.create.guru'),     'fa-chalkboard-teacher',   'bg-success bg-opacity-10',  'text-success',  'Tambah Guru',         'Daftarkan guru baru'],
                    [route('admin.users.create.siswa'),    'fa-user-graduate',        'bg-warning bg-opacity-10',  'text-warning',  'Tambah Siswa',        'Daftarkan siswa baru'],
                    [route('admin.kelas.create'),          'fa-door-open',            'bg-info bg-opacity-10',     'text-info',     'Buat Kelas',          'Tambah kelas baru'],
                    [route('admin.mata-pelajaran.create'), 'fa-book-open',            'bg-primary bg-opacity-10',  'text-primary',  'Mata Pelajaran',      'Tambah mapel baru'],
                    [route('admin.exam-schedules.create'), 'fa-calendar-alt',         'bg-secondary bg-opacity-10','text-secondary','Jadwal Ujian',        'Buat jadwal ujian'],
                ] as [$url, $icon, $ibg, $itxt, $title, $sub])
                <a href="{{ $url }}" class="quick-btn">
                    <div class="qi {{ $ibg }}">
                        <i class="fas {{ $icon }} {{ $itxt }}"></i>
                    </div>
                    <div>
                        <div class="fw-semibold lh-1 text-dark">{{ $title }}</div>
                        <div class="text-muted" style="font-size:.72rem;">{{ $sub }}</div>
                    </div>
                    <i class="fas fa-chevron-right ms-auto text-muted" style="font-size:.65rem;"></i>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Recent Users --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-user-clock me-2 text-primary"></i>Pengguna Terbaru</h6>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @php
                    try {
                        $recentUsers = \App\Models\UserCentral::latest()->take(6)->get();
                    } catch(\Throwable $e) {
                        $recentUsers = collect();
                    }
                @endphp
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Pengguna</th>
                                <th class="text-center">Role</th>
                                <th class="text-center">Status</th>
                                <th>Bergabung</th>
                                <th class="pe-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUsers as $usr)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $usr->photo_url ?? asset('images/default-avatar.png') }}"
                                             class="rounded-circle" width="34" height="34"
                                             style="object-fit:cover;border:2px solid #e2e8f0;" alt="">
                                        <div>
                                            <div class="fw-semibold lh-1">{{ $usr->name }}</div>
                                            <div class="text-muted" style="font-size:.72rem;">{{ $usr->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @php
                                        $roleColors = ['admin'=>'danger','guru'=>'warning','siswa'=>'success'];
                                        $rc = $roleColors[$usr->role] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $rc }} bg-opacity-10 text-{{ $rc }} fw-semibold" style="font-size:.7rem;">
                                        {{ ucfirst($usr->role) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if(($usr->status ?? 'active') === 'active')
                                        <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.7rem;">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.7rem;">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-muted">
                                    <div>{{ optional($usr->created_at)->format('d M Y') }}</div>
                                    <div style="font-size:.7rem;">{{ optional($usr->created_at)->diffForHumans() }}</div>
                                </td>
                                <td class="pe-4 text-center">
                                    <a href="{{ route('admin.users.show', $usr->id) }}"
                                       class="btn btn-outline-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-users fa-2x opacity-25 mb-2 d-block"></i>
                                    Belum ada pengguna terdaftar.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>{{-- /row quick+users --}}

{{-- ══════════════════════════════════════════════
     AKTIVITAS TERBARU + JADWAL UJIAN
═══════════════════════════════════════════════ --}}
<div class="row g-4 mb-4">

    {{-- Aktivitas Terbaru --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-history me-2 text-secondary"></i>Aktivitas Terbaru</h6>
                <span class="badge bg-secondary bg-opacity-10 text-secondary">Live</span>
            </div>
            <div class="card-body p-0">
                @php $acts = is_array($recentActivities ?? null) ? $recentActivities : []; @endphp
                @if(count($acts) > 0)
                    <ul class="list-group list-group-flush">
                        @foreach(array_slice($acts, 0, 8) as $act)
                        @php
                            $desc    = is_array($act) ? ($act['description'] ?? '—') : ($act->description ?? '—');
                            $actTime = is_array($act)
                                ? optional(\Carbon\Carbon::parse($act['created_at'] ?? null))->diffForHumans()
                                : optional($act->created_at)->diffForHumans();
                        @endphp
                        <li class="list-group-item px-4 py-2 border-0 border-bottom small">
                            <div class="d-flex gap-2 align-items-start">
                                <div class="activity-dot bg-primary mt-1" style="margin-top:5px !important;"></div>
                                <div class="flex-grow-1">
                                    <div class="text-dark lh-sm">{{ $desc }}</div>
                                    <div class="text-muted" style="font-size:.7rem;">{{ $actTime }}</div>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-2x opacity-25 mb-2 d-block"></i>
                        <small>Belum ada aktivitas</small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Jadwal Ujian Mendatang --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-calendar-alt me-2 text-primary"></i>Jadwal Ujian Mendatang
                </h6>
                <a href="{{ route('admin.exam-schedules.index') }}" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @php
                    try {
                        $upcomingExams = \App\Models\ExamSchedule::with(['subject','kelas'])
                            ->published()->upcoming()
                            ->orderBy('start_time')->take(5)->get();
                    } catch(\Throwable $e) {
                        $upcomingExams = collect();
                    }
                @endphp
                @if($upcomingExams->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Ujian</th>
                                <th class="text-center">Tipe</th>
                                <th>Jadwal</th>
                                <th class="text-center pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingExams as $exam)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold lh-1">{{ $exam->title }}</div>
                                    <small class="text-muted">
                                        {{ $exam->subject?->name ?? $exam->subject?->nama ?? '—' }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    @php
                                        $tc = ['uts'=>'info','uas'=>'danger','quiz'=>'warning','praktikum'=>'success'][$exam->exam_type] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $tc }}">{{ strtoupper($exam->exam_type) }}</span>
                                </td>
                                <td>
                                    <div>{{ $exam->start_time->format('d M Y') }}</div>
                                    <small class="text-muted">{{ $exam->start_time->format('H:i') }} WIB</small>
                                </td>
                                <td class="text-center pe-4">
                                    <span class="badge bg-{{ $exam->status_color ?? 'secondary' }}">
                                        {{ $exam->status_label ?? ucfirst($exam->status ?? '—') }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-calendar-times fa-3x opacity-25 mb-3 d-block"></i>
                    <p class="mb-2">Tidak ada jadwal ujian mendatang</p>
                    <a href="{{ route('admin.exam-schedules.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Buat Jadwal
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>{{-- /row aktivitas+jadwal --}}

{{-- ══════════════════════════════════════════════
     SYSTEM OVERVIEW (kelas, jurusan, mapel)
═══════════════════════════════════════════════ --}}
<div class="row g-4">

    {{-- Ringkasan Akademik --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-school me-2 text-success"></i>Ringkasan Akademik</h6>
            </div>
            <div class="card-body">
                @php
                    try {
                        $totalKelas   = \App\Models\Kelas::count();
                        $totalJurusan = \App\Models\Jurusan::count();
                        $totalMapel   = \App\Models\Subject::count();
                        $activeKelas  = \App\Models\Kelas::aktif()->count();
                    } catch(\Throwable $e) {
                        $totalKelas = $totalJurusan = $totalMapel = $activeKelas = 0;
                    }
                @endphp

                @foreach([
                    ['Kelas Aktif',     $activeKelas,   $totalKelas,   'success', 'fa-door-open',        route('admin.kelas.index')],
                    ['Jurusan',         $totalJurusan,  $totalJurusan, 'info',    'fa-graduation-cap',   route('admin.jurusan.index')],
                    ['Mata Pelajaran',  $totalMapel,    $totalMapel,   'primary', 'fa-book-open',        route('admin.mata-pelajaran.index')],
                ] as [$label, $val, $total, $color, $icon, $url])
                @php $pct = $total > 0 ? round(($val/$total)*100) : 100; @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-2 bg-{{ $color }} bg-opacity-10 p-1" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas {{ $icon }} text-{{ $color }}" style="font-size:.75rem;"></i>
                            </div>
                            <a href="{{ $url }}" class="small fw-semibold text-dark text-decoration-none">{{ $label }}</a>
                        </div>
                        <span class="small fw-bold text-{{ $color }}">{{ $val }}</span>
                    </div>
                    <div class="progress" style="height:5px;border-radius:4px;">
                        <div class="progress-bar bg-{{ $color }}" style="width:{{ $pct }}%;transition:width .6s;"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Info Sistem --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2 text-info"></i>Informasi Sistem</h6>
            </div>
            <div class="card-body">
                @php
                    $sysInfo = [
                        ['Laravel Version',   app()->version(),                           'fa-laravel',     'danger'],
                        ['PHP Version',       phpversion(),                               'fa-code',        'primary'],
                        ['Environment',       app()->environment(),                       'fa-server',      'success'],
                        ['Timezone',          config('app.timezone'),                    'fa-clock',       'warning'],
                        ['Database',          config('database.default'),                'fa-database',    'info'],
                        ['Cache Driver',      config('cache.default'),                   'fa-memory',      'secondary'],
                    ];
                @endphp
                <div class="row g-2">
                    @foreach($sysInfo as [$label, $value, $icon, $color])
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 p-2 rounded-2 bg-light">
                            <div class="rounded-2 bg-{{ $color }} bg-opacity-10 p-1 flex-shrink-0" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;">
                                <i class="fab {{ $icon }} text-{{ $color }}" style="font-size:.75rem;"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:.68rem;">{{ $label }}</div>
                                <div class="fw-semibold" style="font-size:.78rem;">{{ $value }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <hr class="my-3">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="fas fa-sync me-1"></i>
                        Terakhir diperbarui: {{ now()->format('d M Y H:i') }}
                    </small>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-sync me-1"></i>Refresh
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /row overview --}}

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Line chart ──────────────────────────────────────────────
    const mCtx = document.getElementById('monthlyChart');
    if (mCtx) {
        new Chart(mCtx, {
            type: 'line',
            data: {
                labels: @json($chartData['months'] ?? []),
                datasets: @json($chartData['datasets'] ?? [])
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: { backgroundColor: '#1e293b', titleColor: '#f1f5f9', bodyColor: '#94a3b8' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,.04)' },
                        ticks: { stepSize: 1, color: '#94a3b8', font: { size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { size: 11 } }
                    }
                },
                elements: { point: { radius: 3, hoverRadius: 6 }, line: { borderWidth: 2 } }
            }
        });
    }

    // ── Donut chart ─────────────────────────────────────────────
    const dCtx = document.getElementById('userDistributionChart');
    if (dCtx) {
        new Chart(dCtx, {
            type: 'doughnut',
            data: {
                labels: @json($userDistribution['labels'] ?? ['Admin','Guru','Siswa']),
                datasets: [{
                    data: @json($userDistribution['data'] ?? [0,0,0]),
                    backgroundColor: ['rgba(59,130,246,.8)','rgba(6,182,212,.8)','rgba(34,197,94,.8)'],
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        callbacks: {
                            label: function(c) {
                                const total = c.dataset.data.reduce((a,b)=>a+b,0);
                                const pct = total ? ((c.parsed/total)*100).toFixed(1) : 0;
                                return ' ' + c.label + ': ' + c.parsed + ' (' + pct + '%)';
                            }
                        }
                    }
                },
                cutout: '68%'
            }
        });
    }

    // ── Animate progress bars ───────────────────────────────────
    document.querySelectorAll('.progress-bar').forEach(function(bar) {
        const target = bar.style.width;
        bar.style.width = '0%';
        setTimeout(function() { bar.style.width = target; }, 300);
    });

    // ── Stat card counter animation ─────────────────────────────
    document.querySelectorAll('.h3.fw-bold').forEach(function(el) {
        const target = parseInt(el.textContent.replace(/,/g,'')) || 0;
        if (target === 0) return;
        let current = 0;
        const step  = Math.ceil(target / 30);
        const timer = setInterval(function() {
            current = Math.min(current + step, target);
            el.textContent = current.toLocaleString('id-ID');
            if (current >= target) clearInterval(timer);
        }, 30);
    });

});
</script>
@endpush
