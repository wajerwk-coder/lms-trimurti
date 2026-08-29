@extends('layouts.admin')

@section('title', 'Manajemen Absensi')
@section('page-title', 'Manajemen Absensi')
@section('page-subtitle', 'Pantau dan kelola data kehadiran siswa.')

@section('page-actions')
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-warning btn-sm" id="bulkUpdateBtn" disabled>
            <i class="fas fa-edit me-1"></i>Update Terpilih
        </button>
        <a href="{{ route('admin.attendance.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Tambah Absensi
        </a>
    </div>
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
@isset($error)
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endisset

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="h3 fw-bold text-primary mb-0">{{ $stats['total'] ?? 0 }}</div>
                <small class="text-muted">Total</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="h3 fw-bold text-success mb-0">{{ $stats['hadir'] ?? 0 }}</div>
                <small class="text-muted">Hadir</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="h3 fw-bold text-info mb-0">{{ $stats['izin'] ?? 0 }}</div>
                <small class="text-muted">Izin</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="h3 fw-bold text-warning mb-0">{{ $stats['sakit'] ?? 0 }}</div>
                <small class="text-muted">Sakit</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="h3 fw-bold text-danger mb-0">{{ $stats['alpha'] ?? 0 }}</div>
                <small class="text-muted">Alpa</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="h3 fw-bold text-purple mb-0" style="color:#6f42c1;">{{ $stats['attendance_rate'] ?? 0 }}%</div>
                <small class="text-muted">Kehadiran</small>
            </div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.attendance.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        @foreach(['hadir'=>'Hadir','izin'=>'Izin','sakit'=>'Sakit','alpha'=>'Alpa'] as $val => $label)
                            <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Siswa</label>
                    <select name="siswa_id" class="form-select">
                        <option value="">Semua Siswa</option>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}" {{ request('siswa_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">
            <i class="fas fa-calendar-check me-2 text-primary"></i>Data Absensi
        </h6>
        <span class="badge bg-secondary">{{ method_exists($attendances, 'total') ? $attendances->total() : $attendances->count() }} record</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width:40px;">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>Siswa</th>
                        <th>Tanggal</th>
                        <th class="text-center">Status</th>
                        <th>Waktu Masuk</th>
                        <th>Waktu Keluar</th>
                        <th>Keterangan</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                        <tr class="attendance-row">
                            <td class="ps-4">
                                <input type="checkbox" class="form-check-input attendance-checkbox" value="{{ $attendance->id }}">
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $attendance->siswa?->name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $attendance->siswa?->email ?? '' }}</small>
                            </td>
                            <td>
                                <div>{{ optional($attendance->date ?? $attendance->tanggal)->format('d M Y') ?? '—' }}</div>
                                <small class="text-muted">{{ optional($attendance->date ?? $attendance->tanggal)->translatedFormat('l') ?? '' }}</small>
                            </td>
                            <td class="text-center">
                                @php
                                    $st = strtolower($attendance->status ?? '');
                                    $stColor = in_array($st,['hadir','present']) ? 'success' : ($st === 'izin' ? 'info' : ($st === 'sakit' ? 'warning' : 'danger'));
                                    $stLabel = in_array($st,['hadir','present']) ? 'Hadir' : ($st === 'izin' ? 'Izin' : ($st === 'sakit' ? 'Sakit' : 'Alpa'));
                                @endphp
                                <span class="badge bg-{{ $stColor }}">{{ $stLabel }}</span>
                            </td>
                            <td class="text-muted">{{ $attendance->waktu_masuk ?? '—' }}</td>
                            <td class="text-muted">{{ $attendance->waktu_keluar ?? '—' }}</td>
                            <td class="text-muted">{{ $attendance->note ?? $attendance->keterangan ?? '—' }}</td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('admin.attendance.show', $attendance) }}"
                                       class="btn btn-outline-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.attendance.edit', $attendance) }}"
                                       class="btn btn-outline-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                            onclick="deleteAttendance({{ $attendance->id }})" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted opacity-25 mb-3 d-block"></i>
                                <h6 class="text-muted">Tidak ada data absensi</h6>
                                <a href="{{ route('admin.attendance.create') }}" class="btn btn-primary btn-sm mt-2">
                                    <i class="fas fa-plus me-1"></i>Tambah Absensi
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(method_exists($attendances, 'hasPages') && $attendances->hasPages())
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Menampilkan {{ $attendances->firstItem() }}–{{ $attendances->lastItem() }} dari {{ $attendances->total() }}
            </small>
            {{ $attendances->links() }}
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
                <p class="text-muted mb-1">Apakah Anda yakin ingin menghapus data absensi ini?</p>
                <p class="text-danger small mb-0"><i class="fas fa-info-circle me-1"></i>Tindakan ini tidak dapat dibatalkan.</p>
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

{{-- Bulk Update Modal --}}
<div class="modal fade" id="bulkUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold">
                    <i class="fas fa-edit text-warning me-2"></i>Update Massal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">
                    Update <span id="selectedCount" class="fw-bold text-dark">0</span> data absensi yang dipilih
                </p>
                <form id="bulkUpdateForm" method="POST" action="{{ route('admin.attendance.bulk-update') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Status Baru</label>
                        <select name="status" class="form-select" required>
                            <option value="">Pilih Status</option>
                            <option value="hadir">Hadir</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="alpha">Alpa</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Keterangan</label>
                        <textarea name="note" rows="3" class="form-control"
                                  placeholder="Keterangan (opsional)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="bulkUpdateForm" class="btn btn-warning">Update</button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll  = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.attendance-checkbox');
    const bulkBtn    = document.getElementById('bulkUpdateBtn');
    const cntEl      = document.getElementById('selectedCount');

    function updateBulk() {
        const cnt = document.querySelectorAll('.attendance-checkbox:checked').length;
        bulkBtn.disabled = cnt === 0;
        if (cntEl) cntEl.textContent = cnt;
        selectAll.indeterminate = cnt > 0 && cnt < checkboxes.length;
        selectAll.checked = cnt === checkboxes.length && checkboxes.length > 0;
    }

    selectAll.addEventListener('change', function () {
        checkboxes.forEach(c => c.checked = this.checked);
        updateBulk();
    });
    checkboxes.forEach(c => c.addEventListener('change', updateBulk));

    bulkBtn.addEventListener('click', function () {
        const checked = document.querySelectorAll('.attendance-checkbox:checked');
        if (!checked.length) return;

        const form = document.getElementById('bulkUpdateForm');
        form.querySelectorAll('input[name="attendance_ids[]"]').forEach(i => i.remove());
        checked.forEach(c => {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'attendance_ids[]'; inp.value = c.value;
            form.appendChild(inp);
        });

        new bootstrap.Modal(document.getElementById('bulkUpdateModal')).show();
    });
});

function deleteAttendance(id) {
    document.getElementById('deleteForm').action =
        '{{ route("admin.attendance.destroy", ":id") }}'.replace(':id', id);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
@endsection