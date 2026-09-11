@extends('layouts.admin')

@section('title', 'Detail Periode — ' . $academicPeriod->full_label)
@section('page-title', 'Detail Periode Pembelajaran')
@section('page-subtitle', $academicPeriod->full_label)

@section('page-actions')
    <a href="{{ route('admin.academic-periods.edit', $academicPeriod) }}"
       class="btn btn-warning btn-sm me-1">
        <i class="fas fa-edit me-1"></i>Edit
    </a>
    <a href="{{ route('admin.academic-periods.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    {{-- ═══ KIRI: Info & Kelas ═══ --}}
    <div class="col-lg-8">

        {{-- Header Card --}}
        <div class="card border-0 shadow-sm mb-4 overflow-hidden">
            <div class="card-body p-0">
                <div style="background: linear-gradient(135deg,#0f766e,#0d9488); padding:2rem;">
                    <div class="d-flex align-items-center gap-4">
                        <div class="rounded-3 bg-white bg-opacity-20 d-flex align-items-center
                                    justify-content-center flex-shrink-0" style="width:64px;height:64px;">
                            <i class="fas fa-calendar-alt text-white fa-2x"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="fw-bold text-white mb-1">{{ $academicPeriod->name }}</h4>
                            <div class="text-white opacity-75">{{ $academicPeriod->academic_year }}</div>
                            <div class="d-flex gap-2 mt-2 flex-wrap">
                                <span class="badge rounded-pill bg-white bg-opacity-20 text-white">
                                    {{ $academicPeriod->semester_label }}
                                </span>
                                @if($academicPeriod->is_active)
                                    <span class="badge bg-success rounded-pill">
                                        <i class="fas fa-check-circle me-1"></i>Aktif
                                    </span>
                                @else
                                    <span class="badge bg-secondary rounded-pill">Nonaktif</span>
                                @endif
                                @if($academicPeriod->is_ongoing)
                                    <span class="badge bg-info rounded-pill">Sedang Berlangsung</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail Periode --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-info-circle me-2 text-primary"></i>Detail Periode
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Tahun Ajaran</label>
                        <div class="fw-semibold">{{ $academicPeriod->academic_year }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Semester</label>
                        <div>
                            <span class="badge
                                {{ $academicPeriod->semester === 'ganjil' ? 'bg-info' : 'bg-warning text-dark' }} fs-6">
                                {{ $academicPeriod->semester_label }}
                            </span>
                        </div>
                    </div>
                    @if($academicPeriod->start_date)
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Tanggal Mulai</label>
                        <div class="fw-semibold">{{ $academicPeriod->start_date->format('d F Y') }}</div>
                    </div>
                    @endif
                    @if($academicPeriod->end_date)
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Tanggal Selesai</label>
                        <div class="fw-semibold">{{ $academicPeriod->end_date->format('d F Y') }}</div>
                    </div>
                    @endif
                    @if($academicPeriod->description)
                    <div class="col-12">
                        <label class="form-label small text-muted mb-1">Keterangan</label>
                        <div>{{ $academicPeriod->description }}</div>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Dibuat</label>
                        <div class="small">{{ $academicPeriod->created_at->format('d M Y, H:i') }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Terakhir Diperbarui</label>
                        <div class="small">{{ $academicPeriod->updated_at->format('d M Y, H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar Kelas --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-school me-2 text-primary"></i>Kelas dalam Periode Ini
                </h6>
                <span class="badge bg-secondary">{{ $kelasList->count() }} kelas</span>
            </div>
            <div class="card-body p-0">
                @if($kelasList->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-school fa-3x text-muted opacity-25 mb-3 d-block"></i>
                        <p class="text-muted small mb-2">Belum ada kelas yang terhubung ke periode ini.</p>
                        <a href="{{ route('admin.kelas.create') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Tambah Kelas
                        </a>
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Nama Kelas</th>
                                <th class="text-center">Tingkat</th>
                                <th>Jurusan</th>
                                <th class="text-center">Siswa</th>
                                <th class="text-center">Status</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kelasList as $kls)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $kls->name }}</td>
                                <td class="text-center">
                                    @if($kls->grade)
                                        <span class="badge bg-primary bg-opacity-10 text-primary">{{ $kls->grade }}</span>
                                    @else —
                                    @endif
                                </td>
                                <td class="text-muted">{{ $kls->jurusan?->name ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-secondary bg-opacity-10 text-dark">
                                        {{ $kls->siswa_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if(($kls->status ?? 'active') === 'active')
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center pe-4">
                                    <a href="{{ route('admin.kelas.show', $kls) }}"
                                       class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ═══ KANAN: Aksi ═══ --}}
    <div class="col-lg-4">

        {{-- Statistik --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-chart-bar me-2 text-primary"></i>Statistik
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-6">
                        <div class="h2 fw-bold text-primary mb-0">{{ $kelasList->count() }}</div>
                        <small class="text-muted">Total Kelas</small>
                    </div>
                    <div class="col-6">
                        <div class="h2 fw-bold text-success mb-0">
                            {{ $kelasList->sum('siswa_count') }}
                        </div>
                        <small class="text-muted">Total Siswa</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Aksi --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-cog me-2 text-primary"></i>Aksi
                </h6>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('admin.academic-periods.edit', $academicPeriod) }}"
                   class="btn btn-warning fw-semibold text-dark">
                    <i class="fas fa-edit me-2"></i>Edit Periode
                </a>
                @if(!$academicPeriod->is_active)
                <form action="{{ route('admin.academic-periods.set-active', $academicPeriod) }}"
                      method="POST"
                      onsubmit="return confirm('Jadikan periode ini sebagai periode aktif?\nPeriode lain akan dinonaktifkan.')">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-check-circle me-2"></i>Jadikan Aktif
                    </button>
                </form>
                @else
                <button class="btn btn-success w-100" disabled>
                    <i class="fas fa-check-circle me-2"></i>Periode Ini Aktif
                </button>
                @endif
                <hr class="my-1">
                <form action="{{ route('admin.academic-periods.destroy', $academicPeriod) }}"
                      method="POST"
                      onsubmit="return confirm('Hapus periode \'{{ addslashes($academicPeriod->full_label) }}\'?\nKelas yang terhubung tidak akan ikut terhapus.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="fas fa-trash me-2"></i>Hapus Periode
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>

@endsection
