@extends('layouts.admin')

@section('title', 'Periode Pembelajaran')
@section('page-title', 'Periode Pembelajaran')
@section('page-subtitle', 'Kelola tahun ajaran dan semester aktif.')

@section('page-actions')
    <a href="{{ route('admin.academic-periods.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Periode
    </a>
@endsection

@section('content')

{{-- Alert --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Periode Aktif Banner --}}
@if($activePeriod)
<div class="alert border-0 mb-4"
     style="background: linear-gradient(135deg,#0f766e,#0d9488); color:#fff; border-radius:14px;">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-3 bg-white bg-opacity-20 p-3 flex-shrink-0">
            <i class="fas fa-calendar-check fa-lg text-white"></i>
        </div>
        <div>
            <div class="fw-bold fs-6">Periode Aktif Saat Ini</div>
            <div class="opacity-90">{{ $activePeriod->full_label }}</div>
            @if($activePeriod->start_date && $activePeriod->end_date)
            <small class="opacity-75">
                {{ $activePeriod->start_date->format('d M Y') }} –
                {{ $activePeriod->end_date->format('d M Y') }}
            </small>
            @endif
        </div>
        <a href="{{ route('admin.academic-periods.edit', $activePeriod) }}"
           class="btn btn-light btn-sm ms-auto">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
    </div>
</div>
@else
<div class="alert alert-warning alert-dismissible fade show mb-4">
    <i class="fas fa-exclamation-triangle me-2"></i>
    Belum ada periode pembelajaran yang aktif.
    <a href="{{ route('admin.academic-periods.create') }}" class="alert-link">Buat periode sekarang</a>.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.academic-periods.index') }}"
              class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Tahun Ajaran</label>
                <select name="academic_year" class="form-select form-select-sm">
                    <option value="">Semua Tahun</option>
                    @foreach($academicYears as $y)
                        <option value="{{ $y }}" {{ request('academic_year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Semester</label>
                <select name="semester" class="form-select form-select-sm">
                    <option value="">Semua Semester</option>
                    <option value="ganjil" {{ request('semester') == 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                    <option value="genap"  {{ request('semester') == 'genap'  ? 'selected' : '' }}>Genap</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="active"   {{ request('status') == 'active'   ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
                <a href="{{ route('admin.academic-periods.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="fas fa-calendar-alt me-2 text-primary"></i>Daftar Periode
        </h6>
        <span class="badge bg-secondary">{{ $periods->total() }} periode</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nama Periode</th>
                        <th class="text-center">Tahun Ajaran</th>
                        <th class="text-center">Semester</th>
                        <th>Durasi</th>
                        <th class="text-center">Kelas</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periods as $p)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-2 p-2 flex-shrink-0
                                    {{ $p->is_active ? 'bg-success bg-opacity-10' : 'bg-secondary bg-opacity-10' }}">
                                    <i class="fas fa-calendar-alt
                                        {{ $p->is_active ? 'text-success' : 'text-secondary' }}"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $p->name }}</div>
                                    @if($p->description)
                                    <small class="text-muted text-truncate d-block" style="max-width:200px;">
                                        {{ $p->description }}
                                    </small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-center fw-semibold">{{ $p->academic_year }}</td>
                        <td class="text-center">
                            <span class="badge
                                {{ $p->semester === 'ganjil' ? 'bg-info' : 'bg-warning text-dark' }}">
                                {{ $p->semester_label }}
                            </span>
                        </td>
                        <td>
                            @if($p->start_date && $p->end_date)
                                <small class="text-muted">
                                    {{ $p->start_date->format('d M Y') }}<br>
                                    s.d. {{ $p->end_date->format('d M Y') }}
                                </small>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary bg-opacity-10 text-dark">
                                {{ $p->kelas_count ?? 0 }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($p->is_active)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Aktif
                                </span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex gap-1 justify-content-center flex-wrap">
                                <a href="{{ route('admin.academic-periods.show', $p) }}"
                                   class="btn btn-outline-info btn-sm" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.academic-periods.edit', $p) }}"
                                   class="btn btn-outline-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(!$p->is_active)
                                <form action="{{ route('admin.academic-periods.set-active', $p) }}" method="POST"
                                      onsubmit="return confirm('Jadikan \'{{ addslashes($p->full_label) }}\' sebagai periode aktif?\nPeriode lain akan dinonaktifkan.')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success btn-sm" title="Aktifkan">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('admin.academic-periods.destroy', $p) }}" method="POST"
                                      onsubmit="return confirm('Hapus periode \'{{ addslashes($p->full_label) }}\'?\nKelas yang terhubung tidak akan ikut terhapus.')">
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
                            <i class="fas fa-calendar-alt fa-3x text-muted opacity-25 mb-3 d-block"></i>
                            <h6 class="text-muted">Belum ada periode pembelajaran</h6>
                            <a href="{{ route('admin.academic-periods.create') }}"
                               class="btn btn-primary btn-sm mt-2">
                                <i class="fas fa-plus me-1"></i>Tambah Periode Pertama
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($periods->hasPages())
    <div class="card-footer bg-white py-3">
        {{ $periods->links() }}
    </div>
    @endif
</div>

@endsection
