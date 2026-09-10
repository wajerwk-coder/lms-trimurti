@extends('layouts.admin')

@section('title', 'Manajemen Tugas')
@section('page-title', 'Manajemen Tugas & Quiz')
@section('page-subtitle', 'Kelola semua tugas yang dibuat oleh guru')

@section('page-actions')
    <a href="{{ route('admin.assignments.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Tugas
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

    {{-- Stats --}}
    @php
        $totalTugas    = $assignments->total();
        $totalPublished = $assignments->getCollection()->where('is_published', true)->count();
        $totalDraft     = $assignments->getCollection()->where('is_published', false)->count();
        $totalSubs      = $assignments->getCollection()->sum(fn($a) => $a->submissions->count());
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 p-2 bg-primary bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-tasks text-primary fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ $totalTugas }}</div>
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
                        <div class="h4 fw-bold mb-0">{{ $totalPublished }}</div>
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
                        <div class="h4 fw-bold mb-0">{{ $totalDraft }}</div>
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
                        <div class="h4 fw-bold mb-0">{{ $totalSubs }}</div>
                        <small class="text-muted">Pengumpulan</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="fas fa-tasks me-2 text-primary"></i>Daftar Tugas & Quiz
            </h6>
            <button type="button" class="btn btn-outline-danger btn-sm" id="bulkDeleteBtn" disabled>
                <i class="fas fa-trash me-1"></i>Hapus Terpilih
            </button>
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
                            <th>Deadline</th>
                            <th class="text-center">Nilai Maks</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Pengumpulan</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                        <tr>
                            <td class="ps-4">
                                <input type="checkbox" class="form-check-input assignment-checkbox"
                                       value="{{ $assignment->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-2 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:34px;height:34px;">
                                        <i class="fas fa-clipboard-list text-primary fa-sm"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $assignment->title }}</div>
                                        @if($assignment->description)
                                            <small class="text-muted">{{ Str::limit($assignment->description, 55) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted">{{ $assignment->guru?->name ?? '—' }}</td>
                            <td>
                                @php $dl = $assignment->deadline ?? $assignment->due_date ?? null; @endphp
                                @if($dl)
                                    @php $dlCarbon = \Carbon\Carbon::parse($dl); @endphp
                                    <div class="{{ $dlCarbon->isPast() ? 'text-danger fw-semibold' : 'text-dark' }}">
                                        {{ $dlCarbon->format('d M Y') }}
                                    </div>
                                    <small class="{{ $dlCarbon->isPast() ? 'text-danger' : 'text-muted' }}">
                                        {{ $dlCarbon->format('H:i') }}
                                        @if($dlCarbon->isPast())
                                            <span class="badge bg-danger ms-1" style="font-size:9px;">Lewat</span>
                                        @endif
                                    </small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center fw-semibold">{{ $assignment->max_score ?? 100 }}</td>
                            <td class="text-center">
                                @if($assignment->is_published)
                                    <span class="badge bg-success bg-opacity-15 text-success">
                                        <i class="fas fa-eye me-1"></i>Publik
                                    </span>
                                @else
                                    <span class="badge bg-warning bg-opacity-15 text-warning">
                                        <i class="fas fa-file me-1"></i>Draft
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info bg-opacity-10 text-info fw-semibold">
                                    {{ $assignment->submissions->count() }}
                                </span>
                            </td>
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
                                            onclick="deleteAssignment({{ $assignment->id }})" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-tasks fa-3x text-muted opacity-25 mb-3 d-block"></i>
                                <h6 class="text-muted">Belum ada tugas</h6>
                                <a href="{{ route('admin.assignments.create') }}" class="btn btn-primary btn-sm mt-2">
                                    <i class="fas fa-plus me-1"></i>Tambah Pertama
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($assignments->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $assignments->links() }}
        </div>
        @endif
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-semibold">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>Hapus Tugas
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-2">
                    <p class="text-muted small mb-1">Hapus tugas ini? Semua submission terkait juga akan dihapus.</p>
                    <p class="text-danger small mb-0"><i class="fas fa-info-circle me-1"></i>Tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <form id="deleteForm" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Bulk Delete Modal --}}
    <div class="modal fade" id="bulkDeleteModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-semibold">Hapus Massal</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-2">
                    <p class="text-muted small mb-1">Hapus <span id="selectedCount" class="fw-bold text-dark">0</span> tugas yang dipilih?</p>
                    <p class="text-danger small mb-0">Tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <form id="bulkDeleteForm" method="POST"
                          action="{{ route('admin.assignments.bulk-delete') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </div>
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

    selectAll.addEventListener('change', function () {
        checkboxes.forEach(c => c.checked = this.checked);
        updateBulk();
    });
    checkboxes.forEach(c => c.addEventListener('change', updateBulk));

    bulkBtn.addEventListener('click', function () {
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
});

function deleteAssignment(id) {
    document.getElementById('deleteForm').action =
        '{{ route("admin.assignments.destroy", ":id") }}'.replace(':id', id);
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
