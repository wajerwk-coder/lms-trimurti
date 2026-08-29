@extends('layouts.admin')

@section('title', 'Manajemen Jurusan')
@section('page-title', 'Manajemen Jurusan')
@section('page-subtitle', 'Kelola data jurusan SMK Kesehatan Trimurti Husada.')

@section('page-actions')
    <a href="{{ route('admin.jurusan.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Jurusan
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

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nama Jurusan</th>
                        <th>Kode</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Kelas</th>
                        <th class="text-center">Siswa</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jurusan as $jsn)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-2 bg-primary bg-opacity-10 p-2 flex-shrink-0">
                                    <i class="fas fa-sitemap text-primary"></i>
                                </div>
                                <span class="fw-semibold">{{ $jsn->name }}</span>
                            </div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $jsn->code }}</span></td>
                        <td class="text-muted">{{ $jsn->description ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                {{ $jsn->kelas_count ?? 0 }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success bg-opacity-10 text-success">
                                {{ $jsn->siswa_count ?? 0 }}
                            </span>
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('admin.jurusan.show', $jsn->id) }}"
                                   class="btn btn-outline-info btn-sm" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.jurusan.edit', $jsn->id) }}"
                                   class="btn btn-outline-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.jurusan.destroy', $jsn->id) }}" method="POST"
                                      onsubmit="return confirm('Hapus jurusan {{ addslashes($jsn->name) }}? Tindakan tidak dapat dibatalkan.')">
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
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-sitemap fa-3x text-muted opacity-25 mb-3 d-block"></i>
                            <h6 class="text-muted">Belum ada data jurusan</h6>
                            <a href="{{ route('admin.jurusan.create') }}" class="btn btn-primary btn-sm mt-2">
                                <i class="fas fa-plus me-1"></i>Tambah Jurusan Pertama
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection