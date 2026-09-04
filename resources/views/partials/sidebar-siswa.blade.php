@php
    $user         = Auth::user();
    $siswaProfile = \App\Models\Siswa::where('user_id', auth()->id())
                        ->with('kelas')
                        ->first();
    $photoSrc = $user->photo_url;
    $photoFallback = 'https://ui-avatars.com/api/?name='.urlencode($user->name ?? 'S').'&background=7c3aed&color=fff&size=64';
@endphp

<aside class="sidebar" id="sidebar" data-role="siswa">

    {{-- BRAND --}}
    <div class="sidebar-brand">
        <a href="{{ route('siswa.dashboard') }}" class="brand-link">
            <div class="brand-icon"
                 style="background:linear-gradient(135deg,#7c3aed,#db2777);box-shadow:0 4px 12px rgba(124,58,237,.4);">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="brand-text">
                <span class="brand-name">LMS Trimurti</span>
                <span class="brand-role">Portal Siswa</span>
            </div>
        </a>
    </div>

    {{-- USER CARD --}}
    <div class="sidebar-user">
        <img src="{{ $photoSrc }}"
             alt="Avatar"
             onerror="this.onerror=null;this.src='{{ $photoFallback }}'">
        <div class="sidebar-user-info">
            <span class="sidebar-user-name">{{ Str::limit($user->name ?? 'Siswa', 18) }}</span>
            <span class="sidebar-user-role">
                {{ $siswaProfile?->kelas?->name ?? ($siswaProfile?->nis ? 'NIS: '.$siswaProfile->nis : 'Siswa') }}
            </span>
        </div>
    </div>

    {{-- NAVIGATION --}}
    <nav class="sidebar-nav">

        {{-- Utama --}}
        <div class="nav-section">
            <span class="nav-section-label">Utama</span>
            <a href="{{ route('siswa.dashboard') }}"
               class="nav-item {{ request()->routeIs('siswa.dashboard') ? 'active' : '' }}"
               data-tooltip="Dashboard">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>

        {{-- Pembelajaran --}}
        <div class="nav-section">
            <span class="nav-section-label">Pembelajaran</span>

            <a href="{{ route('siswa.pelajaran.index') }}"
               class="nav-item {{ request()->routeIs('siswa.pelajaran.*') ? 'active' : '' }}"
               data-tooltip="Mata Pelajaran">
                <i class="fas fa-graduation-cap"></i>
                <span>Mata Pelajaran</span>
            </a>

            <a href="{{ route('siswa.materials.index') }}"
               class="nav-item {{ request()->routeIs('siswa.materials.*') ? 'active' : '' }}"
               data-tooltip="Materi">
                <i class="fas fa-file-alt"></i>
                <span>Materi</span>
            </a>

            <a href="{{ route('siswa.assignments.index') }}"
               class="nav-item {{ request()->routeIs('siswa.assignments.*') ? 'active' : '' }}"
               data-tooltip="Tugas">
                <i class="fas fa-tasks"></i>
                <span>Tugas</span>
            </a>

            <a href="{{ route('siswa.praktikum.index') }}"
               class="nav-item {{ request()->routeIs('siswa.praktikum.*') || request()->routeIs('siswa.practicals.*') ? 'active' : '' }}"
               data-tooltip="Praktikum">
                <i class="fas fa-flask"></i>
                <span>Praktikum</span>
            </a>
        </div>

        {{-- Akademik --}}
        <div class="nav-section">
            <span class="nav-section-label">Akademik</span>

            <a href="{{ route('siswa.nilai.index') }}"
               class="nav-item {{ request()->routeIs('siswa.nilai.*') || request()->routeIs('siswa.reports.*') ? 'active' : '' }}"
               data-tooltip="Nilai">
                <i class="fas fa-chart-bar"></i>
                <span>Nilai Saya</span>
            </a>

            <a href="{{ route('siswa.absensi.index') }}"
               class="nav-item {{ request()->routeIs('siswa.absensi.*') || request()->routeIs('siswa.attendance.*') ? 'active' : '' }}"
               data-tooltip="Absensi">
                <i class="fas fa-calendar-check"></i>
                <span>Absensi</span>
            </a>
        </div>

        {{-- Informasi --}}
        <div class="nav-section">
            <span class="nav-section-label">Informasi</span>

            <a href="{{ route('siswa.jadwal-ujian.index') }}"
               class="nav-item {{ request()->routeIs('siswa.jadwal-ujian.*') ? 'active' : '' }}"
               data-tooltip="Jadwal Ujian">
                <i class="fas fa-calendar-alt"></i>
                <span>Jadwal Ujian</span>
            </a>
        </div>

        {{-- Akun --}}
        <div class="nav-section">
            <span class="nav-section-label">Akun</span>
            <a href="{{ route('siswa.profile.edit') }}"
               class="nav-item {{ request()->routeIs('siswa.profile.*') ? 'active' : '' }}"
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
