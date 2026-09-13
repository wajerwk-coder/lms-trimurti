@extends('layouts.admin')

@section('title', 'Manajemen Absensi')
@section('page-title', 'Manajemen Absensi')
@section('page-subtitle', 'Pantau dan kelola data kehadiran siswa')

@section('page-actions')
<div class="d-flex gap-2 flex-wrap">
    <button type="button" class="btn btn-outline-warning btn-sm" id="bulkUpdateBtn" disabled>
        <i class="fas fa-edit me-1"></i>Update Terpilih
    </button>
    <a href="{{ route('admin.attendance.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Absensi
    </a>
</div>
@endsection

@section('content')
<div class="container-fluid px-0">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @isset($error)
        <div class="alert alert-warning alert-dismissible fade show mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endisset

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        @foreach([
            ['primary',  'fa-calendar-check', $stats['total']   ?? 0, 'Total Absensi'],
            ['success',  'fa-user-check',      $stats['hadir']  ?? 0, 'Hadir'],
            ['info',     'fa-clock',           $stats['izin']   ?? 0, 'Izin'],
            ['warning',  'fa-heartbeat',       $stats['sakit']  ?? 0, 'Sakit'],
            ['danger',   'fa-user-times',      $stats['alpha']  ?? 0, 'Alpa'],
            ['secondary','fa-percentage',      ($stats['attendance_rate'] ?? 0) . '%', 'Kehadiran'],
        ] as [$color, $icon, $val, $label])
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3 px-2">
                    <div class="rounded-3 p-2 bg-{{ $color }} bg-opacity-10 d-inline-flex mb-2">
                        <i class="fas {{ $icon }} text-{{ $color }}"></i>
                    </div>
                    <div class="h4 fw-bold mb-0 text-{{ $color }}">{{ $val }}</div>
                    <small class="text-muted">{{ $label }}</small>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.attendance.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                               class="form-control form-control-sm">
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold mb-1">Tanggal Akhir</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                               class="form-control form-control-sm">
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label small fw-semibold mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach(['hadir'=>'Hadir','izin'=>'Izin','sakit'=>'Sakit','alpha'=>'Alpa'] as $val => $lbl)
                                <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label small fw-semibold mb-1">Siswa</label>
                        <select name="siswa_id" class="form-select form-select-sm">
                            <option value="">Semua Siswa</option>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}" {{ request('siswa_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-12 col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="fas fa-calendar-check me-2 text-primary"></i>Data Absensi
            </h6>
            <span class="badge bg-secondary">
                {{ method_exists($attendances, 'total') ? $attendances->total() : $attendances->count() }} record
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" width="40">
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
                        @php
                            $st      = strtolower($attendance->status ?? '');
                            $stColor = in_array($st, ['hadir','present']) ? 'success'
                                : ($st === 'izin'  ? 'info'
                                : ($st === 'sakit' ? 'warning'
                                : 'danger'));
                            $stLabel = in_array($st, ['hadir','present']) ? 'Hadir'
                                : ($st === 'izin'  ? 'Izin'
                                : ($st === 'sakit' ? 'Sakit'
                                : 'Alpa'));
                            $tgl = $attendance->date ?? $attendance->tanggal ?? null;
                        @endphp
                        <tr class="attendance-row">
                            <td class="ps-4">
                                <input type="checkbox" class="form-check-input attendance-checkbox"
                                       value="{{ $attendance->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-{{ $stColor }} bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:32px;height:32px;">
                                        <i class="fas fa-user text-{{ $stColor }} fa-xs"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $attendance->siswa?->name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $attendance->siswa?->email ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($tgl)
                                    <div class="fw-semibold">{{ \Carbon\Carbon::parse($tgl)->format('d M Y') }}</div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($tgl)->translatedFormat('l') }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $stColor }} bg-opacity-10 text-{{ $stColor }}">
                                    {{ $stLabel }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $attendance->waktu_masuk ?? '—' }}</td>
                            <td class="text-muted">{{ $attendance->waktu_keluar ?? '—' }}</td>
                            <td class="text-muted" style="max-width:140px;">
                                {{ Str::limit($attendance->note ?? $attendance->keterangan ?? '—', 40) }}
                            </td>
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
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-muted">
                Menampilkan {{ $attendances->firstItem() }}–{{ $attendances->lastItem() }}
                dari {{ $attendances->total() }} record
            </small>
            {{ $attendances->links() }}
        </div>
        @endif
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-semibold">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>Hapus Absensi
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-2">
                    <p class="text-muted small mb-1">Hapus data absensi ini?</p>
                    <p class="text-danger small mb-0">
                        <i class="fas fa-info-circle me-1"></i>Tidak dapat dibatalkan.
                    </p>
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

    {{-- Bulk Update Modal --}}
    <div class="modal fade" id="bulkUpdateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-semibold">
                        <i class="fas fa-edit text-warning me-2"></i>Update Massal Absensi
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Update <span id="selectedCount" class="fw-bold text-dark">0</span> data absensi yang dipilih
                    </p>
                    <form id="bulkUpdateForm" method="POST" action="{{ route('admin.attendance.bulk-update') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Status Baru</label>
                            <select name="status" class="form-select form-select-sm" required>
                                <option value="">Pilih Status</option>
                                <option value="hadir">Hadir</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="alpha">Alpa</option>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-semibold">Keterangan (Opsional)</label>
                            <textarea name="note" rows="2" class="form-control form-control-sm"
                                      placeholder="Tambahkan keterangan..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="bulkUpdateForm" class="btn btn-warning btn-sm">
                        <i class="fas fa-save me-1"></i>Update
                    </button>
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
