@extends('layouts.admin')

@section('title', 'Mata Pelajaran')
@section('page-title', 'Mata Pelajaran')
@section('page-subtitle', 'Kelola data mata pelajaran yang tersedia')

@section('page-actions')
<div class="d-flex gap-2 flex-wrap">
    <a href="{{ route('admin.mata-pelajaran.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Mapel
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
<div class="container-fluid px-0">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats --}}
    @php
        $totalMapel   = $mataPelajarans->count();
        $totalAktif   = $mataPelajaranAktif;
        $totalTeori   = $mataPelajaranTeori;
        $totalPraktik = $mataPelajaranPraktikum;
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 p-2 bg-primary bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-book-open text-primary fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ $totalMapel }}</div>
                        <small class="text-muted">Total Mapel</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 p-2 bg-success bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-check-circle text-success fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ $totalAktif }}</div>
                        <small class="text-muted">Aktif</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 p-2 bg-info bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-chalkboard text-info fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ $totalTeori }}</div>
                        <small class="text-muted">Teori</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 p-2 bg-warning bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-flask text-warning fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ $totalPraktik }}</div>
                        <small class="text-muted">Praktikum</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">Cari Mata Pelajaran</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control" id="searchInput"
                               placeholder="Nama atau kode...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Jurusan</label>
                    <select class="form-select form-select-sm" id="jurusanFilter">
                        <option value="">Semua Jurusan</option>
                        @foreach($jurusans as $j)
                            <option value="{{ $j->id }}">{{ $j->name }}</option>
                        @endforeach
                        <option value="0">Umum / Tanpa Jurusan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Jenis</label>
                    <select class="form-select form-select-sm" id="jenisFilter">
                        <option value="">Semua Jenis</option>
                        <option value="teori">Teori</option>
                        <option value="praktikum">Praktikum</option>
                        <option value="campuran">Campuran</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Status</label>
                    <select class="form-select form-select-sm" id="statusFilter">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="resetFilter()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Kelompok per jurusan ── --}}
    @php
        // Warna badge per jurusan (index-based agar konsisten)
        $jurusanColors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];

        // Susun urutan tampil: jurusan-jurusan dulu, lalu group 0 (umum)
        $groupOrder = $jurusans->pluck('id')->push(0)->toArray();
    @endphp

    @forelse($groupOrder as $groupId)
        @php
            $items = $perJurusan->get($groupId, collect());
            if ($items->isEmpty()) continue;

            $colorIdx   = array_search($groupId, $groupOrder);
            $groupColor = $jurusanColors[$colorIdx % count($jurusanColors)];

            if ($groupId === 0) {
                $groupName  = 'Umum / Tanpa Jurusan';
                $groupIcon  = 'fa-globe';
                $groupColor = 'secondary';
            } else {
                $jur        = $jurusans->firstWhere('id', $groupId);
                $groupName  = $jur?->name ?? 'Jurusan #' . $groupId;
                $groupCode  = $jur?->code;
                $groupIcon  = 'fa-graduation-cap';
            }
        @endphp

        <div class="card border-0 shadow-sm mb-4 jurusan-group"
             data-jurusan-id="{{ $groupId }}">
            {{-- Header jurusan --}}
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 p-2 bg-{{ $groupColor }} bg-opacity-10 flex-shrink-0">
                            <i class="fas {{ $groupIcon }} text-{{ $groupColor }}"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold">{{ $groupName }}</h6>
                            <small class="text-muted">
                                {{ $items->count() }} mata pelajaran
                                @if(isset($groupCode) && $groupCode)
                                    &middot; Kode: <span class="fw-semibold">{{ $groupCode }}</span>
                                @endif
                            </small>
                        </div>
                    </div>
                    <span class="badge bg-{{ $groupColor }} bg-opacity-10 text-{{ $groupColor }} rounded-pill px-3">
                        {{ $items->where('is_active', true)->count() }} aktif
                    </span>
                </div>
            </div>

            {{-- Tabel --}}
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small mapel-table">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" width="40">#</th>
                                <th>Mata Pelajaran</th>
                                <th>Kode</th>
                                <th class="text-center">Jenis</th>
                                <th class="text-center">SKS</th>
                                <th class="text-center">Status</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items->sortBy('name')->values() as $i => $mapel)
                            <tr class="mata-pelajaran-row"
                                data-jurusan="{{ $mapel->major_id ?? 0 }}"
                                data-type="{{ $mapel->type }}"
                                data-status="{{ $mapel->is_active ? 'active' : 'inactive' }}">

                                <td class="ps-4 text-muted">{{ $i + 1 }}</td>

                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 bg-{{ $groupColor }} bg-opacity-10 d-flex align-items-center
                                                    justify-content-center flex-shrink-0"
                                             style="width:36px;height:36px;">
                                            <i class="fas fa-book-open text-{{ $groupColor }} fa-sm"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $mapel->name }}</div>
                                            @if($mapel->description)
                                                <small class="text-muted">{{ Str::limit($mapel->description, 55) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary fw-semibold px-2">
                                        {{ $mapel->code ?? '—' }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    @php
                                        $tc = match($mapel->type) {
                                            'teori'     => ['color' => 'info',    'label' => 'Teori'],
                                            'praktikum' => ['color' => 'warning', 'label' => 'Praktikum'],
                                            default     => ['color' => 'primary', 'label' => 'Campuran'],
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $tc['color'] }} bg-opacity-10 text-{{ $tc['color'] }}">
                                        {{ $tc['label'] }}
                                    </span>
                                </td>

                                <td class="text-center fw-semibold text-dark">{{ $mapel->sks ?? '—' }}</td>

                                <td class="text-center">
                                    @if($mapel->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="fas fa-circle me-1" style="font-size:7px;vertical-align:middle;"></i>Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                            <i class="fas fa-circle me-1" style="font-size:7px;vertical-align:middle;"></i>Nonaktif
                                        </span>
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
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Ubah status mata pelajaran ini?')">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-outline-{{ $mapel->is_active ? 'secondary' : 'success' }} btn-sm"
                                                    title="{{ $mapel->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.mata-pelajaran.destroy', $mapel->id) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Hapus mata pelajaran {{ addslashes($mapel->name) }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-book fa-3x text-muted opacity-25 mb-3 d-block"></i>
                <h6 class="text-muted">Belum ada mata pelajaran</h6>
                <p class="text-muted small mb-3">Tambahkan mata pelajaran atau gunakan data default.</p>
                <a href="{{ route('admin.mata-pelajaran.create') }}" class="btn btn-primary btn-sm me-2">
                    <i class="fas fa-plus me-1"></i>Tambah Manual
                </a>
                <form action="{{ route('admin.mata-pelajaran.seed-default') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-database me-1"></i>Seed Default
                    </button>
                </form>
            </div>
        </div>
    @endforelse

    {{-- Pesan tidak ada hasil filter --}}
    <div id="noResultMsg" class="text-center py-4" style="display:none;">
        <i class="fas fa-search fa-2x text-muted opacity-25 mb-2 d-block"></i>
        <span class="text-muted">Tidak ada mata pelajaran yang cocok dengan filter.</span>
    </div>

</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const search  = document.getElementById('searchInput');
    const jFilter = document.getElementById('jurusanFilter');
    const jenis   = document.getElementById('jenisFilter');
    const status  = document.getElementById('statusFilter');
    const noRes   = document.getElementById('noResultMsg');

    function doFilter() {
        const q  = (search.value || '').toLowerCase().trim();
        const jv = jFilter.value;   // jurusan id atau '' atau '0'
        const j  = jenis.value;
        const s  = status.value;

        let totalVisible = 0;

        // Filter per group card
        document.querySelectorAll('.jurusan-group').forEach(function (card) {
            const cardJurusanId = card.dataset.jurusanId; // string
            let groupVisible = 0;

            card.querySelectorAll('.mata-pelajaran-row').forEach(function (row) {
                const rowJ   = row.dataset.jurusan;   // major_id atau '0'
                const rowT   = row.dataset.type;
                const rowS   = row.dataset.status;
                const txt    = row.textContent.toLowerCase();

                const matchQ = !q  || txt.includes(q);
                const matchJ = !jv || rowJ === jv;
                const matchT = !j  || rowT === j;
                const matchS = !s  || rowS === s;

                const show = matchQ && matchJ && matchT && matchS;
                row.style.display = show ? '' : 'none';
                if (show) { groupVisible++; totalVisible++; }
            });

            // Sembunyikan seluruh card group jika tidak ada baris visible
            // atau jika filter jurusan aktif dan bukan group ini
            const hideCard = (groupVisible === 0) || (jv && cardJurusanId !== jv);
            card.style.display = hideCard ? 'none' : '';
        });

        if (noRes) noRes.style.display = (totalVisible === 0) ? '' : 'none';
    }

    search.addEventListener('input', doFilter);
    jFilter.addEventListener('change', doFilter);
    jenis.addEventListener('change', doFilter);
    status.addEventListener('change', doFilter);
});

function resetFilter() {
    document.getElementById('searchInput').value  = '';
    document.getElementById('jurusanFilter').value = '';
    document.getElementById('jenisFilter').value  = '';
    document.getElementById('statusFilter').value = '';

    document.querySelectorAll('.jurusan-group').forEach(c => c.style.display = '');
    document.querySelectorAll('.mata-pelajaran-row').forEach(r => r.style.display = '');
    const noRes = document.getElementById('noResultMsg');
    if (noRes) noRes.style.display = 'none';
}
</script>
@endpush
