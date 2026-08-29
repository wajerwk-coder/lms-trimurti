@extends('layouts.admin')

@section('title', 'Materi Pembelajaran')
@section('page-title', 'Materi Pembelajaran')
@section('page-subtitle', 'Kelola semua materi pembelajaran dari guru.')

@section('page-actions')
    <a href="{{ route('admin.materials.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Materi
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
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-primary bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-book text-primary fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ number_format($stats['total_materials'] ?? 0) }}</div>
                    <small class="text-muted">Total Materi</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-eye text-success fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ number_format($stats['published_materials'] ?? 0) }}</div>
                    <small class="text-muted">Dipublikasikan</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-warning bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-eye-slash text-warning fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ number_format($stats['unpublished_materials'] ?? 0) }}</div>
                    <small class="text-muted">Disembunyikan</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-info bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-download text-info fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ number_format($stats['total_downloads'] ?? 0) }}</div>
                    <small class="text-muted">Total Unduhan</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Cari Materi</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari judul...">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="published">Dipublikasikan</option>
                    <option value="unpublished">Disembunyikan</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2 align-items-end">
                <button type="button" id="bulkDeleteBtn" class="btn btn-outline-danger btn-sm flex-fill" disabled>
                    <i class="fas fa-trash me-1"></i>Hapus
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-book me-2 text-primary"></i>Daftar Materi</h6>
        <span class="badge bg-secondary">{{ $materials->total() }} materi</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width:40px;">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>Judul Materi</th>
                        <th>Guru</th>
                        <th>Mata Pelajaran</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Unduhan</th>
                        <th>Tanggal</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody id="materialsBody">
                    @forelse($materials as $material)
                        <tr class="material-row"
                            data-status="{{ $material->published_at ? 'published' : 'unpublished' }}">
                            <td class="ps-4">
                                <input type="checkbox" class="form-check-input material-checkbox" value="{{ $material->id }}">
                            </td>
                            <td>
                                <div class="fw-semibold">{{ Str::limit($material->title, 45) }}</div>
                            </td>
                            <td class="text-muted">{{ $material->guru?->name ?? $material->teacher?->name ?? '—' }}</td>
                            <td>
                                @if($material->subject)
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        {{ $material->subject->name ?? '—' }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($material->published_at)
                                    <span class="badge bg-success">
                                        <i class="fas fa-eye me-1"></i>Publik
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-eye-slash me-1"></i>Draft
                                    </span>
                                @endif
                            </td>
                            <td class="text-center text-muted">
                                <i class="fas fa-download me-1 opacity-50"></i>
                                {{ number_format($material->downloads_count ?? 0) }}
                            </td>
                            <td class="text-muted">{{ $material->created_at->format('d/m/Y') }}</td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('admin.materials.show', $material) }}"
                                       class="btn btn-outline-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.materials.edit', $material) }}"
                                       class="btn btn-outline-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.materials.publish', $material) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-{{ $material->published_at ? 'secondary' : 'success' }} btn-sm"
                                                title="{{ $material->published_at ? 'Sembunyikan' : 'Publikasikan' }}">
                                            <i class="fas fa-{{ $material->published_at ? 'eye-slash' : 'check' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.materials.destroy', $material) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Hapus materi ini?')">
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
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-book fa-3x text-muted opacity-25 mb-3 d-block"></i>
                                <h6 class="text-muted">Belum ada materi pembelajaran</h6>
                                <a href="{{ route('admin.materials.create') }}" class="btn btn-primary btn-sm mt-2">
                                    <i class="fas fa-plus me-1"></i>Tambah Pertama
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($materials->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $materials->links() }}
        </div>
    @endif
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll   = document.getElementById('selectAll');
    const checkboxes  = document.querySelectorAll('.material-checkbox');
    const bulkBtn     = document.getElementById('bulkDeleteBtn');
    const searchInput = document.getElementById('searchInput');
    const statusFil   = document.getElementById('statusFilter');
    const rows        = document.querySelectorAll('.material-row');

    // Select all
    selectAll.addEventListener('change', function () {
        checkboxes.forEach(c => c.checked = this.checked);
        updateBulk();
    });
    checkboxes.forEach(c => c.addEventListener('change', updateBulk));

    function updateBulk() {
        const cnt = document.querySelectorAll('.material-checkbox:checked').length;
        bulkBtn.disabled = cnt === 0;
        bulkBtn.innerHTML = cnt > 0
            ? `<i class="fas fa-trash me-1"></i>Hapus (${cnt})`
            : '<i class="fas fa-trash me-1"></i>Hapus';
    }

    // Bulk delete
    bulkBtn.addEventListener('click', function () {
        const ids = Array.from(document.querySelectorAll('.material-checkbox:checked')).map(c => c.value);
        if (!ids.length || !confirm(`Hapus ${ids.length} materi?`)) return;

        fetch('{{ route("admin.materials.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ ids })
        }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
    });

    // Filter
    function filterRows() {
        const q  = searchInput.value.toLowerCase();
        const st = statusFil.value;

        rows.forEach(row => {
            const title  = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const rowSt  = row.dataset.status;
            const match  = (!q || title.includes(q)) && (!st || rowSt === st);
            row.style.display = match ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterRows);
    statusFil.addEventListener('change', filterRows);
});
</script>
@endpush
@endsection