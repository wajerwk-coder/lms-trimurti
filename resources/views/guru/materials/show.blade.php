@extends('layouts.guru')

@section('title', $material->title)
@section('page-title', $material->title)
@section('page-subtitle', ($material->subject?->name ?? '—') . ' · Materi')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('guru.materials.index') }}" class="text-decoration-none">Materi</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $material->title }}</li>
@endsection

@section('page-actions')
    <div class="d-flex gap-2">
        @if($material->file_url)
        <a href="{{ route('guru.materials.download', $material->id) }}" class="btn btn-success btn-sm">
            <i class="fas fa-download me-1"></i> Download File
        </a>
        @endif
        <a href="{{ route('guru.materials.edit', $material->id) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        <a href="{{ route('guru.materials.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-12">
        <!-- Material Info Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="h3 mb-3">{{ $material->title }}</h1>
                        <div class="d-flex flex-wrap gap-3 mb-2">
                            <span class="text-muted">
                                <i class="fas fa-book me-1"></i>
                                {{ $material->subject?->name ?? '—' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <span class="badge {{ $material->is_published ? 'bg-success' : 'bg-secondary' }} fs-6 px-3 py-2">
                            {{ $material->is_published ? 'Diterbitkan' : 'Draft' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Material Details Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Informasi Materi</h6>
                        <div class="d-flex flex-column gap-2">
                            <small class="text-muted">
                                <i class="fas fa-calendar-plus me-1"></i>
                                Dibuat: {{ $material->created_at->format('d M Y H:i') }}
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-calendar-edit me-1"></i>
                                Diupdate: {{ $material->updated_at->format('d M Y H:i') }}
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Statistik</h6>
                        <div class="d-flex flex-column gap-2">
                            <small class="text-muted">
                                <i class="fas fa-eye me-1"></i>
                                {{ $material->views_count ?? 0 }} views
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-download me-1"></i>
                                {{ $material->downloads_count ?? 0 }} downloads
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Material Description -->
        @if($material->content)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file-text text-primary me-2"></i>
                    Deskripsi Materi
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <p class="mb-0">{!! nl2br(e($material->content)) !!}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- File Information -->
        @if($material->file_url)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file text-success me-2"></i>
                    File Materi
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-success d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="bg-success text-white rounded p-3 me-3">
                            <i class="fas fa-file fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">{{ $material->file_url }}</h6>
                            <small class="text-muted d-block">{{ $material->file_size_formatted }}</small>
                        </div>
                    </div>
                    <a href="{{ route('guru.materials.download', $material->id) }}" class="btn btn-success">
                        <i class="fas fa-download me-1"></i>Download
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar text-info me-2"></i>
                    Statistik Materi
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <div class="stats-card bg-primary bg-opacity-10 border border-primary border-opacity-25">
                            <div class="display-6 fw-bold text-primary">{{ $material->views_count ?? 0 }}</div>
                            <small class="text-primary fw-medium">Views</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stats-card bg-success bg-opacity-10 border border-success border-opacity-25">
                            <div class="display-6 fw-bold text-success">{{ $material->downloads_count ?? 0 }}</div>
                            <small class="text-success fw-medium">Downloads</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stats-card bg-info bg-opacity-10 border border-info border-opacity-25">
                            <div class="display-6 fw-bold text-info">{{ $stats['unique_downloaders'] ?? 0 }}</div>
                            <small class="text-info fw-medium">Unique Downloads</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Downloads History -->
        @if(isset($downloads) && $downloads->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-users text-warning me-2"></i>
                    Riwayat Download ({{ $downloads->total() }} total)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Siswa</th>
                                <th>Waktu Download</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($downloads as $download)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <small class="fw-bold">
                                                {{ strtoupper(substr($download->siswa->name ?? 'Guest', 0, 2)) }}
                                            </small>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $download->siswa->name ?? 'Guest User' }}</div>
                                            <small class="text-muted">{{ $download->siswa->email ?? 'No email' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{ $download->downloaded_at ? $download->downloaded_at->format('d M Y H:i') : 'Unknown' }}
                                </td>
                                <td class="text-muted">
                                    {{ $download->ip_address ?? 'Unknown' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($downloads->hasPages())
                <div class="mt-3">
                    {{ $downloads->links() }}
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
