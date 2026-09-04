@extends('layouts.admin')

@section('title', 'Manajemen Admin')
@section('page-title', 'Manajemen Admin')
@section('page-subtitle', 'Kelola semua akun administrator sistem.')

@section('page-actions')
    <a href="{{ route('admin.users.create.admin') }}" class="btn btn-danger btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Admin
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
                <a class="nav-link active" href="{{ route('admin.users.index') }}">
                    <i class="fas fa-user-shield me-1"></i>Admin
                    <span class="badge bg-danger bg-opacity-25 text-danger ms-1">{{ $users->total() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-muted" href="{{ route('admin.users.guru') }}">
                    <i class="fas fa-chalkboard-teacher me-1"></i>Guru
                    <span class="badge bg-secondary ms-1">{{ \App\Models\UserCentral::where('role','guru')->count() }}</span>
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
                <div class="rounded-3 p-3 bg-danger bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-user-shield text-danger fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ $users->total() }}</div>
                    <small class="text-muted">Total Admin</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-user-check text-success fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ \App\Models\UserCentral::where('role','admin')->where('is_active',true)->count() }}</div>
                    <small class="text-muted">Admin Aktif</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-warning bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-user-graduate text-warning fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ \App\Models\UserCentral::where('role','siswa')->count() }}</div>
                    <small class="text-muted">Siswa</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-primary bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-users text-primary fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ \App\Models\UserCentral::count() }}</div>
                    <small class="text-muted">Total User</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Pencarian --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold mb-1">Cari Admin</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="adminSearch" class="form-control"
                           placeholder="Nama, email, atau username...">
                </div>
            </div>
            <div class="col-md-2">
                <button onclick="resetSearch()" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-undo me-1"></i>Reset
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Tabel Admin --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">
            <i class="fas fa-user-shield me-2 text-danger"></i>Daftar Administrator
        </h6>
        <span class="badge bg-secondary">{{ $users->total() }} admin</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Admin</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th class="text-center">Status</th>
                        <th>Bergabung</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody id="adminTableBody">
                    @forelse($users as $user)
                        <tr class="admin-row">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    {{-- Avatar dengan inisial --}}
                                    <img src="{{ $user->photo_url }}"
                                         class="rounded-circle flex-shrink-0"
                                         style="width:40px;height:40px;object-fit:cover;"
                                         alt="{{ $user->name }}"
                                         onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
                                    <div class="rounded-circle flex-shrink-0 d-none align-items-center justify-content-center fw-bold text-white"
                                         style="width:40px;height:40px;font-size:1rem;background:linear-gradient(135deg,#ef4444,#dc2626);">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $user->name }}</div>
                                        @if($user->id === auth()->id())
                                            <span class="badge bg-primary bg-opacity-15 text-primary"
                                                  style="font-size:10px;">Anda</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted">{{ $user->email }}</td>
                            <td>
                                <code class="text-secondary" style="font-size:12px;">{{ $user->username ?? '—' }}</code>
                            </td>
                            <td class="text-center">
                                @if($user->is_active)
                                    <span class="badge bg-success">
                                        <i class="fas fa-circle me-1" style="font-size:7px;"></i>Aktif
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-circle me-1" style="font-size:7px;"></i>Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted">
                                <div>{{ $user->created_at->format('d M Y') }}</div>
                                <small class="opacity-75">{{ $user->created_at->diffForHumans() }}</small>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('admin.users.show', $user->id) }}"
                                       class="btn btn-outline-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user->id) }}"
                                       class="btn btn-outline-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user->id) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Hapus admin {{ addslashes($user->name) }}? Tindakan ini tidak dapat dibatalkan.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-outline-secondary btn-sm" disabled
                                                title="Tidak dapat menghapus akun sendiri">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex
                                                align-items-center justify-content-center"
                                         style="width:64px;height:64px;">
                                        <i class="fas fa-user-shield text-danger fa-xl"></i>
                                    </div>
                                </div>
                                <h6 class="text-muted fw-semibold">Belum ada administrator</h6>
                                <p class="text-muted small mb-3">Tambahkan administrator pertama untuk mengelola sistem.</p>
                                <a href="{{ route('admin.users.create.admin') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i>Tambah Admin
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }}
            </small>
            {{ $users->links() }}
        </div>
    @endif
</div>

@push('js')
<script>
const rows = document.querySelectorAll('.admin-row');
document.getElementById('adminSearch').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    rows.forEach(r => {
        r.style.display = !q || r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
function resetSearch() {
    document.getElementById('adminSearch').value = '';
    rows.forEach(r => r.style.display = '');
}
</script>
@endpush
@endsection