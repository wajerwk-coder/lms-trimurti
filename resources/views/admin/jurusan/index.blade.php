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

{{-- Stats Cards --}}
<div class="row g-3 mb-4">
    @foreach([
        ['primary', 'fa-sitemap',      $totalJurusan, 'Total Jurusan'],
        ['success', 'fa-toggle-on',    $jurusanAktif, 'Jurusan Aktif'],
        ['info',    'fa-school',       $totalKelas,   'Total Kelas'],
        ['warning', 'fa-user-friends', $totalSiswa,   'Total Siswa'],
    ] as [$color, $icon, $val, $label])
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-{{ $color }} bg-opacity-10 flex-shrink-0">
                    <i class="fas {{ $icon }} text-{{ $color }} fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ number_format($val) }}</div>
                    <small class="text-muted">{{ $label }}</small>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="fas fa-sitemap me-2 text-primary"></i>Daftar Jurusan
        </h6>
        <div class="d-flex gap-2 align-items-center">
            <div class="input-group input-group-sm" style="width:220px;">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="fas fa-search text-muted" style="font-size:.75rem;"></i>
                </span>
                <input type="text" id="jurusanSearch" class="form-control border-start-0 ps-0"
                       placeholder="Cari jurusan…">
            </div>
            <span class="badge bg-secondary" id="jurusanCount">{{ $totalJurusan }} jurusan</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small" id="jurusanTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nama Jurusan</th>
                        <th>Kode</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Kelas</th>
                        <th class="text-center">Siswa</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jurusan as $jsn)
                    <tr class="jurusan-row">
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-2 bg-primary bg-opacity-10 p-2 flex-shrink-0">
                                    <i class="fas fa-sitemap text-primary"></i>
                                </div>
                                <span class="fw-semibold">{{ $jsn->name }}</span>
                            </div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $jsn->code }}</span></td>
                        <td class="text-muted" style="max-width:220px;">
                            <span class="d-block text-truncate" title="{{ $jsn->description }}">
                                {{ $jsn->description ?? '—' }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($jsn->is_active ?? true)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">
                                {{ $jsn->kelas_count ?? 0 }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success bg-opacity-10 text-success fw-semibold">
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
                        <td colspan="7" class="text-center py-5">
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

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchEl = document.getElementById('jurusanSearch');
    const counter  = document.getElementById('jurusanCount');
    const rows     = document.querySelectorAll('.jurusan-row');

    searchEl.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        let visible = 0;
        rows.forEach(function (row) {
            const match = !q || row.textContent.toLowerCase().includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        counter.textContent = visible + ' jurusan';
    });
});
</script>
@endpush

@endsection
