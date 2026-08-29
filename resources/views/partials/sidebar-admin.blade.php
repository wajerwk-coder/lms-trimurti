@php
    $user               = Auth::user();
    $adminPhotoSrc      = $user->photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name ?? 'A').'&background=3b82f6&color=fff&size=64';
    $adminPhotoFallback = 'https://ui-avatars.com/api/?name='.urlencode($user->name ?? 'A').'&background=3b82f6&color=fff&size=64';
@endphp

<aside class="sidebar" id="sidebar" data-role="admin">

    {{-- BRAND --}}
    <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" class="brand-link">
            <div class="brand-icon"
                 style="background:linear-gradient(135deg,#3b82f6,#6d28d9);box-shadow:0 4px 12px rgba(99,102,241,.4);">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="brand-text">
                <span class="brand-name">LMS Trimurti</span>
                <span class="brand-role">Admin Panel</span>
            </div>
        </a>
    </div>

    {{-- USER CARD --}}
    <div class="sidebar-user">
        <img src="{{ $adminPhotoSrc }}"
             alt="Avatar"
             onerror="this.onerror=null;this.src='{{ $adminPhotoFallback }}'">
        <div class="sidebar-user-info">
            <span class="sidebar-user-name">{{ Str::limit($user->name ?? 'Admin', 18) }}</span>
            <span class="sidebar-user-role">Administrator</span>
        </div>
    </div>

    {{-- NAVIGATION --}}
    <nav class="sidebar-nav">

        {{-- Utama --}}
        <div class="nav-section">
            <span class="nav-section-label">Utama</span>
            <a href="{{ route('admin.dashboard') }}"
               class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
               data-tooltip="Dashboard">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>

        {{-- Pengguna --}}
        <div class="nav-section">
            <span class="nav-section-label">Pengguna</span>

            <div class="nav-group {{ request()->routeIs('admin.users.*') ? 'open' : '' }}" id="grp-users">
                <button class="nav-item nav-group-toggle {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                        data-tooltip="Pengguna"
                        aria-expanded="{{ request()->routeIs('admin.users.*') ? 'true' : 'false' }}">
                    <i class="fas fa-users"></i>
                    <span>Pengguna</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </button>
                <div class="nav-sub">
                    <a href="{{ route('admin.users.index') }}"
                       class="nav-sub-item {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                        <i class="fas fa-user-shield"></i>
                        <span>Admin</span>
                    </a>
                    <a href="{{ route('admin.users.guru') }}"
                       class="nav-sub-item {{ request()->routeIs('admin.users.guru') ? 'active' : '' }}">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Guru</span>
                    </a>
                    <a href="{{ route('admin.users.siswa') }}"
                       class="nav-sub-item {{ request()->routeIs('admin.users.siswa') ? 'active' : '' }}">
                        <i class="fas fa-user-graduate"></i>
                        <span>Siswa</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Akademik --}}
        <div class="nav-section">
            <span class="nav-section-label">Akademik</span>

            <a href="{{ route('admin.kelas.index') }}"
               class="nav-item {{ request()->routeIs('admin.kelas.*') ? 'active' : '' }}"
               data-tooltip="Kelas">
                <i class="fas fa-door-open"></i>
                <span>Kelas</span>
            </a>

            <a href="{{ route('admin.jurusan.index') }}"
               class="nav-item {{ request()->routeIs('admin.jurusan.*') ? 'active' : '' }}"
               data-tooltip="Jurusan">
                <i class="fas fa-sitemap"></i>
                <span>Jurusan</span>
            </a>

            <a href="{{ route('admin.mata-pelajaran.index') }}"
               class="nav-item {{ request()->routeIs('admin.mata-pelajaran.*') ? 'active' : '' }}"
               data-tooltip="Mata Pelajaran">
                <i class="fas fa-book-open"></i>
                <span>Mata Pelajaran</span>
            </a>

            <a href="{{ route('admin.kriteria-penilaian.index') }}"
               class="nav-item {{ request()->routeIs('admin.kriteria-penilaian.*') ? 'active' : '' }}"
               data-tooltip="Kriteria Penilaian">
                <i class="fas fa-clipboard-check"></i>
                <span>Kriteria Penilaian</span>
            </a>

            <a href="{{ route('admin.exam-schedules.index') }}"
               class="nav-item {{ request()->routeIs('admin.exam-schedules.*') ? 'active' : '' }}"
               data-tooltip="Jadwal Ujian">
                <i class="fas fa-calendar-alt"></i>
                <span>Jadwal Ujian</span>
            </a>
        </div>

        {{-- Konten --}}
        <div class="nav-section">
            <span class="nav-section-label">Konten</span>

            <a href="{{ route('admin.materials.index') }}"
               class="nav-item {{ request()->routeIs('admin.materials.*') ? 'active' : '' }}"
               data-tooltip="Materi">
                <i class="fas fa-file-alt"></i>
                <span>Materi</span>
            </a>

            <a href="{{ route('admin.assignments.index') }}"
               class="nav-item {{ request()->routeIs('admin.assignments.*') ? 'active' : '' }}"
               data-tooltip="Tugas">
                <i class="fas fa-tasks"></i>
                <span>Tugas</span>
            </a>

            <a href="{{ route('admin.practicals.index') }}"
               class="nav-item {{ request()->routeIs('admin.practicals.*') ? 'active' : '' }}"
               data-tooltip="Praktikum">
                <i class="fas fa-flask"></i>
                <span>Praktikum</span>
            </a>

            <a href="{{ route('admin.attendance.index') }}"
               class="nav-item {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}"
               data-tooltip="Absensi">
                <i class="fas fa-calendar-check"></i>
                <span>Absensi</span>
            </a>
        </div>

        {{-- Akun --}}
        <div class="nav-section">
            <span class="nav-section-label">Akun</span>
            <a href="{{ route('admin.profile.edit') }}"
               class="nav-item {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}"
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

{{-- Mobile overlay --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

@include('partials.sidebar-shared-css')
@include('partials.sidebar-shared-js')
