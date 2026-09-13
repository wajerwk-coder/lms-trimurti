@extends('layouts.admin')

@section('title', 'Manajemen Tugas & Quiz')
@section('page-title', 'Manajemen Tugas & Quiz')
@section('page-subtitle', 'Kelola semua tugas dan kuis yang dibuat oleh guru')

@section('page-actions')
    <a href="{{ route('admin.assignments.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Tugas
    </a>
@endsection

@section('content')

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

{{-- ── Stats ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 p-2 bg-primary bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-tasks text-primary fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ $statsAll->total ?? 0 }}</div>
                    <small class="text-muted">Total Tugas</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 p-2 bg-success bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-eye text-success fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ $statsAll->published ?? 0 }}</div>
                    <small class="text-muted">Dipublikasikan</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 p-2 bg-warning bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-file-alt text-warning fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ $statsAll->draft ?? 0 }}</div>
                    <small class="text-muted">Draft</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 p-2 bg-info bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-paper-plane text-info fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ $totalSubs ?? 0 }}</div>
                    <small class="text-muted">Total Pengumpulan</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Filter ── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.assignments.index') }}"
              class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">Cari Tugas</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Judul tugas..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Guru</label>
                <select name="guru_id" class="form-select form-select-sm">
                    <option value="">Semua Guru</option>
                    @foreach($guruList as $g)
                        <option value="{{ $g->id }}" {{ request('guru_id') == $g->id ? 'selected' : '' }}>
                            {{ $g->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Dipublikasikan</option>
                    <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
                <a href="{{ route('admin.assignments.index') }}"
                   class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── Tabel ── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-semibold">
                <i class="fas fa-clipboard-list me-2 text-primary"></i>Daftar Tugas & Quiz
            </h6>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary rounded-pill">
                    {{ $assignments->total() }} tugas
                </span>
                <button type="button" class="btn btn-outline-danger btn-sm" id="bulkDeleteBtn" disabled>
                    <i class="fas fa-trash me-1"></i>Hapus Terpilih
                </button>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small" id="assignmentsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" width="40">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>Judul Tugas</th>
                        <th>Guru</th>
                        <th>Mata Pelajaran / Kelas</th>
                        <th>Deadline</th>
                        <th class="text-center">Nilai Maks</th>
                        <th class="text-center">Pengumpulan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                    @php
                        $dl        = $assignment->deadline ?? $assignment->due_date ?? null;
                        $dlCarbon  = $dl ? \Carbon\Carbon::parse($dl) : null;
                        $isExpired = $dlCarbon?->isPast();
                        $subCount  = $assignment->submissions->count();
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <input type="checkbox" class="form-check-input assignment-checkbox"
                                   value="{{ $assignment->id }}">
                        </td>

                        {{-- Judul --}}
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-2 bg-primary bg-opacity-10 d-flex align-items-center
                                            justify-content-center flex-shrink-0"
                                     style="width:36px;height:36px;">
                                    <i class="fas fa-clipboard-list text-primary fa-sm"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">
                                        {{ Str::limit($assignment->title, 50) }}
                                    </div>
                                    @if($assignment->description)
                                        <small class="text-muted">
                                            {{ Str::limit(strip_tags($assignment->description), 55) }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Guru --}}
                        <td>
                            @if($assignment->guru)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-teal bg-opacity-10 d-flex align-items-center
                                                justify-content-center flex-shrink-0"
                                         style="width:28px;height:28px;background:#e0f2f1;">
                                        <i class="fas fa-chalkboard-teacher text-success"
                                           style="font-size:.7rem;"></i>
                                    </div>
                                    <span class="text-dark small">{{ $assignment->guru->name }}</span>
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- Mapel / Kelas --}}
                        <td>
                            <div class="d-flex flex-column gap-1">
                                @if($assignment->subject)
                                    <span class="badge bg-primary bg-opacity-10 text-primary"
                                          style="font-size:.7rem;">
                                        <i class="fas fa-book me-1"></i>{{ $assignment->subject->name }}
                                    </span>
                                @endif
                                @if($assignment->kelas)
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary"
                                          style="font-size:.7rem;">
                                        <i class="fas fa-school me-1"></i>{{ $assignment->kelas->name }}
                                    </span>
                                @endif
                                @if(!$assignment->subject && !$assignment->kelas)
                                    <span class="text-muted small">—</span>
                                @endif
                            </div>
                        </td>

                        {{-- Deadline --}}
                        <td>
                            @if($dlCarbon)
                                <div class="{{ $isExpired ? 'text-danger fw-semibold' : 'text-dark' }} small">
                                    <i class="fas fa-calendar{{ $isExpired ? '-times' : '-alt' }} me-1"></i>
                                    {{ $dlCarbon->format('d M Y') }}
                                </div>
                                <div class="small {{ $isExpired ? 'text-danger' : 'text-muted' }}">
                                    {{ $dlCarbon->format('H:i') }} WIT
                                    @if($isExpired)
                                        <span class="badge bg-danger ms-1" style="font-size:.6rem;">Lewat</span>
                                    @elseif($dlCarbon->diffInDays(now()) <= 2)
                                        <span class="badge bg-warning text-dark ms-1"
                                              style="font-size:.6rem;">Segera</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>

                        {{-- Nilai Maks --}}
                        <td class="text-center">
                            <span class="badge bg-light text-dark border fw-semibold">
                                {{ $assignment->max_score ?? 100 }}
                            </span>
                        </td>

                        {{-- Pengumpulan --}}
                        <td class="text-center">
                            <div class="d-flex flex-column align-items-center">
                                <span class="badge bg-info bg-opacity-10 text-info fw-bold"
                                      style="font-size:.8rem;">
                                    {{ $subCount }}
                                </span>
                                @if($subCount > 0)
                                    <small class="text-muted" style="font-size:.65rem;">pengumpulan</small>
                                @endif
                            </div>
                        </td>

                        {{-- Status --}}
                        <td class="text-center">
                            @if($assignment->is_published)
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-circle me-1" style="font-size:6px;vertical-align:middle;"></i>
                                    Publik
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning">
                                    <i class="fas fa-circle me-1" style="font-size:6px;vertical-align:middle;"></i>
                                    Draft
                                </span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="text-center pe-4">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('admin.assignments.show', $assignment) }}"
                                   class="btn btn-outline-info btn-sm" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.assignments.edit', $assignment) }}"
                                   class="btn btn-outline-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button"
                                        class="btn btn-outline-{{ $assignment->is_published ? 'secondary' : 'success' }} btn-sm"
                                        onclick="togglePublish({{ $assignment->id }})"
                                        title="{{ $assignment->is_published ? 'Sembunyikan' : 'Publikasikan' }}">
                                    <i class="fas fa-{{ $assignment->is_published ? 'eye-slash' : 'check' }}"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                        onclick="deleteAssignment({{ $assignment->id }}, '{{ addslashes($assignment->title) }}')"
                                        title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="fas fa-tasks fa-3x text-muted opacity-25 mb-3 d-block"></i>
                            <h6 class="text-muted">
                                @if(request()->hasAny(['search','status','guru_id']))
                                    Tidak ada tugas yang cocok dengan filter
                                @else
                                    Belum ada tugas
                                @endif
                            </h6>
                            @if(request()->hasAny(['search','status','guru_id']))
                                <a href="{{ route('admin.assignments.index') }}"
                                   class="btn btn-outline-secondary btn-sm mt-2">
                                    <i class="fas fa-undo me-1"></i>Reset Filter
                                </a>
                            @else
                                <a href="{{ route('admin.assignments.create') }}"
                                   class="btn btn-primary btn-sm mt-2">
                                    <i class="fas fa-plus me-1"></i>Tambah Pertama
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($assignments->hasPages())
    <div class="card-footer bg-white border-top py-3">
        {{ $assignments->links() }}
    </div>
    @endif
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-semibold">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Konfirmasi Hapus
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-2">
                <p class="text-muted small mb-1">
                    Hapus tugas <strong id="deleteTitle" class="text-dark"></strong>?
                </p>
                <p class="text-danger small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Semua pengumpulan terkait juga akan dihapus dan tidak dapat dibatalkan.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash me-1"></i>Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Bulk Delete Modal --}}
<div class="modal fade" id="bulkDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-semibold">Hapus Massal</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-2">
                <p class="text-muted small mb-1">
                    Hapus <span id="selectedCount" class="fw-bold text-dark">0</span> tugas yang dipilih?
                </p>
                <p class="text-danger small mb-0">Tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Batal</button>
                <form id="bulkDeleteForm" method="POST"
                      action="{{ route('admin.assignments.bulk-delete') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash me-1"></i>Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll  = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.assignment-checkbox');
    const bulkBtn    = document.getElementById('bulkDeleteBtn');
    const cntEl      = document.getElementById('selectedCount');

    function updateBulk() {
        const cnt = document.querySelectorAll('.assignment-checkbox:checked').length;
        bulkBtn.disabled = cnt === 0;
        if (cntEl) cntEl.textContent = cnt;
        selectAll.indeterminate = cnt > 0 && cnt < checkboxes.length;
        selectAll.checked = cnt > 0 && cnt === checkboxes.length;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(c => c.checked = this.checked);
            updateBulk();
        });
    }
    checkboxes.forEach(c => c.addEventListener('change', updateBulk));

    const bulkBtnEl = document.getElementById('bulkDeleteBtn');
    if (bulkBtnEl) {
        bulkBtnEl.addEventListener('click', function () {
            const checked = document.querySelectorAll('.assignment-checkbox:checked');
            if (!checked.length) return;
            const form = document.getElementById('bulkDeleteForm');
            form.querySelectorAll('input[name="assignment_ids[]"]').forEach(i => i.remove());
            checked.forEach(c => {
                const inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'assignment_ids[]'; inp.value = c.value;
                form.appendChild(inp);
            });
            new bootstrap.Modal(document.getElementById('bulkDeleteModal')).show();
        });
    }
});

function deleteAssignment(id, title) {
    document.getElementById('deleteForm').action =
        '{{ route("admin.assignments.destroy", ":id") }}'.replace(':id', id);
    const titleEl = document.getElementById('deleteTitle');
    if (titleEl) titleEl.textContent = '"' + title + '"';
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function togglePublish(id) {
    if (!confirm('Ubah status publikasi tugas ini?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.assignments.publish", ":id") }}'.replace(':id', id);
    const csrf = document.createElement('input');
    csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
