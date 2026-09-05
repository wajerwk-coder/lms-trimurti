@php
    // Baca SELALU fresh dari DB — bypass semua cache sesi
    $user         = \App\Models\UserCentral::find(Auth::id());
    // Sync Auth::user() dengan data fresh agar header juga dapat data terbaru
    if ($user) { Auth::setUser($user); }
    $pendingGrade = isset($stats['pending_grading']) ? (int)$stats['pending_grading'] : 0;
    $guruProfile  = $user->guruProfile;
    // Foto: fresh() dari DB agar selalu terbaca yang terbaru
    $freshUser         = $user->fresh() ?? $user;
    $guruPhotoSrc      = $freshUser->photo_url;
    $guruPhotoFallback = 'https://ui-avatars.com/api/?name='.urlencode($user->name ?? 'G').'&background=0f766e&color=fff&size=64';

    // Cek active state untuk grup laporan
    $laporanActive = request()->routeIs('guru.laporan.*') || request()->routeIs('guru.reports.*');
@endphp

<aside class="sidebar" id="sidebar" data-role="guru">

    {{-- BRAND --}}
    <div class="sidebar-brand">
        <a href="{{ route('guru.dashboard') }}" class="brand-link">
            <div class="brand-icon"
                 style="background:linear-gradient(135deg,#0f766e,#0891b2);box-shadow:0 4px 12px rgba(8,145,178,.4);">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="brand-text">
                <span class="brand-name">LMS Trimurti</span>
                <span class="brand-role">Panel Guru</span>
            </div>
        </a>
    </div>

    {{-- USER CARD --}}
    <div class="sidebar-user">
        <img src="{{ $guruPhotoSrc }}"
             alt="Avatar"
             onerror="console.error('Foto gagal dimuat:',this.src);this.onerror=null;this.src='{{ $guruPhotoFallback }}'">
        <div class="sidebar-user-info">
            <span class="sidebar-user-name">{{ Str::limit($user->name ?? 'Guru', 18) }}</span>
            <span class="sidebar-user-role">
                {{ $guruProfile?->mata_pelajaran ? Str::limit($guruProfile->mata_pelajaran, 22) : 'Guru' }}
            </span>
        </div>
    </div>

    {{-- NAVIGATION --}}
    <nav class="sidebar-nav">

        {{-- Utama --}}
        <div class="nav-section">
            <span class="nav-section-label">Utama</span>
            <a href="{{ route('guru.dashboard') }}"
               class="nav-item {{ request()->routeIs('guru.dashboard') ? 'active' : '' }}"
               data-tooltip="Dashboard">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>

        {{-- Pengajaran --}}
        <div class="nav-section">
            <span class="nav-section-label">Pengajaran</span>

            <a href="{{ route('guru.materials.index') }}"
               class="nav-item {{ request()->routeIs('guru.materials.*') ? 'active' : '' }}"
               data-tooltip="Materi">
                <i class="fas fa-file-alt"></i>
                <span>Materi</span>
            </a>

            <a href="{{ route('guru.assignments.index') }}"
               class="nav-item {{ request()->routeIs('guru.assignments.*') ? 'active' : '' }}"
               data-tooltip="Tugas">
                <i class="fas fa-tasks"></i>
                <span>Tugas</span>
            </a>

            <a href="{{ route('guru.praktikum.index') }}"
               class="nav-item {{ request()->routeIs('guru.praktikum.*') || request()->routeIs('guru.practicals.*') ? 'active' : '' }}"
               data-tooltip="Praktikum">
                <i class="fas fa-flask"></i>
                <span>Praktikum</span>
            </a>

            <a href="{{ route('guru.absensi.index') }}"
               class="nav-item {{ request()->routeIs('guru.absensi.*') || request()->routeIs('guru.attendance.*') ? 'active' : '' }}"
               data-tooltip="Absensi">
                <i class="fas fa-calendar-check"></i>
                <span>Absensi</span>
            </a>
        </div>

        {{-- Penilaian --}}
        <div class="nav-section">
            <span class="nav-section-label">Penilaian</span>

            <a href="{{ route('guru.submissions.index') }}"
               class="nav-item {{ request()->routeIs('guru.submissions.*') ? 'active' : '' }}"
               data-tooltip="Pengumpulan Tugas">
                <i class="fas fa-inbox"></i>
                <span>Pengumpulan Tugas</span>
            </a>

            <a href="{{ route('guru.penilaian.index') }}"
               class="nav-item {{ request()->routeIs('guru.penilaian.*') && !request()->routeIs('guru.penilaian-praktik.*') ? 'active' : '' }}"
               data-tooltip="Penilaian">
                <i class="fas fa-star"></i>
                <span>Penilaian</span>
                @if($pendingGrade > 0)
                    <span class="nav-badge">{{ $pendingGrade > 99 ? '99+' : $pendingGrade }}</span>
                @endif
            </a>
        </div>

        {{-- Laporan (accordion) --}}
        <div class="nav-section">
            <span class="nav-section-label">Laporan</span>

            <div class="nav-group {{ $laporanActive ? 'open' : '' }}" id="grp-laporan">
                <button class="nav-item nav-group-toggle {{ $laporanActive ? 'active' : '' }}"
                        data-tooltip="Laporan"
                        aria-expanded="{{ $laporanActive ? 'true' : 'false' }}">
                    <i class="fas fa-chart-bar"></i>
                    <span>Laporan</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </button>
                <div class="nav-sub">
                    <a href="{{ route('guru.laporan.index') }}"
                       class="nav-sub-item {{ request()->routeIs('guru.laporan.index') || request()->routeIs('guru.reports.index') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>Ringkasan</span>
                    </a>
                    <a href="{{ route('guru.laporan.absensi') }}"
                       class="nav-sub-item {{ request()->routeIs('guru.laporan.absensi*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-check"></i>
                        <span>Laporan Absensi</span>
                    </a>
                    <a href="{{ route('guru.laporan.nilai') }}"
                       class="nav-sub-item {{ request()->routeIs('guru.laporan.nilai*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <span>Laporan Nilai</span>
                    </a>
                    <a href="{{ route('guru.laporan.tugas') }}"
                       class="nav-sub-item {{ request()->routeIs('guru.laporan.tugas*') ? 'active' : '' }}">
                        <i class="fas fa-tasks"></i>
                        <span>Laporan Tugas</span>
                    </a>
                    <a href="{{ route('guru.laporan.siswa') }}"
                       class="nav-sub-item {{ request()->routeIs('guru.laporan.siswa*') ? 'active' : '' }}">
                        <i class="fas fa-user-graduate"></i>
                        <span>Laporan Siswa</span>
                    </a>
                    <a href="{{ route('guru.laporan.materi') }}"
                       class="nav-sub-item {{ request()->routeIs('guru.laporan.materi*') ? 'active' : '' }}">
                        <i class="fas fa-file-alt"></i>
                        <span>Laporan Materi</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Jadwal Ujian --}}
        <div class="nav-section">
            <span class="nav-section-label">Informasi</span>
            <a href="{{ route('guru.jadwal-ujian.index') }}"
               class="nav-item {{ request()->routeIs('guru.jadwal-ujian.*') ? 'active' : '' }}"
               data-tooltip="Jadwal Ujian">
                <i class="fas fa-calendar-alt"></i>
                <span>Jadwal Ujian</span>
            </a>
        </div>

        {{-- Akun --}}
        <div class="nav-section">
            <span class="nav-section-label">Akun</span>
            <a href="{{ route('guru.profile.edit') }}"
               class="nav-item {{ request()->routeIs('guru.profile.*') ? 'active' : '' }}"
               data-tooltip="Profil Saya">
                <i class="fas fa-user-cog"></i>
                <span>Profil Saya</span>
            </a>
        </div>

    </nav>

    {{-- COLLAPSE TOGGLE --}}
    <div class="sidebar-bottom">
        <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Collapse sidebar">
            <i class="fas fa-chevron-left" id="collapseIcon"></i>
            <span class="collapse-label">Sembunyikan</span>
        </button>
    </div>

</aside>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

@include('partials.sidebar-shared-css')
@include('partials.sidebar-shared-js')
