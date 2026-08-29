@extends('layouts.admin')

@section('title', 'Manajemen Kelas')
@section('page-title', 'Manajemen Kelas')
@section('page-subtitle', 'Kelola data kelas dan program keahlian.')

@section('page-actions')
    <a href="{{ route('admin.kelas.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Kelas
    </a>
@endsection

@section('content')

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([
        ['primary', 'fa-school',        $kelas->count()      ?? 0, 'Total Kelas'],
        ['success', 'fa-user-friends',  $totalSiswa          ?? 0, 'Total Siswa'],
        ['info',    'fa-graduation-cap', $kelasKeperawatan    ?? 0, $namaJurusan1 ?? 'Jurusan 1'],
        ['warning', 'fa-pills',          $kelasFarmasi        ?? 0, $namaJurusan2 ?? 'Jurusan 2'],
    ] as [$color, $icon, $val, $label])
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-{{ $color }} bg-opacity-10 flex-shrink-0">
                    <i class="fas {{ $icon }} text-{{ $color }} fa-lg"></i>
                </div>
                <div>
                    <div class="h3 fw-bold mb-0">{{ $val }}</div>
                    <small class="text-muted">{{ $label }}</small>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Cari Kelas</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control" id="searchInput"
                           placeholder="Nama atau tahun ajaran…">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Tingkat</label>
                <select class="form-select" id="gradeFilter">
                    <option value="">Semua Tingkat</option>
                    <option value="X">Kelas X</option>
                    <option value="XI">Kelas XI</option>
                    <option value="XII">Kelas XII</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Jurusan</label>
                <select class="form-select" id="majorFilter">
                    <option value="">Semua Jurusan</option>
                    @foreach(\App\Models\Jurusan::orderBy('name')->get() as $jur)
                        <option value="{{ strtolower($jur->name) }}">{{ $jur->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Status</label>
                <select class="form-select" id="statusFilter">
                    <option value="">Semua</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="fas fa-school me-2 text-primary"></i>Daftar Kelas
        </h6>
        <span class="badge bg-secondary" id="kelasCount">{{ $kelas->count() }} kelas</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small" id="kelasTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nama Kelas</th>
                        <th class="text-center">Tingkat</th>
                        <th>Jurusan</th>
                        <th>Tahun Ajaran</th>
                        <th class="text-center">Siswa</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelas as $kls)
                    <tr class="kelas-row"
                        data-grade="{{ $kls->grade ?? '' }}"
                        data-major="{{ strtolower($kls->jurusan?->name ?? '') }}"
                        data-status="{{ $kls->status ?? 'active' }}">
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-2 bg-primary bg-opacity-10 p-2 flex-shrink-0">
                                    <i class="fas fa-school text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $kls->name }}</div>
                                    <small class="text-muted">{{ $kls->academic_year ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($kls->grade)
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">
                                    {{ $kls->grade }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $jName = $kls->jurusan?->name ?? null;
                                $jc = $jName ? match(strtolower($jName)) {
                                    'keperawatan' => 'info',
                                    default       => 'success'
                                } : 'secondary';
                            @endphp
                            @if($jName)
                                <span class="badge bg-{{ $jc }}">{{ $jName }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $kls->academic_year ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary bg-opacity-10 text-dark">
                                {{ $kls->siswa_count ?? 0 }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if(($kls->status ?? 'active') === 'active')
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('admin.kelas.show', $kls->id) }}"
                                   class="btn btn-outline-info btn-sm" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.kelas.edit', $kls->id) }}"
                                   class="btn btn-outline-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.kelas.destroy', $kls->id) }}" method="POST"
                                      onsubmit="return confirm('Hapus kelas {{ addslashes($kls->name) }}?\nSiswa yang ada di kelas ini tidak akan ikut terhapus.')">
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
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-school fa-3x text-muted opacity-25 mb-3 d-block"></i>
                            <h6 class="text-muted">Belum ada kelas</h6>
                            <a href="{{ route('admin.kelas.create') }}" class="btn btn-primary btn-sm mt-2">
                                <i class="fas fa-plus me-1"></i>Tambah Kelas Pertama
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
document.addEventListener('DOMContentLoaded', function () {
    const search  = document.getElementById('searchInput');
    const grade   = document.getElementById('gradeFilter');
    const major   = document.getElementById('majorFilter');
    const status  = document.getElementById('statusFilter');
    const counter = document.getElementById('kelasCount');
    const rows    = document.querySelectorAll('.kelas-row');

    function filter() {
        const q  = search.value.toLowerCase().trim();
        const g  = grade.value;
        const m  = major.value.toLowerCase();
        const s  = status.value;
        let visible = 0;

        rows.forEach(function (row) {
            const txt  = row.textContent.toLowerCase();
            const rg   = row.dataset.grade;
            const rm   = row.dataset.major;
            const rs   = row.dataset.status;
            const show = (!q || txt.includes(q))
                      && (!g || rg === g)
                      && (!m || rm === m)
                      && (!s || rs === s);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (counter) counter.textContent = visible + ' kelas';
    }

    search.addEventListener('input', filter);
    grade.addEventListener('change', filter);
    major.addEventListener('change', filter);
    status.addEventListener('change', filter);
});
</script>
@endpush

@endsection
