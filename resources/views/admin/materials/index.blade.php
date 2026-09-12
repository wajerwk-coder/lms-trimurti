@extends('layouts.admin')

@section('title', 'Materi Pembelajaran')
@section('page-title', 'Materi Pembelajaran')
@section('page-subtitle', 'Kelola semua materi pembelajaran dari guru')

@section('page-actions')
    <a href="{{ route('admin.materials.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Materi
    </a>
@endsection

@section('content')
<div class="container-fluid px-0">

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

    {{-- Stats ──────────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        @foreach([
            ['primary', 'fa-book',      $stats['total_materials']      ?? 0, 'Total Materi'],
            ['success', 'fa-eye',       $stats['published_materials']  ?? 0, 'Dipublikasikan'],
            ['warning', 'fa-eye-slash', $stats['unpublished_materials']?? 0, 'Disembunyikan'],
            ['info',    'fa-download',  $stats['total_downloads']      ?? 0, 'Total Unduhan'],
        ] as [$color, $icon, $val, $label])
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 p-2 bg-{{ $color }} bg-opacity-10 flex-shrink-0">
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

    {{-- Filter ─────────────────────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">Cari Materi</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control"
                               placeholder="Judul, guru, atau mata pelajaran…">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Status</label>
                    <select id="statusFilter" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="published">Dipublikasikan</option>
                        <option value="unpublished">Draft / Disembunyikan</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2 align-items-end">
                    <button type="button" id="bulkDeleteBtn"
                            class="btn btn-outline-danger btn-sm flex-fill" disabled>
                        <i class="fas fa-trash me-1"></i>Hapus
                        (<span id="selectedCount">0</span>)
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                            onclick="resetFilter()" title="Reset filter">
                        <i class="fas fa-undo"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel ─────────────────────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="fas fa-book me-2 text-primary"></i>Daftar Materi
            </h6>
            <span class="badge bg-secondary rounded-pill" id="materialCount">
                {{ $materials->total() }} materi
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small" id="materialsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" width="40">
                                <input type="checkbox" id="selectAll" class="form-check-input" title="Pilih semua">
                            </th>
                            <th>Judul Materi</th>
                            <th>Guru</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Unduhan</th>
                            <th>Tanggal Dibuat</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="materialsBody">
                        @forelse($materials as $material)
                        <tr class="material-row"
                            data-status="{{ $material->published_at ? 'published' : 'unpublished' }}"
                            data-search="{{ strtolower($material->title . ' ' . ($material->teacher?->name ?? $material->guru?->name ?? '') . ' ' . ($material->subject?->name ?? '')) }}">

                            {{-- Checkbox --}}
                            <td class="ps-4">
                                <input type="checkbox" class="form-check-input material-checkbox"
                                       value="{{ $material->id }}">
                            </td>

                            {{-- Judul --}}
                            <td style="max-width:260px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-2 bg-primary bg-opacity-10 d-flex align-items-center
                                                justify-content-center flex-shrink-0"
                                         style="width:36px;height:36px;">
                                        @php
                                            $ext = $material->file_url
                                                ? strtolower(pathinfo($material->file_url, PATHINFO_EXTENSION))
                                                : '';
                                            $fileIcon = match(true) {
                                                in_array($ext, ['pdf'])             => 'fa-file-pdf text-danger',
                                                in_array($ext, ['doc','docx'])      => 'fa-file-word text-primary',
                                                in_array($ext, ['ppt','pptx'])      => 'fa-file-powerpoint text-orange',
                                                in_array($ext, ['xls','xlsx'])      => 'fa-file-excel text-success',
                                                in_array($ext, ['zip','rar'])       => 'fa-file-archive text-secondary',
                                                $material->video_url !== null       => 'fa-video text-danger',
                                                default                             => 'fa-file-alt text-primary',
                                            };
                                        @endphp
                                        <i class="fas {{ $fileIcon }} fa-sm"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-semibold text-dark text-truncate"
                                             style="max-width:200px;" title="{{ $material->title }}">
                                            {{ $material->title }}
                                        </div>
                                        @if($material->content)
                                            <small class="text-muted text-truncate d-block"
                                                   style="max-width:200px;">
                                                {{ Str::limit(strip_tags($material->content), 55) }}
                                            </small>
                                        @elseif($material->video_url)
                                            <small class="text-muted">
                                                <i class="fas fa-video me-1"></i>Video tersedia
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Guru --}}
                            <td>
                                @php $guruName = $material->teacher?->name ?? $material->guru?->name ?? null; @endphp
                                @if($guruName)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-teal bg-opacity-10 d-flex align-items-center
                                                    justify-content-center flex-shrink-0 text-white fw-bold"
                                             style="width:28px;height:28px;background:#0f766e;font-size:.7rem;">
                                            {{ strtoupper(substr($guruName, 0, 1)) }}
                                        </div>
                                        <span class="text-dark small">{{ $guruName }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Mata Pelajaran --}}
                            <td>
                                @if($material->subject)
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">
                                        {{ $material->subject->name }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Kelas --}}
                            <td>
                                @if($material->kelas)
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                        {{ $material->kelas->name }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="text-center">
                                @if($material->published_at)
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        <i class="fas fa-eye me-1" style="font-size:.65rem;"></i>Publik
                                    </span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                        <i class="fas fa-eye-slash me-1" style="font-size:.65rem;"></i>Draft
                                    </span>
                                @endif
                            </td>

                            {{-- Unduhan --}}
                            <td class="text-center">
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-download me-1" style="font-size:.65rem;"></i>
                                    {{ number_format($material->downloads_count ?? 0) }}
                                </span>
                            </td>

                            {{-- Tanggal --}}
                            <td class="text-muted small" style="white-space:nowrap;">
                                <div>{{ $material->created_at->format('d M Y') }}</div>
                                <div style="font-size:.7rem;" class="text-muted">
                                    {{ $material->created_at->diffForHumans() }}
                                </div>
                            </td>

                            {{-- Aksi --}}
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center flex-wrap">
                                    <a href="{{ route('admin.materials.show', $material) }}"
                                       class="btn btn-outline-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.materials.edit', $material) }}"
                                       class="btn btn-outline-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.materials.publish', $material) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-outline-{{ $material->published_at ? 'secondary' : 'success' }} btn-sm"
                                                title="{{ $material->published_at ? 'Sembunyikan' : 'Publikasikan' }}">
                                            <i class="fas fa-{{ $material->published_at ? 'eye-slash' : 'check' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.materials.destroy', $material) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Hapus materi \'{{ addslashes($material->title) }}\'? Tindakan tidak dapat dibatalkan.')">
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
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-book fa-3x text-muted opacity-25 mb-3 d-block"></i>
                                <h6 class="text-muted">Belum ada materi pembelajaran</h6>
                                <p class="text-muted small mb-3">Materi dapat ditambahkan oleh admin atau oleh guru.</p>
                                <a href="{{ route('admin.materials.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i>Tambah Materi
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($materials->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $materials->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll   = document.getElementById('selectAll');
    const bulkBtn     = document.getElementById('bulkDeleteBtn');
    const cntEl       = document.getElementById('selectedCount');
    const searchInput = document.getElementById('searchInput');
    const statusFil   = document.getElementById('statusFilter');
    const rows        = document.querySelectorAll('.material-row');
    const countBadge  = document.getElementById('materialCount');

    // ── Checkbox select-all ───────────────────────────────────────────────
    selectAll.addEventListener('change', function () {
        const visible = document.querySelectorAll('.material-row:not([style*="display: none"]) .material-checkbox');
        visible.forEach(c => c.checked = this.checked);
        updateBulk();
    });

    document.querySelectorAll('.material-checkbox').forEach(function (c) {
        c.addEventListener('change', updateBulk);
    });

    function updateBulk() {
        const cnt = document.querySelectorAll('.material-checkbox:checked').length;
        bulkBtn.disabled = cnt === 0;
        if (cntEl) cntEl.textContent = cnt;
    }

    // ── Bulk delete ───────────────────────────────────────────────────────
    bulkBtn.addEventListener('click', function () {
        const ids = Array.from(document.querySelectorAll('.material-checkbox:checked'))
                        .map(c => c.value);
        if (!ids.length) return;
        if (!confirm('Hapus ' + ids.length + ' materi yang dipilih?\nTindakan tidak dapat dibatalkan.')) return;

        fetch('{{ route("admin.materials.bulk-delete") }}', {
            method  : 'POST',
            headers : {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body    : JSON.stringify({ ids }),
        })
        .then(r => r.json())
        .then(d => { if (d.success) location.reload(); })
        .catch(() => alert('Terjadi kesalahan. Silakan coba lagi.'));
    });

    // ── Filter ────────────────────────────────────────────────────────────
    function doFilter() {
        const q  = searchInput.value.toLowerCase().trim();
        const st = statusFil.value;
        let visible = 0;

        rows.forEach(function (row) {
            const search = row.dataset.search ?? '';
            const rowSt  = row.dataset.status;
            const show   = (!q || search.includes(q)) && (!st || rowSt === st);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (countBadge) countBadge.textContent = visible + ' materi';
    }

    searchInput.addEventListener('input', doFilter);
    statusFil.addEventListener('change', doFilter);
});

function resetFilter() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.querySelectorAll('.material-row').forEach(r => r.style.display = '');
    const badge = document.getElementById('materialCount');
    if (badge) badge.textContent = '{{ $materials->total() }} materi';
}
</script>
@endpush
