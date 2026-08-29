@extends('layouts.admin')

@section('title', 'Kriteria Penilaian')
@section('page-title', 'Kriteria Penilaian')
@section('page-subtitle', 'Kelola kriteria penilaian praktikum.')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.kriteria-penilaian.create-combined') }}" class="btn btn-success btn-sm">
            <i class="fas fa-layer-group me-1"></i>Tambah Gabungan
        </a>
        <a href="{{ route('admin.kriteria-penilaian.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Tambah Kriteria
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

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row g-2">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari nama, mata praktik…">
                </div>
            </div>
            <div class="col-md-3">
                <select id="kategoriFilter" class="form-select">
                    <option value="">Semua Kategori</option>
                    <option value="persiapan">Persiapan</option>
                    <option value="pelaksanaan">Pelaksanaan</option>
                    <option value="hasil">Hasil</option>
                    <option value="sikap">Sikap Profesional</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="tingkatFilter" class="form-select">
                    <option value="">Semua Tingkat</option>
                    <option value="X">Kelas X</option>
                    <option value="XI">Kelas XI</option>
                    <option value="XII">Kelas XII</option>
                </select>
            </div>
            <div class="col-md-2">
                <select id="statusFilter" class="form-select">
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
            <table class="table table-hover align-middle mb-0 small" id="kriteriaTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nama Kriteria</th>
                        <th>Kategori</th>
                        <th>Mata Praktik</th>
                        <th class="text-center">Tingkat</th>
                        <th class="text-center">Bobot</th>
                        <th class="text-center">Checklist</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kriteria as $item)
                    @php
                        $katColors = [
                            'persiapan'   => 'info',
                            'pelaksanaan' => 'primary',
                            'hasil'       => 'success',
                            'sikap'       => 'warning',
                        ];
                        $katLabels = [
                            'persiapan'   => 'Persiapan',
                            'pelaksanaan' => 'Pelaksanaan',
                            'hasil'       => 'Hasil',
                            'sikap'       => 'Sikap Profesional',
                        ];
                        $kat      = $item->kategori ?? '';
                        $katColor = $katColors[$kat] ?? 'secondary';
                        $katLabel = $katLabels[$kat] ?? ucfirst($kat ?: '—');
                    @endphp
                    <tr class="kriteria-row"
                        data-kategori="{{ $kat }}"
                        data-tingkat="{{ $item->tingkat_kelas }}"
                        data-status="{{ $item->is_active ? 'active' : 'inactive' }}">
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-2 bg-primary bg-opacity-10 p-2 flex-shrink-0">
                                    <i class="fas fa-clipboard-list text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $item->name }}</div>
                                    <small class="text-muted">{{ Str::limit($item->description ?? '', 60) }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $katColor }} {{ $kat === 'sikap' ? 'text-dark' : '' }}">
                                {{ $katLabel }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $item->mata_praktik ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary fw-semibold">
                                {{ $item->tingkat_kelas ?? '—' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info bg-opacity-10 text-info fw-semibold">
                                {{ $item->weight }}%
                            </span>
                        </td>
                        <td class="text-center text-muted">
                            {{ $item->jumlah_checklist }}
                        </td>
                        <td class="text-center">
                            @if($item->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('admin.kriteria-penilaian.show', $item->id) }}"
                                   class="btn btn-outline-info btn-sm" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.kriteria-penilaian.edit', $item->id) }}"
                                   class="btn btn-outline-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.kriteria-penilaian.destroy', $item->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Hapus kriteria \'{{ addslashes($item->name) }}\'?')">
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
                            <i class="fas fa-clipboard-list fa-3x text-muted opacity-25 mb-3 d-block"></i>
                            <h6 class="text-muted">Belum ada kriteria penilaian</h6>
                            <a href="{{ route('admin.kriteria-penilaian.create') }}"
                               class="btn btn-primary btn-sm mt-2">
                                <i class="fas fa-plus me-1"></i>Tambah Pertama
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($kriteria instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $kriteria->hasPages())
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-2 px-4">
            <small class="text-muted">
                {{ $kriteria->firstItem() }}–{{ $kriteria->lastItem() }} dari {{ $kriteria->total() }} kriteria
            </small>
            {{ $kriteria->links() }}
        </div>
    @endif
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const s  = document.getElementById('searchInput');
    const kf = document.getElementById('kategoriFilter');
    const tf = document.getElementById('tingkatFilter');
    const sf = document.getElementById('statusFilter');

    function filter() {
        const q = s.value.toLowerCase();
        document.querySelectorAll('.kriteria-row').forEach(function(r) {
            const show = (!q || r.textContent.toLowerCase().includes(q)) &&
                         (!kf.value || r.dataset.kategori === kf.value) &&
                         (!tf.value || r.dataset.tingkat === tf.value) &&
                         (!sf.value || r.dataset.status === sf.value);
            r.style.display = show ? '' : 'none';
        });
    }
    s.addEventListener('input', filter);
    kf.addEventListener('change', filter);
    tf.addEventListener('change', filter);
    sf.addEventListener('change', filter);
});
</script>
@endpush

@endsection
