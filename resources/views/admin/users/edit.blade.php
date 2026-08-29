{{--
    File ini adalah wrapper — logika routing ada di UserController::edit()
    yang sudah otomatis redirect ke view yang benar (edit-admin, edit-guru, edit-siswa).
    File ini tidak seharusnya dirender langsung.
--}}
@extends('layouts.admin')
@section('title', 'Edit Pengguna')
@section('content')
<div class="text-center py-5">
    <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3 d-block"></i>
    <p class="text-muted">Memuat halaman edit...</p>
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>
@endsection
