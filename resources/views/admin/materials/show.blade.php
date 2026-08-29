@extends('layouts.admin')

@section('title', 'Detail Materi')
@section('page-title', 'Detail Materi')
@section('page-subtitle', 'Informasi lengkap dan statistik materi pembelajaran.')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.materials.edit', $material) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <form action="{{ route('admin.materials.toggle-publish', $material) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm {{ $material->published_at ? 'btn-secondary' : 'btn-success' }}">
                <i class="fas fa-{{ $material->published_at ? 'eye-slash' : 'eye' }} me-1"></i>
                {{ $material->published_at ? 'Sembunyikan' : 'Publikasikan' }}
            </button>
        </form>
        <a href="{{ route('admin.materials.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
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

<div class="row g-4">

    {{-- Info Materi --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0 fw-bold"><i class="fas fa-book me-2"></i>Informasi Materi</h6>
            </div>
            <div class="card-body">
                <h5 class="fw-bold mb-1">{{ $material->title }}</h5>
                <p class="text-muted small mb-3">
                    <i class="fas fa-user me-1"></i>{{ $material->guru?->name ?? 'N/A' }}
                    &nbsp;·&nbsp;
                    <i class="fas fa-book me-1"></i>{{ $material->subject?->name ?? 'N/A' }}
                </p>

                @if($material->content)
                    <h6 class="fw-bold">Konten</h6>
                    <div class="bg-light rounded-3 p-3 mb-3">
                        {!! nl2br(e($material->content)) !!}
                    </div>
                @endif

                @if($material->video_url)
                    <div class="mt-3">
                        <h6 class="fw-bold"><i class="fas fa-video me-1 text-info"></i>Video</h6>
                        <a href="{{ $material->video_url }}" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-external-link-alt me-1"></i>Buka Video
                        </a>
                    </div>
                @endif

                @if($material->file_url)
                    <div class="mt-3">
                        <h6 class="fw-bold"><i class="fas fa-file-alt me-1 text-success"></i>File Materi</h6>
                        <div class="d-flex align-items-center p-3 bg-light rounded-3">
                            <i class="fas fa-file-alt text-success me-3 fa-lg"></i>
                            <div class="flex-grow-1">
                                <div class="fw-medium">{{ $material->file_url }}</div>
                            </div>
                            <a href="{{ Storage::url('materials/' . $material->file_url) }}" download
                               class="btn btn-sm btn-outline-success">
                                <i class="fas fa-download me-1"></i>Unduh
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Download Log --}}
        @if(isset($downloads) && $downloads->count())
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-download me-2"></i>Log Unduhan</h6>
                    <span class="badge bg-white text-info">{{ $downloads->total() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Siswa</th>
                                    <th>Waktu Unduh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($downloads as $dl)
                                    <tr>
                                        <td class="ps-3 fw-semibold">{{ $dl->siswa?->name ?? 'N/A' }}</td>
                                        <td class="text-muted">{{ optional($dl->downloaded_at ?? $dl->created_at)->format('d M Y H:i') ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($downloads->hasPages())
                    <div class="card-footer bg-white border-top">{{ $downloads->links() }}</div>
                @endif
            </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Detail Info</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted d-block">Status</small>
                    @if($material->published_at)
                        <span class="badge bg-success">Published</span>
                    @else
                        <span class="badge bg-secondary">Draft</span>
                    @endif
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Dipublikasikan</small>
                    <span class="fw-semibold">{{ optional($material->published_at)->format('d M Y H:i') ?? '—' }}</span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Guru</small>
                    <span class="fw-semibold">{{ $material->guru?->name ?? '—' }}</span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Mata Pelajaran</small>
                    <span class="fw-semibold">{{ $material->subject?->name ?? '—' }}</span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Kelas</small>
                    <span class="fw-semibold">{{ $material->kelas?->name ?? 'Semua Kelas' }}</span>
                </div>
                <hr>
                <div class="mb-2">
                    <small class="text-muted d-block">Total Unduhan</small>
                    <span class="fw-bold text-primary h5">{{ $material->downloads_count ?? 0 }}</span>
                </div>
                @if(isset($stats))
                    <div class="mb-2">
                        <small class="text-muted d-block">Unduhan Minggu Ini</small>
                        <span class="fw-semibold text-success">{{ $stats['last_week_downloads'] ?? 0 }}</span>
                    </div>
                    <div>
                        <small class="text-muted d-block">Pengunduh Unik</small>
                        <span class="fw-semibold text-info">{{ $stats['unique_downloaders'] ?? 0 }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body d-grid gap-2">
                <a href="{{ route('admin.materials.edit', $material) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i>Edit Materi
                </a>
                <button type="button" class="btn btn-danger"
                        onclick="if(confirm('Hapus materi ini?')) document.getElementById('deleteForm').submit()">
                    <i class="fas fa-trash me-2"></i>Hapus Materi
                </button>
                <form id="deleteForm" action="{{ route('admin.materials.destroy', $material) }}" method="POST">
                    @csrf @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</div>

@endsection