@extends('layouts.admin')

@section('title', 'Detail Kelas - ' . $kelas->name)
@section('page-title', 'Detail Kelas')
@section('page-subtitle', $kelas->name . ' — ' . $kelas->grade . ' | ' . ($kelas->academic_year ?? ''))

@section('page-actions')
    <a href="{{ route('admin.kelas.edit', $kelas->id) }}" class="btn btn-sm btn-warning me-1">
        <i class="fas fa-edit me-1"></i> Edit
    </a>
    <a href="{{ route('admin.kelas.index') }}" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
@endsection

@push('css')
<style>
/* Custom styling for kelas detail page */
.info-card {
    background: linear-gradient(135deg, #f8f9fc 0%, #ffffff 100%);
    border: 1px solid #e3e6f0;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.info-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}

.info-label {
    font-weight: 600;
    color: #5a5c69;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    color: #3a3b45;
    font-weight: 500;
    font-size: 1rem;
}

.student-avatar {
    width: 32px;
    height: 32px;
    object-fit: cover;
}

.action-buttons .btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 10px 20px;
    transition: all 0.3s ease;
}

@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .action-buttons .btn {
        width: 100%;
    }
}
</style>
@endpush

@section('content')
<div class="row">
    <!-- Back Button -->
    <div class="col-12 mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.kelas.index') }}">Manajemen Kelas</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $kelas->name }}</li>
            </ol>
        </nav>
    </div>
    
    <!-- Class Information Card -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-school me-2"></i>
                    Informasi Kelas
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="fas fa-school text-primary fs-1"></i>
                    </div>
                    <h4 class="mt-3 mb-2">{{ $kelas->name }}</h4>
                    @if($kelas->grade)
                        <span class="badge bg-secondary mb-2">Kelas {{ $kelas->grade }}</span>
                    @endif
                    <p class="text-muted mb-0">{{ $kelas->jurusan?->name ?? 'Jurusan tidak ditentukan' }}</p>
                </div>

                <hr>

                <div class="info-card p-3 mb-3">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="info-label">Tingkat</div>
                                <div class="info-value">{{ $kelas->grade }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="info-label">Jurusan</div>
                                <div class="info-value">
                                    @php $jurusanName = $kelas->jurusan?->name ?? '—'; @endphp
                                    @php $jc = match(strtolower($jurusanName)) { 'keperawatan' => 'info', 'farmasi' => 'warning', default => 'success' }; @endphp
                                    <span class="badge bg-{{ $jc }}">{{ $jurusanName }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row text-center">
                    <div class="col-6">
                        <div class="info-label">Kapasitas</div>
                        <div class="info-value">{{ $kelas->siswa->count() }} siswa</div>
                    </div>
                    <div class="col-6">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            @if($kelas->status === 'active')
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </div>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <div class="info-label">Tahun Ajaran</div>
                    <div class="info-value">{{ $kelas->academic_year }}</div>
                </div>

                <div class="mb-3">
                    <div class="info-label">Wali Kelas</div>
                    <div class="info-value">
                        @if($kelas->guru_id && $kelas->guru)
                            <div class="d-flex align-items-center">
                                <img src="{{ asset('images/default-avatar.png') }}" 
                                     class="rounded-circle me-2 student-avatar" alt="Wali Kelas">
                                <span>{{ $kelas->guru->name }}</span>
                            </div>
                        @else
                            <span class="text-muted">Belum ditentukan</span>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <div class="info-label">Dibuat</div>
                    <div class="info-value">
                        @if($kelas->created_at)
                            {{ $kelas->created_at->format('d M Y H:i') }}
                        @else
                            <span class="text-muted">Tidak tersedia</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="action-buttons d-flex gap-2">
                    <a href="{{ route('admin.kelas.edit', $kelas->id) }}" class="btn btn-warning flex-fill">
                        <i class="fas fa-edit me-1"></i>
                        Edit
                    </a>
                    <button type="button" class="btn btn-danger flex-fill" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash me-1"></i>
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Students List -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>
                    Daftar Siswa ({{ $kelas->siswa->count() }} siswa)
                </h5>
                @if($kelas->siswa->count() > 0)
                    <span class="badge bg-primary">{{ $kelas->siswa->count() }} siswa</span>
                @else
                    <span class="badge bg-secondary">Kosong</span>
                @endif
            </div>
            
            <div class="card-body">
                @if($kelas->siswa->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="60">#</th>
                                    <th>Nama Siswa</th>
                                    <th>NIS</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kelas->siswa as $index => $siswa)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $siswa->photo_url ?? asset('images/default-avatar.png') }}" 
                                                 class="rounded-circle me-2 student-avatar" alt="Avatar">
                                            <div>
                                                <div class="fw-semibold">{{ $siswa->name }}</div>
                                                <small class="text-muted">{{ $siswa->phone ?? 'No phone' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $siswa->siswa?->nis ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>{{ $siswa->email }}</td>
                                    <td>
                                        @if($siswa->status === 'active')
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.users.show', $siswa->id) }}" 
                                               class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.users.edit', $siswa->id) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit">
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
                        <i class="fas fa-user-slash fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum ada siswa</h5>
                        <p class="text-muted mb-4">Kelas ini belum memiliki siswa yang terdaftar</p>
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i>
                            Tambah Siswa Baru
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mt-4">
    <div class="col-md-3 mb-3">
        <div class="info-card p-3 text-center h-100">
            <div class="text-primary">
                <i class="fas fa-user-graduate fa-2x mb-2"></i>
            </div>
            <div class="h4 mb-1 text-primary">{{ $kelas->siswa->count() }}</div>
            <div class="small text-muted">Total Siswa</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="info-card p-3 text-center h-100">
            <div class="text-success">
                <i class="fas fa-user-check fa-2x mb-2"></i>
            </div>
            <div class="h4 mb-1 text-success">{{ $kelas->siswa->where('status', 'active')->count() }}</div>
            <div class="small text-muted">Siswa Aktif</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="info-card p-3 text-center h-100">
            <div class="text-warning">
                <i class="fas fa-percentage fa-2x mb-2"></i>
            </div>
            <div class="h4 mb-1 text-warning">
                {{ $kelas->siswa->count() > 0 ? 100 : 0 }}%
            </div>
            <div class="small text-muted">Terisi</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="info-card p-3 text-center h-100">
            <div class="text-info">
                <i class="fas fa-users fa-2x mb-2"></i>
            </div>
            <div class="h4 mb-1 text-info">{{ $kelas->siswa->count() }}</div>
            <div class="small text-muted">Total Siswa</div>
        </div>
    </div>
</div>

<!-- Back and Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex justify-content-between">
            <a href="{{ route('admin.kelas.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Kembali ke Daftar Kelas
            </a>
            <div>
                <a href="{{ route('admin.kelas.edit', $kelas->id) }}" class="btn btn-warning me-2">
                    <i class="fas fa-edit me-1"></i>
                    Edit Kelas
                </a>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash me-1"></i>
                    Hapus Kelas
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Konfirmasi Hapus Kelas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-school fa-4x text-danger mb-3"></i>
                    <h5 class="mb-3">Hapus Kelas: {{ $kelas->name }}?</h5>
                    <p class="text-muted mb-0">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                
                @if($kelas->siswa->count() > 0)
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Peringatan:</strong> Kelas ini masih memiliki {{ $kelas->siswa->count() }} siswa. 
                        Anda harus memindahkan semua siswa ke kelas lain terlebih dahulu sebelum menghapus kelas ini.
                    </div>
                @else
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        Kelas ini tidak memiliki siswa dan aman untuk dihapus.
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Batal
                </button>
                @if($kelas->siswa->count() === 0)
                    <form action="{{ route('admin.kelas.destroy', $kelas->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>
                            Ya, Hapus Kelas
                        </button>
                    </form>
                @else
                    <button type="button" class="btn btn-danger" disabled>
                        <i class="fas fa-ban me-1"></i>
                        Tidak Dapat Dihapus
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection