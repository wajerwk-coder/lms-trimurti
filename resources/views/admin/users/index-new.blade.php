@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')
@section('page-title', 'Manajemen Pengguna')

@section('breadcrumb')
    <li class="breadcrumb-item active">Manajemen Pengguna</li>
@endsection

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Pengguna
        </a>
        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fas fa-download me-2"></i>Export
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
            <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
        </ul>
    </div>
@endsection

@section('content')
<!-- Alerts -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">{{ $users->where('role', 'admin')->count() }}</h5>
                        <p class="card-text">Administrator</p>
                    </div>
                    <i class="fas fa-user-shield fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">{{ $users->where('role', 'guru')->count() }}</h5>
                        <p class="card-text">Guru</p>
                    </div>
                    <i class="fas fa-chalkboard-teacher fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">{{ $users->where('role', 'siswa')->count() }}</h5>
                        <p class="card-text">Siswa</p>
                    </div>
                    <i class="fas fa-user-graduate fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">{{ $users->where('status', 'active')->count() }}</h5>
                        <p class="card-text">User Aktif</p>
                    </div>
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-semibold text-primary">
            <i class="fas fa-filter me-2"></i>Filter dan Pencarian
        </h6>
    </div>
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="searchInput" class="form-label small fw-semibold">Cari Pengguna</label>
                <div class="input-group">
                    <input type="text" id="searchInput" class="form-control" placeholder="Nama, email, atau NIP/NIS...">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-2">
                <label for="roleFilter" class="form-label small fw-semibold">Role</label>
                <select id="roleFilter" class="form-select">
                    <option value="">Semua Role</option>
                    <option value="admin">Admin</option>
                    <option value="guru">Guru</option>
                    <option value="siswa">Siswa</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="statusFilter" class="form-label small fw-semibold">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-secondary" id="resetFilters">
                    <i class="fas fa-undo me-1"></i>Reset
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions -->
<div class="card shadow mb-4" id="bulkActionsCard" style="display: none;">
    <div class="card-body">
        <form id="bulkForm" method="POST" action="{{ route('admin.users.bulk-action') }}">
            @csrf
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span id="selectedCount" class="text-muted">0 pengguna dipilih</span>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-flex gap-2 justify-content-end">
                        <select name="action" class="form-control" style="width: auto;" required>
                            <option value="">Pilih Aksi</option>
                            <option value="activate">Aktifkan</option>
                            <option value="deactivate">Nonaktifkan</option>
                            <option value="delete">Hapus</option>
                        </select>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-play me-2"></i>Terapkan
                        </button>
                        <button type="button" id="cancelBulk" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Batal
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 fw-semibold text-primary">
            <i class="fas fa-users me-2"></i>Daftar Pengguna
            <span class="badge bg-primary ms-2">{{ $users->total() }}</span>
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="usersTable">
                <thead>
                    <tr>
                        <th width="30">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </div>
                        </th>
                        <th>Pengguna</th>
                        <th>Email & Kontak</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Bergabung</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr class="user-row" data-role="{{ $user->role }}" data-status="{{ $user->status }}">
                        <td>
                            <div class="form-check">
                                <input class="form-check-input user-checkbox" type="checkbox" name="user_ids[]" value="{{ $user->id }}">
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <img class="rounded-circle" width="40" height="40"
                                         src="{{ $user->avatar ?? asset('images/default-avatar.png') }}" 
                                         alt="{{ $user->name }}"
                                         onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $user->name }}</div>
                                    <small class="text-muted">
                                        @if($user->role === 'guru') 
                                            NIP: {{ $user->nip ?? '-' }}
                                        @elseif($user->role === 'siswa') 
                                            NIS: {{ $user->nis ?? '-' }}
                                        @else 
                                            ID: {{ $user->id }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium">{{ $user->email }}</div>
                            @if($user->phone)
                            <small class="text-muted">{{ $user->phone }}</small>
                            @endif
                        </td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge bg-primary">Administrator</span>
                            @elseif($user->role === 'guru')
                                <span class="badge bg-success">Guru</span>
                            @else
                                <span class="badge bg-info">Siswa</span>
                            @endif
                        </td>
                        <td>
                            @if($user->status === 'active')
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div>{{ $user->created_at->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.users.show', $user) }}" 
                                   class="btn btn-sm btn-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" 
                                   class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" 
                                      style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            title="Hapus"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada pengguna yang ditemukan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="d-flex align-items-center">
                <span class="text-muted">
                    Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} 
                    dari {{ $users->total() }} hasil
                </span>
            </div>
            <div>
                {{ $users->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

@push('css')
<style>
.user-row {
    transition: background-color 0.2s;
}
.user-row:hover {
    background-color: rgba(0,0,0,0.02);
}
.btn-group .btn {
    margin-right: 2px;
}
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select All functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkActionsCard = document.getElementById('bulkActionsCard');
    const selectedCount = document.getElementById('selectedCount');
    
    selectAllCheckbox.addEventListener('change', function() {
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });
    
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
    
    function updateBulkActions() {
        const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
        const count = selectedCheckboxes.length;
        
        if (count > 0) {
            bulkActionsCard.style.display = 'block';
            selectedCount.textContent = `${count} pengguna dipilih`;
        } else {
            bulkActionsCard.style.display = 'none';
        }
        
        // Update select all checkbox
        selectAllCheckbox.indeterminate = count > 0 && count < userCheckboxes.length;
        selectAllCheckbox.checked = count === userCheckboxes.length && userCheckboxes.length > 0;
    }
    
    // Cancel bulk actions
    document.getElementById('cancelBulk').addEventListener('click', function() {
        userCheckboxes.forEach(checkbox => checkbox.checked = false);
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
        bulkActionsCard.style.display = 'none';
    });
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    const resetFiltersBtn = document.getElementById('resetFilters');
    
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const roleValue = roleFilter.value;
        const statusValue = statusFilter.value;
        
        document.querySelectorAll('.user-row').forEach(row => {
            const text = row.textContent.toLowerCase();
            const role = row.dataset.role;
            const status = row.dataset.status;
            
            const matchesSearch = text.includes(searchTerm);
            const matchesRole = !roleValue || role === roleValue;
            const matchesStatus = !statusValue || status === statusValue;
            
            if (matchesSearch && matchesRole && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
                // Uncheck if hidden
                const checkbox = row.querySelector('.user-checkbox');
                if (checkbox) checkbox.checked = false;
            }
        });
        
        updateBulkActions();
    }
    
    searchInput.addEventListener('input', filterTable);
    roleFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);
    
    resetFiltersBtn.addEventListener('click', function() {
        searchInput.value = '';
        roleFilter.value = '';
        statusFilter.value = '';
        filterTable();
    });
    
    // Bulk form submission with confirmation
    document.getElementById('bulkForm').addEventListener('submit', function(e) {
        const action = this.querySelector('select[name="action"]').value;
        const count = document.querySelectorAll('.user-checkbox:checked').length;
        
        if (!action) {
            e.preventDefault();
            alert('Pilih aksi yang akan dilakukan!');
            return;
        }
        
        let message = `Apakah Anda yakin ingin ${action} ${count} pengguna yang dipilih?`;
        if (action === 'delete') {
            message = `PERHATIAN: Anda akan menghapus ${count} pengguna. Aksi ini tidak dapat dibatalkan. Lanjutkan?`;
        }
        
        if (!confirm(message)) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
@endsection