@extends('layouts.admin')

@section('title', 'Semua Pengguna')
@section('page-title', 'Semua Pengguna')
@section('page-subtitle', 'Ringkasan seluruh pengguna sistem')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.users.create.admin') }}" class="btn btn-danger btn-sm">
            <i class="fas fa-user-shield me-1"></i>Admin
        </a>
        <a href="{{ route('admin.users.create.guru') }}" class="btn btn-success btn-sm">
            <i class="fas fa-chalkboard-teacher me-1"></i>Guru
        </a>
        <a href="{{ route('admin.users.create.siswa') }}" class="btn btn-warning btn-sm">
            <i class="fas fa-user-graduate me-1"></i>Siswa
        </a>
    </div>
@endsection

@section('content')

@php
    $admins = \App\Models\UserCentral::where('role','admin')->latest()->get();
    $gurus  = \App\Models\UserCentral::where('role','guru')->with('guruProfile')->latest()->get();
    $siswas = \App\Models\UserCentral::where('role','siswa')->with('siswaProfile.kelas')->latest()->get();
@endphp

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([
        ['danger',  'fa-user-shield',            $admins->count(),  'Administrator', route('admin.users.index')],
        ['success', 'fa-chalkboard-teacher',     $gurus->count(),   'Guru',          route('admin.users.guru')],
        ['warning', 'fa-user-graduate',          $siswas->count(),  'Siswa',         route('admin.users.siswa')],
        ['primary', 'fa-users',                  $admins->count() + $gurus->count() + $siswas->count(), 'Total', '#'],
    ] as [$color, $icon, $count, $label, $link])
    <div class="col-6 col-md-3">
        <a href="{{ $link }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-{{ $color }} bg-opacity-10 flex-shrink-0">
                        <i class="fas {{ $icon }} text-{{ $color }} fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0 text-dark">{{ $count }}</div>
                        <small class="text-muted">{{ $label }}</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

{{-- Nav tabs --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom p-0">
        <ul class="nav nav-tabs border-0 px-3" id="userTabs">
            <li class="nav-item">
                <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#tabAdmin">
                    <i class="fas fa-user-shield me-1 text-danger"></i>Admin
                    <span class="badge bg-danger ms-1">{{ $admins->count() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tabGuru">
                    <i class="fas fa-chalkboard-teacher me-1 text-success"></i>Guru
                    <span class="badge bg-success ms-1">{{ $gurus->count() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tabSiswa">
                    <i class="fas fa-user-graduate me-1 text-warning"></i>Siswa
                    <span class="badge bg-warning text-dark ms-1">{{ $siswas->count() }}</span>
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content">

        {{-- Tab Admin --}}
        <div class="tab-pane fade show active" id="tabAdmin">
            <div class="p-3 border-bottom">
                <input type="text" class="form-control form-control-sm" style="max-width:260px;"
                       placeholder="Cari admin..." onkeyup="filterTable('admin-row', this.value)">
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Admin</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $i => $u)
                        <tr class="admin-row">
                            <td class="ps-4 text-muted">{{ $i+1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                                         style="width:32px;height:32px;font-size:.8rem;background:linear-gradient(135deg,#ef4444,#dc2626);">
                                        {{ strtoupper(substr($u->name,0,1)) }}
                                    </div>
                                    <span class="fw-semibold">{{ $u->name }}</span>
                                </div>
                            </td>
                            <td class="text-muted">{{ $u->email }}</td>
                            <td><code class="text-secondary">{{ $u->username ?? '—' }}</code></td>
                            <td class="text-center">
                                <span class="badge bg-{{ $u->is_active ? 'success' : 'secondary' }}">
                                    {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    @if($u->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Hapus {{ addslashes($u->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada admin</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tab Guru --}}
        <div class="tab-pane fade" id="tabGuru">
            <div class="p-3 border-bottom">
                <input type="text" class="form-control form-control-sm" style="max-width:260px;"
                       placeholder="Cari guru..." onkeyup="filterTable('guru-row', this.value)">
            </div>
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
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gurus as $i => $u)
                        <tr class="guru-row">
                            <td class="ps-4 text-muted">{{ $i+1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                                         style="width:32px;height:32px;font-size:.8rem;background:linear-gradient(135deg,#22c55e,#16a34a);">
                                        {{ strtoupper(substr($u->name,0,1)) }}
                                    </div>
                                    <span class="fw-semibold">{{ $u->name }}</span>
                                </div>
                            </td>
                            <td class="text-muted">{{ $u->email }}</td>
                            <td class="text-muted">{{ $u->guruProfile?->nip ?? '—' }}</td>
                            <td class="text-muted">{{ $u->guruProfile?->mata_pelajaran ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $u->is_active ? 'success' : 'secondary' }}">
                                    {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Hapus {{ addslashes($u->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada guru</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tab Siswa --}}
        <div class="tab-pane fade" id="tabSiswa">
            <div class="p-3 border-bottom">
                <input type="text" class="form-control form-control-sm" style="max-width:260px;"
                       placeholder="Cari siswa..." onkeyup="filterTable('siswa-row', this.value)">
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Siswa</th>
                            <th>Email</th>
                            <th>NIS</th>
                            <th>Kelas</th>
                            <th>Jurusan</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswas as $i => $u)
                        <tr class="siswa-row">
                            <td class="ps-4 text-muted">{{ $i+1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                                         style="width:32px;height:32px;font-size:.8rem;background:linear-gradient(135deg,#f59e0b,#d97706);">
                                        {{ strtoupper(substr($u->name,0,1)) }}
                                    </div>
                                    <span class="fw-semibold">{{ $u->name }}</span>
                                </div>
                            </td>
                            <td class="text-muted">{{ $u->email }}</td>
                            <td class="text-muted">{{ $u->siswaProfile?->nis ?? '—' }}</td>
                            <td>
                                @if($u->siswaProfile?->kelas?->name)
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        {{ $u->siswaProfile->kelas->name }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $u->siswaProfile?->major ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $u->is_active ? 'success' : 'secondary' }}">
                                    {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Hapus {{ addslashes($u->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada siswa</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@push('css')
<style>
.hover-card { transition: transform .2s, box-shadow .2s; }
.hover-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,.1) !important; }
</style>
@endpush

@push('js')
<script>
function filterTable(rowClass, q) {
    document.querySelectorAll('.' + rowClass).forEach(r => {
        r.style.display = !q || r.textContent.toLowerCase().includes(q.toLowerCase()) ? '' : 'none';
    });
}
</script>
@endpush
@endsection