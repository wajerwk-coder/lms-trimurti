@extends('layouts.admin')

@section('title', 'Mata Pelajaran')
@section('page-title', 'Mata Pelajaran')
@section('page-subtitle', 'Kelola data mata pelajaran.')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.mata-pelajaran.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Tambah
        </a>
        <form action="{{ route('admin.mata-pelajaran.seed-default') }}" method="POST"
              onsubmit="return confirm('Tambahkan data mata pelajaran default?')">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-database me-1"></i>Seed Default
            </button>
        </form>
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

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row g-2">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Nama atau kode'¦">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="jenisFilter">
                    <option value="">Semua Jenis</option>
                    <option value="teori">Teori</option>
                    <option value="praktikum">Praktikum</option>
                    <option value="campuran">Campuran</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Tidak Aktif</option>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small" id="mataPelajaranTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Mata Pelajaran</th>
                        <th>Kode</th>
                        <th class="text-center">Jenis</th>
                        <th class="text-center">SKS</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mataPelajarans as $mapel)
                    <tr class="mata-pelajaran-row"
                        data-type="{{ $mapel->type }}"
                        data-status="{{ $mapel->is_active ? 'active' : 'inactive' }}">
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-2 bg-primary bg-opacity-10 p-2 flex-shrink-0">
                                    <i class="fas fa-book text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $mapel->name }}</div>
                                    <small class="text-muted">{{ Str::limit($mapel->description ?? '', 60) }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $mapel->code }}</span></td>
                        <td class="text-center">
                            @php
                                $tc = match($mapel->type) {
                                    'teori'     => 'info',
                                    'praktikum' => 'warning',
                                    default     => 'primary'
                                };
                            @endphp
                            <span class="badge bg-{{ $tc }}">{{ ucfirst($mapel->type) }}</span>
                        </td>
                        <td class="text-center fw-semibold">{{ $mapel->sks }}</td>
                        <td class="text-center">
                            @if($mapel->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('admin.mata-pelajaran.show', $mapel->id) }}"
                                   class="btn btn-outline-info btn-sm" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.mata-pelajaran.edit', $mapel->id) }}"
                                   class="btn btn-outline-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.mata-pelajaran.toggle-status', $mapel->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Ubah status mata pelajaran ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary btn-sm" title="Toggle Status">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.mata-pelajaran.destroy', $mapel->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Hapus {{ addslashes($mapel->name) }}?')">
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
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-book fa-3x text-muted opacity-25 mb-3 d-block"></i>
                            <h6 class="text-muted">Belum ada mata pelajaran</h6>
                            <a href="{{ route('admin.mata-pelajaran.create') }}" class="btn btn-primary btn-sm mt-2">
                                <i class="fas fa-plus me-1"></i>Tambah Pertama
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const s  = document.getElementById('searchInput');
    const jf = document.getElementById('jenisFilter');
    const sf = document.getElementById('statusFilter');
    function filter() {
        const q = s.value.toLowerCase();
        document.querySelectorAll('.mata-pelajaran-row').forEach(function(r) {
            const show = (!q || r.textContent.toLowerCase().includes(q)) &&
                         (!jf.value || r.dataset.type === jf.value) &&
                         (!sf.value || r.dataset.status === sf.value);
            r.style.display = show ? '' : 'none';
        });
    }
    s.addEventListener('input', filter);
    jf.addEventListener('change', filter);
    sf.addEventListener('change', filter);
});
</script>
@endpush

@endsection