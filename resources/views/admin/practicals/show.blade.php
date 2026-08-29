@extends('layouts.admin')

@section('title', 'Detail Praktikum')
@section('page-title', 'Detail Praktikum')
@section('page-subtitle', 'Informasi lengkap praktikum.')

@section('page-actions')
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.practicals.edit', $practical) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <form action="{{ route('admin.practicals.toggle-publish', $practical) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm {{ $practical->is_published ? 'btn-secondary' : 'btn-success' }}">
                <i class="fas fa-{{ $practical->is_published ? 'eye-slash' : 'eye' }} me-1"></i>
                {{ $practical->is_published ? 'Unpublish' : 'Publish' }}
            </button>
        </form>
        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
            <i class="fas fa-trash me-1"></i>Hapus
        </button>
        <a href="{{ route('admin.practicals.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>
@endsection

@section('content')

{{-- Flash --}}
@if(session('success'))
    <div id="flashMessage" class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">

    {{-- Main Column --}}
    <div class="col-lg-8">

        {{-- Info Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0 fw-bold"><i class="fas fa-flask me-2"></i>Informasi Praktikum</h6>
            </div>
            <div class="card-body">
                <h5 class="fw-bold mb-1">{{ $practical->title }}</h5>
                <p class="text-muted small mb-3">
                    <i class="fas fa-user me-1"></i>{{ $practical->guru->name ?? 'N/A' }}
                    &nbsp;·&nbsp;
                    <i class="fas fa-calendar me-1"></i>{{ optional($practical->created_at)->format('d/m/Y H:i') }}
                </p>

                <h6 class="fw-bold">Deskripsi</h6>
                <div class="bg-light rounded-3 p-3 mb-3">
                    {!! nl2br(e($practical->description)) !!}
                </div>

                @if($practical->instructions)
                    <h6 class="fw-bold">Instruksi</h6>
                    <div class="bg-light rounded-3 p-3">
                        @if(is_array($practical->instructions))
                            <ol class="mb-0 ps-3 small">
                                @foreach($practical->instructions as $step)
                                    <li>{{ $step }}</li>
                                @endforeach
                            </ol>
                        @else
                            {!! nl2br(e($practical->instructions)) !!}
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Scores Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-list-check me-2"></i>Penilaian Siswa</h6>
                <span class="badge bg-white text-success">{{ $practical->scores->count() }} siswa</span>
            </div>
            <div class="card-body p-0">
                @if($practical->scores->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Nama Siswa</th>
                                    <th class="text-center">Nilai</th>
                                    <th>Komentar</th>
                                    <th>Tanggal Penilaian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($practical->scores as $i => $score)
                                    <tr>
                                        <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                                        <td class="fw-semibold">{{ $score->siswa->name ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            @if($score->score !== null)
                                                @php
                                                    $val = $score->score;
                                                    $badgeColor = $val >= 80 ? 'success' : ($val >= 60 ? 'warning' : 'danger');
                                                @endphp
                                                <span class="badge bg-{{ $badgeColor }}">{{ $val }}</span>
                                            @else
                                                <span class="badge bg-secondary">Belum dinilai</span>
                                            @endif
                                        </td>
                                        <td class="text-muted">{{ $score->comment ?? '—' }}</td>
                                        <td class="text-muted small">
                                            {{ optional($score->created_at)->format('d/m/Y H:i') ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted opacity-25 mb-3 d-block"></i>
                        <p class="text-muted mb-0">Belum ada penilaian siswa.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Detail Info</h6>
            </div>
            <div class="card-body">

                <div class="mb-3">
                    <small class="text-muted d-block">Status</small>
                    @if($practical->is_published)
                        <span class="badge bg-success">Published</span>
                    @else
                        <span class="badge bg-secondary">Draft</span>
                    @endif
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Batas Waktu</small>
                    <span class="fw-semibold">
                        {{ optional($practical->due_date)->format('d M Y H:i') ?? '—' }}
                    </span>
                </div>

                @if($practical->published_at)
                <div class="mb-3">
                    <small class="text-muted d-block">Dipublikasikan</small>
                    <span class="fw-semibold">{{ optional($practical->published_at)->format('d M Y H:i') }}</span>
                </div>
                @endif

                <div class="mb-3">
                    <small class="text-muted d-block">Guru</small>
                    <span class="fw-semibold">{{ $practical->guru->name ?? '—' }}</span>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Mata Pelajaran</small>
                    <span class="fw-semibold">{{ $practical->subject->name ?? '—' }}</span>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Kelas</small>
                    <span class="fw-semibold">{{ $practical->kelas->name ?? 'Semua Kelas' }}</span>
                </div>

                <hr>

                @php
                    $scored = $practical->scores->whereNotNull('score');
                    $avgScore = $scored->count() > 0 ? $scored->avg('score') : 0;
                @endphp

                <div class="mb-2">
                    <small class="text-muted d-block">Total Penilaian</small>
                    <span class="fw-bold text-primary">{{ $practical->scores->count() }}</span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Sudah Dinilai</small>
                    <span class="fw-bold text-success">{{ $scored->count() }}</span>
                </div>
                <div>
                    <small class="text-muted d-block">Rata-rata Nilai</small>
                    <span class="fw-bold text-info">{{ number_format($avgScore, 1) }}</span>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Apakah Anda yakin ingin menghapus praktikum <strong>{{ $practical->title }}</strong>?</p>
                <p class="text-danger small"><i class="fas fa-info-circle me-1"></i>Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('admin.practicals.destroy', $practical) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const flash = document.getElementById('flashMessage');
    if (flash) setTimeout(() => flash.classList.remove('show'), 5000);
});
</script>
@endpush

@endsection