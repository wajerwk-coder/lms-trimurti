@extends('layouts.admin')

@section('title', 'Manajemen Siswa')
@section('page-title', 'Manajemen Siswa')
@section('page-subtitle', 'Kelola semua akun siswa yang terdaftar.')

@section('page-actions')
    <a href="{{ route('admin.users.create.siswa') }}" class="btn btn-warning btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Siswa
    </a>
@endsection

@push('css')
<style>
.avatar-siswa {
    width:38px; height:38px; border-radius:50%; object-fit:cover; flex-shrink:0;
}
.avatar-initial {
    width:38px; height:38px; border-radius:50%; flex-shrink:0;
    background:linear-gradient(135deg,#d97706,#b45309);
    display:flex; align-items:center; justify-content:center;
    font-weight:700; color:#fff; font-size:.9rem;
}
</style>
@endpush

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

{{-- Tab Navigasi Role --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2 px-3">
        <ul class="nav nav-pills gap-1">
            <li class="nav-item">
                <a class="nav-link text-muted" href="{{ route('admin.users.index') }}">
                    <i class="fas fa-user-shield me-1"></i>Admin
                    <span class="badge bg-secondary ms-1">{{ \App\Models\UserCentral::where('role','admin')->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-muted" href="{{ route('admin.users.guru') }}">
                    <i class="fas fa-chalkboard-teacher me-1"></i>Guru
                    <span class="badge bg-secondary ms-1">{{ \App\Models\UserCentral::where('role','guru')->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.users.siswa') }}">
                    <i class="fas fa-user-graduate me-1"></i>Siswa
                    <span class="badge bg-warning bg-opacity-25 text-warning ms-1">{{ $siswas->total() }}</span>
                </a>
            </li>
        </ul>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @php
        $totalSiswa  = $siswas->total();
        $aktifSiswa  = \App\Models\UserCentral::where('role','siswa')->where('is_active',true)->count();
        $kelasAktif  = \App\Models\Kelas::count() ?? 0;
        $jurusanCount = \App\Models\Jurusan::count() ?? 0;
    @endphp
    @foreach([
        ['warning', 'fa-user-graduate',  $totalSiswa,   'Total Siswa'],
        ['success', 'fa-user-check',     $aktifSiswa,   'Siswa Aktif'],
        ['info',    'fa-school',         $kelasAktif,   'Total Kelas'],
        ['primary', 'fa-graduation-cap', $jurusanCount, 'Jurusan'],
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

{{-- Filter & Bulk --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Cari Siswa</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="siswaSearch" class="form-control" placeholder="Nama, email, NIS...">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Filter Kelas</label>
                <select id="kelasFilter" class="form-select">
                    <option value="">Semua Kelas</option>
                    @php try { $allKelas = \App\Models\Kelas::orderBy('name')->pluck('name'); } catch(\Throwable $e) { $allKelas = collect(); } @endphp
                    @foreach($allKelas as $k)
                        <option value="{{ $k }}">{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Jurusan</label>
                <select id="jurusanFilter" class="form-select">
                    <option value="">Semua Jurusan</option>
                    @php try { $allJurusan = \App\Models\Jurusan::orderBy('name')->pluck('name'); } catch(\Throwable $e) { $allJurusan = collect(); } @endphp
                    @foreach($allJurusan as $j)
                        <option value="{{ strtolower($j) }}">{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button onclick="resetSearch()" class="btn btn-outline-secondary flex-fill">
                    <i class="fas fa-undo me-1"></i>Reset
                </button>
                <button type="button" id="bulkDeleteBtn" class="btn btn-outline-danger flex-fill" disabled>
                    <i class="fas fa-trash me-1"></i>Hapus
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="fas fa-user-graduate me-2 text-warning"></i>Daftar Siswa
        </h6>
        <div class="d-flex gap-2 align-items-center">
            <span class="badge bg-secondary" id="totalVisible">{{ $siswas->total() }} siswa</span>
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
                        <th>#</th>
                        <th>Siswa</th>
                        <th>Email</th>
                        <th>NIS / NISN</th>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody id="siswaTableBody">
                    @forelse($siswas as $i => $siswa)
                    @php
                        $kelasName   = $siswa->siswaProfile?->kelas?->name ?? '';
                        $majorName   = $siswa->siswaProfile?->major ?? '';
                    @endphp
                    <tr class="siswa-row"
                        data-kelas="{{ strtolower($kelasName) }}"
                        data-jurusan="{{ strtolower($majorName) }}"
                        data-status="{{ $siswa->is_active ? 'aktif' : 'nonaktif' }}">
                        <td class="ps-4">
                            <input type="checkbox" class="form-check-input siswa-check"
                                   value="{{ $siswa->id }}">
                        </td>
                        <td class="text-muted">{{ $siswas->firstItem() + $i }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($siswa->photo)
                                    <img src="{{ asset('storage/'.$siswa->photo) }}" class="avatar-siswa" alt="">
                                @else
                                    <div class="avatar-initial">{{ strtoupper(substr($siswa->name, 0, 1)) }}</div>
                                @endif
                                <div>
                                    <div class="fw-semibold lh-1">{{ $siswa->name }}</div>
                                    <small class="text-muted">{{ $siswa->username ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted">{{ $siswa->email }}</td>
                        <td>
                            @if($siswa->siswaProfile?->nis)
                                <div><span class="badge bg-primary bg-opacity-10 text-primary">NIS: {{ $siswa->siswaProfile->nis }}</span></div>
                            @endif
                            @if($siswa->siswaProfile?->nisn)
                                <div class="mt-1"><span class="badge bg-secondary bg-opacity-10 text-secondary">NISN: {{ $siswa->siswaProfile->nisn }}</span></div>
                            @endif
                            @if(!$siswa->siswaProfile?->nis && !$siswa->siswaProfile?->nisn)
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($kelasName)
                                <span class="badge bg-success bg-opacity-10 text-success">{{ $kelasName }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $majorName ?: '—' }}</td>
                        <td class="text-center">
                            @if($siswa->is_active)
                                <span class="badge bg-success"><i class="fas fa-circle me-1" style="font-size:7px;"></i>Aktif</span>
                            @else
                                <span class="badge bg-secondary"><i class="fas fa-circle me-1" style="font-size:7px;"></i>Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('admin.users.show', $siswa->id) }}"
                                   class="btn btn-outline-info btn-sm" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $siswa->id) }}"
                                   class="btn btn-outline-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.users.destroy', $siswa->id) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus siswa {{ addslashes($siswa->name) }}?')">
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
                            <i class="fas fa-user-graduate fa-3x text-muted opacity-25 mb-3 d-block"></i>
                            <h6 class="text-muted">Belum ada data siswa</h6>
                            <a href="{{ route('admin.users.create.siswa') }}" class="btn btn-warning btn-sm mt-2">
                                <i class="fas fa-plus me-1"></i>Tambah Siswa
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($siswas->hasPages())
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
        <small class="text-muted">
            Menampilkan {{ $siswas->firstItem() }}–{{ $siswas->lastItem() }} dari {{ $siswas->total() }}
        </small>
        {{ $siswas->links() }}
    </div>
    @endif
</div>

{{-- Bulk Delete Modal --}}
<div class="modal fade" id="bulkDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Konfirmasi Hapus Massal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Hapus <strong id="selectedCount">0</strong> siswa yang dipilih?</p>
                <p class="text-danger small mb-0"><i class="fas fa-info-circle me-1"></i>Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="bulkDeleteForm" method="POST"
                      action="{{ route('admin.users.bulk-action') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="action" value="delete">
                    <div id="bulkIdsContainer"></div>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows       = document.querySelectorAll('.siswa-row');
    const checks     = document.querySelectorAll('.siswa-check');
    const selectAll  = document.getElementById('selectAll');
    const bulkBtn    = document.getElementById('bulkDeleteBtn');
    const cntEl      = document.getElementById('selectedCount');
    const searchEl   = document.getElementById('siswaSearch');
    const kelasFl    = document.getElementById('kelasFilter');
    const jurusanFl  = document.getElementById('jurusanFilter');
    const statusFl   = document.getElementById('statusFilter');
    const totalVis   = document.getElementById('totalVisible');

    // ── Filter rows ──────────────────────────────────────
    function filterRows() {
        const q = searchEl.value.toLowerCase();
        const k = kelasFl.value.toLowerCase();
        const j = jurusanFl.value.toLowerCase();
        const s = statusFl.value.toLowerCase();
        let vis = 0;
        rows.forEach(r => {
            const mQ = !q || r.textContent.toLowerCase().includes(q);
            const mK = !k || r.dataset.kelas === k;
            const mJ = !j || r.dataset.jurusan.includes(j);
            const mS = !s || r.dataset.status === s;
            const show = mQ && mK && mJ && mS;
            r.style.display = show ? '' : 'none';
            if (show) vis++;
        });
        if (totalVis) totalVis.textContent = vis + ' siswa';
    }

    searchEl.addEventListener('input', filterRows);
    kelasFl.addEventListener('change', filterRows);
    jurusanFl.addEventListener('change', filterRows);
    statusFl.addEventListener('change', filterRows);

    // ── Select all ───────────────────────────────────────
    function updateBulk() {
        const checked = document.querySelectorAll('.siswa-check:checked').length;
        bulkBtn.disabled = checked === 0;
        bulkBtn.textContent = checked > 0 ? `Hapus (${checked})` : 'Hapus';
        if (cntEl) cntEl.textContent = checked;
        selectAll.indeterminate = checked > 0 && checked < checks.length;
        selectAll.checked = checked === checks.length && checks.length > 0;
    }

    selectAll.addEventListener('change', function () {
        checks.forEach(c => c.checked = this.checked);
        updateBulk();
    });
    checks.forEach(c => c.addEventListener('change', updateBulk));

    // ── Bulk delete ──────────────────────────────────────
    bulkBtn.addEventListener('click', function () {
        const checked = document.querySelectorAll('.siswa-check:checked');
        if (!checked.length) return;
        const container = document.getElementById('bulkIdsContainer');
        container.innerHTML = '';
        checked.forEach(c => {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'user_ids[]'; inp.value = c.value;
            container.appendChild(inp);
        });
        new bootstrap.Modal(document.getElementById('bulkDeleteModal')).show();
    });

    window.resetSearch = function () {
        searchEl.value = ''; kelasFl.value = ''; jurusanFl.value = ''; statusFl.value = '';
        rows.forEach(r => r.style.display = '');
        if (totalVis) totalVis.textContent = '{{ $siswas->total() }} siswa';
    };
});
</script>
@endpush
@endsection