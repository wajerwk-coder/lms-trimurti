@extends('layouts.admin')

@section('title', 'Manajemen Tugas')
@section('page-title', 'Manajemen Tugas & Quiz')
@section('page-subtitle', 'Kelola semua tugas yang dibuat oleh guru.')

@section('page-actions')
    <a href="{{ route('admin.assignments.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Tugas
    </a>
@endsection

@section('content')

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-tasks me-2 text-primary"></i>Daftar Tugas & Quiz</h6>
        <button type="button" class="btn btn-outline-danger btn-sm" id="bulkDeleteBtn" disabled>
            <i class="fas fa-trash me-1"></i>Hapus Terpilih
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small" id="assignmentsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width:40px;">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>Judul Tugas</th>
                        <th>Guru</th>
                        <th>Deadline</th>
                        <th class="text-center">Nilai Maks</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Submissions</th>
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
                                <div class="fw-semibold">{{ $assignment->title }}</div>
                                @if($assignment->description)
                                    <small class="text-muted">{{ Str::limit($assignment->description, 50) }}</small>
                                @endif
                            </td>
                            <td class="text-muted">{{ $assignment->guru?->name ?? '—' }}</td>
                            <td>
                                @if($assignment->deadline ?? $assignment->due_date)
                                    @php $dl = $assignment->deadline ?? $assignment->due_date; @endphp
                                    <div class="{{ $dl->isPast() ? 'text-danger fw-semibold' : 'text-muted' }}">
                                        {{ $dl->format('d/m/Y') }}
                                    </div>
                                    <small class="{{ $dl->isPast() ? 'text-danger' : 'text-muted' }}">
                                        {{ $dl->format('H:i') }}
                                        @if($dl->isPast()) <span class="badge bg-danger ms-1">Lewat</span> @endif
                                    </small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $assignment->max_score ?? 100 }}</td>
                            <td class="text-center">
                                @if($assignment->is_published)
                                    <span class="badge bg-success">Dipublikasikan</span>
                                @else
                                    <span class="badge bg-warning text-dark">Draft</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary">
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
                                        <i class="fas fa-{{ $assignment->is_published ? 'eye-slash' : 'eye' }}"></i>
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
                                <h6 class="text-muted">Tidak ada tugas</h6>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Hapus tugas ini? Semua submission terkait juga akan dihapus.</p>
                <p class="text-danger small"><i class="fas fa-info-circle me-1"></i>Tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Bulk Delete Modal --}}
<div class="modal fade" id="bulkDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold">Hapus Massal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Hapus <span id="selectedCount" class="fw-bold text-dark">0</span> tugas?</p>
                <p class="text-danger small">Tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="bulkDeleteForm" method="POST"
                      action="{{ route('admin.assignments.bulk-delete') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

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
@endsection