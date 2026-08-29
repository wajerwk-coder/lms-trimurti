@extends('layouts.app-dashboard')

@section('title', 'LMS Siswa')

@section('sidebar')
    <a href="{{ route('siswa.dashboard') }}" class="menu-item {{ request()->routeIs('siswa.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('siswa.pelajaran.index') }}" class="menu-item {{ request()->routeIs('siswa.pelajaran.*') ? 'active' : '' }}">
        <i class="fas fa-graduation-cap"></i>
        <span>Pelajaran</span>
    </a>
    <a href="{{ route('siswa.materials.index') }}" class="menu-item {{ request()->routeIs('siswa.materials.*') ? 'active' : '' }}">
        <i class="fas fa-book"></i>
        <span>Materi</span>
    </a>
    <a href="{{ route('siswa.assignments.index') }}" class="menu-item {{ request()->routeIs('siswa.assignments.*') ? 'active' : '' }}">
        <i class="fas fa-tasks"></i>
        <span>Tugas</span>
    </a>
    <a href="{{ route('siswa.reports.index') }}" class="menu-item {{ request()->routeIs('siswa.reports.index') ? 'active' : '' }}">
        <i class="fas fa-chart-line"></i>
        <span>Nilai</span>
    </a>
    <a href="{{ route('siswa.praktikum.index') }}" class="menu-item {{ request()->routeIs('siswa.praktikum.*') ? 'active' : '' }}">
        <i class="fas fa-flask"></i>
        <span>Praktikum</span>
    </a>
    <a href="{{ route('siswa.reports.attendance') }}" class="menu-item {{ request()->routeIs('siswa.reports.attendance') ? 'active' : '' }}">
        <i class="fas fa-calendar-check"></i>
        <span>Absensi</span>
    </a>
    <a href="{{ route('siswa.profile.edit') }}" class="menu-item {{ request()->routeIs('siswa.profile.*') ? 'active' : '' }}">
        <i class="fas fa-user-circle"></i>
        <span>Profil</span>
    </a>
    <a href="{{ route('logout') }}" class="menu-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
@endsection