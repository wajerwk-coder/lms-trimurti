@extends('layouts.guru')

@section('title', 'Praktikum - Guru')
@section('page-title', 'Manajemen Praktikum')
@section('page-subtitle', 'Kelola praktikum yang Anda buat untuk siswa.')

@section('page-actions')
    <a href="{{ route('guru.praktikum.create') }}" class="btn btn-warning btn-sm">
        <i class="fas fa-plus me-1"></i>Buat Praktikum
    </a>
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

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-warning bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-flask text-warning fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ $praktikums->total() }}</div>
                    <small class="text-muted">Total Praktikum</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-eye text-success fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ $totalPublished }}</div>
                    <small class="text-muted">Dipublikasikan</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-secondary bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-file-alt text-secondary fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ $totalDraft }}</div>
                    <small class="text-muted">Draft</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-info bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-star text-info fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">
                        {{ $praktikums->sum('scores_count') }}
                    </div>
                    <small class="text-muted">Total Penilaian</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">
            <i class="fas fa-flask me-2 text-warning"></i>Daftar Praktikum
        </h6>
        <span class="badge bg-secondary">{{ $praktikums->total() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Judul</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Batas Waktu</th>
                        <th class="text-center">Penilaian</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($praktikums as $p)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $p->title }}</div>
                                <small class="text-muted">{{ Str::limit($p->description, 55) }}</small>
                            </td>
                            <td class="text-muted small">{{ $p->subject?->name ?? '—' }}</td>
                            <td class="text-muted small">{{ $p->kelas?->name ?? 'Semua' }}</td>
                            <td>
                                @if($p->due_date)
                                    @php $past = $p->due_date->isPast(); @endphp
                                    <div class="{{ $past ? 'text-danger' : 'text-dark' }} small">
                                        {{ $p->due_date->format('d/m/Y H:i') }}
                                    </div>
                                    @if($past)
                                        <small class="text-danger"><i class="fas fa-clock me-1"></i>Sudah lewat</small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info bg-opacity-10 text-info fw-semibold">
                                    {{ $p->scores_count ?? 0 }}
                                </span>
                            </td>
                            <td class="text-center">
                                <form action="{{ route('guru.praktikum.toggle-publish', $p) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit"
                                            class="badge border-0 {{ $p->is_published ? 'bg-success' : 'bg-warning text-dark' }}"
                                            title="{{ $p->is_published ? 'Klik untuk sembunyikan' : 'Klik untuk publikasikan' }}">
                                        <i class="fas {{ $p->is_published ? 'fa-eye' : 'fa-eye-slash' }} me-1"></i>
                                        {{ $p->is_published ? 'Publik' : 'Draft' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('guru.praktikum.show', $p) }}"
                                       class="btn btn-outline-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('guru.praktikum.edit', $p) }}"
                                       class="btn btn-outline-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('guru.praktikum.destroy', $p) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Hapus praktikum \'{{ addslashes($p->title) }}\'?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-flask fa-3x text-muted opacity-25 mb-3 d-block"></i>
                                <h6 class="text-muted">Belum ada praktikum</h6>
                                <a href="{{ route('guru.praktikum.create') }}" class="btn btn-warning btn-sm mt-2">
                                    <i class="fas fa-plus me-1"></i>Buat Praktikum
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($praktikums->hasPages())
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-2 px-4">
            <small class="text-muted">
                {{ $praktikums->firstItem() }}–{{ $praktikums->lastItem() }} dari {{ $praktikums->total() }}
            </small>
            {{ $praktikums->links() }}
        </div>
    @endif
</div>

@endsection
