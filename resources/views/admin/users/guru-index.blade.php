@extends('layouts.admin')

@section('title', 'Manajemen Guru')
@section('page-title', 'Manajemen Guru')
@section('page-subtitle', 'Kelola semua akun guru yang mengajar.')

@section('page-actions')
    <a href="{{ route('admin.users.create.guru') }}" class="btn btn-success btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Guru
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
                <a class="nav-link active" href="{{ route('admin.users.guru') }}">
                    <i class="fas fa-chalkboard-teacher me-1"></i>Guru
                    <span class="badge bg-success bg-opacity-25 text-success ms-1">{{ $gurus->total() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-muted" href="{{ route('admin.users.siswa') }}">
                    <i class="fas fa-user-graduate me-1"></i>Siswa
                    <span class="badge bg-secondary ms-1">{{ \App\Models\UserCentral::where('role','siswa')->count() }}</span>
                </a>
            </li>
        </ul>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-chalkboard-teacher text-success fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ $gurus->total() }}</div>
                    <small class="text-muted">Total Guru</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-primary bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-user-check text-primary fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">
                        {{ \App\Models\UserCentral::where('role','guru')->where('is_active',true)->count() }}
                    </div>
                    <small class="text-muted">Guru Aktif</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-warning bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-book text-warning fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">
                        @php
                            try { echo \App\Models\Subject::count(); } catch(\Throwable $e) { echo 0; }
                        @endphp
                    </div>
                    <small class="text-muted">Mata Pelajaran</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-info bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-tasks text-info fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">
                        @php
                            try { echo \App\Models\Material::count(); } catch(\Throwable $e) { echo 0; }
                        @endphp
                    </div>
                    <small class="text-muted">Total Materi</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">Cari Guru</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="guruSearch" class="form-control" placeholder="Nama, email, atau NIP...">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Filter Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
            <div class="col-md-2">
                <button onclick="resetSearch()" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-undo me-1"></i>Reset
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">
            <i class="fas fa-chalkboard-teacher me-2 text-success"></i>Daftar Guru
        </h6>
        <span class="badge bg-secondary">{{ $gurus->total() }} guru</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Guru</th>
                        <th>Email</th>
                        <th>NIP</th>
                        <th>Mata Pelajaran</th>
                        <th class="text-center">Status</th>
                        <th>Bergabung</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody id="guruTableBody">
                    @forelse($gurus as $i => $guru)
                        <tr class="guru-row"
                            data-status="{{ $guru->is_active ? 'aktif' : 'nonaktif' }}">
                            <td class="ps-4 text-muted">{{ $gurus->firstItem() + $i }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($guru->photo)
                                        <img src="{{ $guru->photo_url }}"
                                             class="rounded-circle flex-shrink-0"
                                             style="width:38px;height:38px;object-fit:cover;" alt=""
                                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($guru->name) }}&background=16a34a&color=fff&size=64'">
                                    @else
                                        <div class="rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center fw-bold text-white"
                                             style="width:38px;height:38px;font-size:.9rem;background:linear-gradient(135deg,#16a34a,#059669);">
                                            {{ strtoupper(substr($guru->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold lh-1">{{ $guru->name }}</div>
                                        <small class="text-muted">{{ $guru->username ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted">{{ $guru->email }}</td>
                            <td>
                                @if($guru->guruProfile?->nip)
                                    <span class="badge bg-info bg-opacity-10 text-info">{{ $guru->guruProfile->nip }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $guru->guruProfile?->mata_pelajaran ?? '—' }}</td>
                            <td class="text-center">
                                @if($guru->is_active)
                                    <span class="badge bg-success">
                                        <i class="fas fa-circle me-1" style="font-size:7px;"></i>Aktif
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-circle me-1" style="font-size:7px;"></i>Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="text-dark">{{ $guru->created_at->format('d M Y') }}</div>
                                <small class="text-muted">{{ $guru->created_at->diffForHumans() }}</small>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('admin.users.show', $guru->id) }}"
                                       class="btn btn-outline-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $guru->id) }}"
                                       class="btn btn-outline-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $guru->id) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Hapus guru {{ addslashes($guru->name) }}? Tindakan ini tidak dapat dibatalkan.')">
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
                                <i class="fas fa-chalkboard-teacher fa-3x text-muted opacity-25 mb-3 d-block"></i>
                                <h6 class="text-muted">Belum ada data guru</h6>
                                <a href="{{ route('admin.users.create.guru') }}" class="btn btn-success btn-sm mt-2">
                                    <i class="fas fa-plus me-1"></i>Tambah Guru
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($gurus->hasPages())
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Menampilkan {{ $gurus->firstItem() }}–{{ $gurus->lastItem() }} dari {{ $gurus->total() }}
            </small>
            {{ $gurus->links() }}
        </div>
    @endif
</div>

@push('js')
<script>
const rows       = document.querySelectorAll('.guru-row');
const searchEl   = document.getElementById('guruSearch');
const statusEl   = document.getElementById('statusFilter');

function filterRows() {
    const q = searchEl.value.toLowerCase();
    const s = statusEl.value.toLowerCase();
    rows.forEach(r => {
        const matchQ = !q || r.textContent.toLowerCase().includes(q);
        const matchS = !s || r.dataset.status === s;
        r.style.display = (matchQ && matchS) ? '' : 'none';
    });
}

searchEl.addEventListener('input', filterRows);
statusEl.addEventListener('change', filterRows);

function resetSearch() {
    searchEl.value = ''; statusEl.value = '';
    rows.forEach(r => r.style.display = '');
}
</script>
@endpush
@endsection