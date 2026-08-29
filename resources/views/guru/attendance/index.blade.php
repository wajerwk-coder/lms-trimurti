{{-- Redirect / alias ke guru.absensi.index --}}
@extends('layouts.guru')

@section('title', 'Absensi Siswa')
@section('page-title', 'Absensi Siswa')
@section('page-subtitle', 'Kelola catatan kehadiran siswa.')

@section('page-actions')
    <a href="{{ route('guru.absensi.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Absensi
    </a>
@endsection

@section('content')
@include('guru.absensi.index')
@endsection