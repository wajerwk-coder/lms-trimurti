@extends('layouts.admin')

@section('title', 'Detail Jurusan — ' . $jurusan->name)
@section('page-title', 'Detail Jurusan')
@section('page-subtitle', $jurusan->name . ($jurusan->code ? ' (' . $jurusan->code . ')' : ''))

@section('page-actions')
    <a href="{{ route('admin.jurusan.edit', $jurusan->id) }}" class="btn btn-warning btn-sm me-1">
        <i class="fas fa-edit me-1"></i>Edit
    </a>
    <a href="{{ route('admin.jurusan.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')

<div class="row g-4">

    {{-- Info Jurusan --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-graduation-cap me-2 text-primary"></i>Informasi Jurusan
                </h6>
            </div>
            <div class="card-body">

                {{-- Nama --}}
                <div class="mb-3">
                    <div class="text-muted small fw-semibold mb-1">NAMA</div>
                    <div class="fw-semibold fs-5">{{ $jurusan->name }}</div>
                </div>

                {{-- Kode --}}
                <div class="mb-3">
                    <div class="text-muted small fw-semibold mb-1">KODE</div>
                    <span class="badge bg-primary fs-6 px-3 py-2">{{ $jurusan->code }}</span>
                </div>

                {{-- Status --}}
                <div class="mb-3">
                    <div class="text-muted small fw-semibold mb-1">STATUS</div>
                    @if($jurusan->is_active)
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i>Aktif
                        </span>
                    @else
                        <span class="badge bg-secondary">
                            <i class="fas fa-times-circle me-1"></i>Nonaktif
                        </span>
                    @endif
                </div>

                {{-- Deskripsi --}}
                @if($jurusan->description)
                <div class="mb-3">
                    <div class="text-muted small fw-semibold mb-1">DESKRIPSI</div>
                    <div class="text-muted small">{{ $jurusan->description }}</div>
                </div>
                @endif

                {{-- Dibuat --}}
                <div class="mb-0">
                    <div class="text-muted small fw-semibold mb-1">DIBUAT</div>
                    <div class="small text-muted">{{ $jurusan->created_at?->format('d M Y H:i') ?? '—' }}</div>
                </div>

            </div>
        </div>
    </div>

    {{-- Statistik + Kelas --}}
    <div class="col-lg-8">

        {{-- Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="h2 fw-bold text-primary mb-0">
                        {{ $jurusan->kelas_count ?? $jurusan->kelas->count() }}
                    </div>
                    <small class="text-muted"><i class="fas fa-school me-1"></i>Total Kelas</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="h2 fw-bold text-success mb-0">
                        {{ $jurusan->siswa_count ?? $jurusan->kelas->sum(fn($k) => $k->siswa->count()) }}
                    </div>
                    <small class="text-muted"><i class="fas fa-user-graduate me-1"></i>Total Siswa</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center py-3">
                    @php
                        $aktifCount = $jurusan->kelas()->where('status','active')->count();
                    @endphp
                    <div class="h2 fw-bold text-info mb-0">{{ $aktifCount }}</div>
                    <small class="text-muted"><i class="fas fa-door-open me-1"></i>Kelas Aktif</small>
                </div>
            </div>
        </div>

        {{-- Daftar Kelas --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-school me-2 text-info"></i>Daftar Kelas
                </h6>
                <a href="{{ route('admin.kelas.create') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Tambah Kelas
                </a>
            </div>
            <div class="card-body p-0">
                @if($jurusan->kelas->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Nama Kelas</th>
                                <th class="text-center">Tingkat</th>
                                <th>Tahun Ajaran</th>
                                <th class="text-center">Siswa</th>
                                <th class="text-center">Status</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jurusan->kelas as $kls)
                            <tr>
                                <td class="ps-4 fw-semibold">
                                    <a href="{{ route('admin.kelas.show', $kls->id) }}"
                                       class="text-decoration-none text-dark">
                                        {{ $kls->name }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        {{ $kls->grade ?? '—' }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $kls->academic_year ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">
                                        {{ $kls->siswa->count() }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($kls->status === 'active')
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('admin.kelas.show', $kls->id) }}"
                                           class="btn btn-outline-info btn-sm" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.kelas.edit', $kls->id) }}"
                                           class="btn btn-outline-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-school fa-3x text-muted opacity-25 mb-3 d-block"></i>
                    <h6 class="text-muted">Belum ada kelas</h6>
                    <a href="{{ route('admin.kelas.create') }}" class="btn btn-primary btn-sm mt-2">
                        <i class="fas fa-plus me-1"></i>Tambah Kelas Pertama
                    </a>
                </div>
                @endif
            </div>
        </div>

    </div>

</div>

@endsection