@extends('layouts.admin')

@section('title', 'Manajemen Praktikum')
@section('page-title', 'Manajemen Praktikum')
@section('page-subtitle', 'Kelola semua praktikum yang dibuat oleh guru.')

@section('page-actions')
    <a href="{{ route('admin.practicals.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Praktikum
    </a>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-primary bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-flask text-primary fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ $practicals->total() }}</div>
                    <small class="text-muted">Total Praktikum</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 flex-shrink-0">
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
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-warning bg-opacity-10 flex-shrink-0">
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
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-info bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-star text-info fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ $totalPenilaian }}</div>
                    <small class="text-muted">Total Penilaian</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-flask me-2 text-primary"></i>Daftar Praktikum</h6>
        <div class="d-flex gap-2">
            <button type="button" id="bulkDeleteBtn" class="btn btn-outline-danger btn-sm" disabled>
                <i class="fas fa-trash me-1"></i>Hapus Terpilih
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width:40px;">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>Judul</th>
                        <th>Guru</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Batas Waktu</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Penilaian</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($practicals as $practical)
                        <tr>
                            <td class="ps-4">
                                <input type="checkbox" class="form-check-input practical-checkbox" value="{{ $practical->id }}">
                            </td>
                            <td>
                                <div class="fw-semibold">{{ Str::limit($practical->title, 45) }}</div>
                                @if($practical->description)
                                    <small class="text-muted">{{ Str::limit($practical->description, 60) }}</small>
                                @endif
                            </td>
                            <td class="text-muted">{{ $practical->guru?->name ?? '—' }}</td>
                            <td>
                                @if($practical->subject)
                                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ $practical->subject->name ?? '—' }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $practical->kelas?->name ?? 'Semua' }}</td>
                            <td>
                                @if($practical->due_date)
                                    <div class="{{ \Carbon\Carbon::parse($practical->due_date)->isPast() ? 'text-danger' : 'text-dark' }}">
                                        {{ \Carbon\Carbon::parse($practical->due_date)->format('d/m/Y') }}
                                    </div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($practical->due_date)->format('H:i') }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($practical->published_at || $practical->is_published)
                                    <span class="badge bg-success"><i class="fas fa-eye me-1"></i>Publik</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="fas fa-file me-1"></i>Draft</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info bg-opacity-10 text-info">{{ $practical->scores->count() }}</span>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('admin.practicals.show', $practical) }}"
                                       class="btn btn-outline-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.practicals.edit', $practical) }}"
                                       class="btn btn-outline-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-outline-{{ ($practical->published_at || $practical->is_published) ? 'secondary' : 'success' }} btn-sm"
                                            onclick="togglePublish({{ $practical->id }})"
                                            title="{{ ($practical->published_at || $practical->is_published) ? 'Sembunyikan' : 'Publikasikan' }}">
                                        <i class="fas fa-{{ ($practical->published_at || $practical->is_published) ? 'eye-slash' : 'check' }}"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                            onclick="deletePractical({{ $practical->id }})" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-flask fa-3x text-muted opacity-25 mb-3 d-block"></i>
                                <h6 class="text-muted">Belum ada praktikum</h6>
                                <a href="{{ route('admin.practicals.create') }}" class="btn btn-primary btn-sm mt-2">
                                    <i class="fas fa-plus me-1"></i>Tambah Pertama
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($practicals->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $practicals->links() }}
        </div>
    @endif
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-muted">Hapus praktikum ini? Semua penilaian terkait juga akan dihapus. Tindakan tidak dapat dibatalkan.</div>
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
            <div class="modal-body text-muted">Hapus <strong id="selectedCount">0</strong> praktikum terpilih? Tidak dapat dibatalkan.</div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="bulkDeleteForm" method="POST" action="{{ route('admin.practicals.bulk-delete') }}" class="d-inline">
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
    const checkboxes = document.querySelectorAll('.practical-checkbox');
    const bulkBtn    = document.getElementById('bulkDeleteBtn');

    function updateBulk() {
        const cnt = document.querySelectorAll('.practical-checkbox:checked').length;
        bulkBtn.disabled = cnt === 0;
        const el = document.getElementById('selectedCount');
        if (el) el.textContent = cnt;
        selectAll.indeterminate = cnt > 0 && cnt < checkboxes.length;
        selectAll.checked = cnt > 0 && cnt === checkboxes.length;
    }

    selectAll.addEventListener('change', function () {
        checkboxes.forEach(c => c.checked = this.checked);
        updateBulk();
    });
    checkboxes.forEach(c => c.addEventListener('change', updateBulk));

    bulkBtn.addEventListener('click', function () {
        const checked = document.querySelectorAll('.practical-checkbox:checked');
        const form = document.getElementById('bulkDeleteForm');
        form.querySelectorAll('input[name="practical_ids[]"]').forEach(i => i.remove());
        checked.forEach(c => {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'practical_ids[]'; inp.value = c.value;
            form.appendChild(inp);
        });
        new bootstrap.Modal(document.getElementById('bulkDeleteModal')).show();
    });
});

function deletePractical(id) {
    document.getElementById('deleteForm').action = '{{ route("admin.practicals.destroy", ":id") }}'.replace(':id', id);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function togglePublish(id) {
    if (!confirm('Ubah status publikasi praktikum ini?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.practicals.toggle-publish", ":id") }}'.replace(':id', id);
    const csrf = document.createElement('input');
    csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
@endsection