@extends('layouts.admin')

@section('title', 'Kriteria Penilaian')
@section('page-title', 'Kriteria Penilaian')
@section('page-subtitle', 'Kriteria penilaian praktikum dikelompokkan per jenis dan judul praktik.')

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

@isset($error)
    <div class="alert alert-warning alert-dismissible fade show mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endisset

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 p-2 bg-primary bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-clipboard-list text-primary fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ $totalJudul ?? 0 }}</div>
                    <small class="text-muted">Judul Praktik</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 p-2 bg-success bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-check-double text-success fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ $totalKriteria ?? 0 }}</div>
                    <small class="text-muted">Total Kriteria</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 p-2 bg-info bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-list-check text-info fa-lg"></i>
                </div>
                <div>
                    @php
                        $totalItems = ($perJudul ?? collect())->flatten()->sum(fn($k) => $k->jumlah_checklist ?? 0);
                    @endphp
                    <div class="h4 fw-bold mb-0">{{ $totalItems }}</div>
                    <small class="text-muted">Total Item SOP</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 p-2 bg-warning bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-toggle-on text-warning fa-lg"></i>
                </div>
                <div>
                    @php
                        $totalAktif = ($perJudul ?? collect())->flatten()->where('is_active', true)->count();
                    @endphp
                    <div class="h4 fw-bold mb-0">{{ $totalAktif }}</div>
                    <small class="text-muted">Aktif</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control"
                           placeholder="Cari judul praktik atau nama kriteria…">
                </div>
            </div>
            <div class="col-md-3">
                <select id="tingkatFilter" class="form-select form-select-sm">
                    <option value="">Semua Tingkat</option>
                    <option value="X">Kelas X</option>
                    <option value="XI">Kelas XI</option>
                    <option value="XII">Kelas XII</option>
                </select>
            </div>
            <div class="col-md-2">
                <select id="statusFilter" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="active">Ada Aktif</option>
                    <option value="inactive">Semua Nonaktif</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" onclick="resetFilter()"
                        class="btn btn-outline-secondary btn-sm w-100">
                    <i class="fas fa-undo me-1"></i>Reset
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Groups per Judul Praktik --}}
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
    $groupColors = ['primary','success','info','warning','danger','secondary','dark'];
    $gIdx = 0;
@endphp

@forelse($perJudul as $groupKey => $items)
    @php
        [$judulPraktik, $tingkat] = explode('||', $groupKey);
        $color         = $groupColors[$gIdx % count($groupColors)];
        $gIdx++;
        $totalBobot    = $items->sum('weight');
        $totalItemAll  = $items->sum(fn($k) => $k->jumlah_checklist ?? 0);
        $adaAktif      = $items->where('is_active', true)->count() > 0;
    @endphp

    <div class="card border-0 shadow-sm mb-4 praktik-group"
         data-judul="{{ strtolower($judulPraktik) }}"
         data-tingkat="{{ $tingkat }}"
         data-status="{{ $adaAktif ? 'active' : 'inactive' }}">

        {{-- Header Kelompok --}}
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 bg-{{ $color }} bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-flask text-{{ $color }}"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-semibold">{{ $judulPraktik }}</h6>
                        <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                            @if($tingkat)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary"
                                      style="font-size:.7rem;">
                                    Kelas {{ $tingkat }}
                                </span>
                            @endif
                            <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }}"
                                  style="font-size:.7rem;">
                                {{ $items->count() }} kategori
                            </span>
                            <span class="badge bg-light text-muted border"
                                  style="font-size:.7rem;">
                                {{ $totalItemAll }} item SOP
                            </span>
                            @if($totalBobot == 100)
                                <span class="badge bg-success bg-opacity-10 text-success"
                                      style="font-size:.7rem;">
                                    <i class="fas fa-check-circle me-1"></i>Bobot 100%
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning"
                                      style="font-size:.7rem;">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Bobot {{ $totalBobot }}%
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Aksi grup --}}
                <div class="d-flex gap-2 align-items-center flex-shrink-0">
                    <button class="btn btn-outline-secondary btn-sm"
                            type="button"
                            onclick="toggleGroup(this)"
                            title="Tampilkan/Sembunyikan">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Baris Kategori --}}
        <div class="group-body">
            <div class="card-body py-2">
                <div class="row g-3">
                    @foreach($items as $item)
                    @php
                        $kat      = $item->kategori ?? '';
                        $katColor = $katColors[$kat] ?? 'secondary';
                        $katLabel = $katLabels[$kat] ?? ucfirst($kat ?: '—');
                    @endphp
                    <div class="col-md-6 col-xl-3">
                        <div class="card border h-100" style="border-color: var(--bs-{{ $katColor }}-border-subtle, #dee2e6) !important;">
                            <div class="card-header py-2 bg-{{ $katColor }} bg-opacity-10 border-bottom-0">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="badge bg-{{ $katColor }} {{ $kat === 'sikap' ? 'text-dark' : '' }}">
                                        {{ $katLabel }}
                                    </span>
                                    <span class="fw-bold small text-{{ $katColor }}">{{ $item->weight }}%</span>
                                </div>
                            </div>
                            <div class="card-body py-2 px-3">
                                <div class="fw-semibold small mb-1 text-truncate" title="{{ $item->name }}">
                                    {{ $item->name }}
                                </div>
                                @if($item->description)
                                <div class="text-muted" style="font-size:.72rem;line-height:1.4;">
                                    {{ Str::limit($item->description, 80) }}
                                </div>
                                @endif
                                <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                                    <span class="badge bg-light text-muted border"
                                          style="font-size:.65rem;">
                                        <i class="fas fa-list-check me-1"></i>{{ $item->jumlah_checklist }} item
                                    </span>
                                    @if($item->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success"
                                              style="font-size:.65rem;">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary"
                                              style="font-size:.65rem;">Nonaktif</span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer bg-white py-2 px-3 border-top">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('admin.kriteria-penilaian.show', $item->id) }}"
                                       class="btn btn-outline-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.kriteria-penilaian.edit', $item->id) }}"
                                       class="btn btn-outline-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.kriteria-penilaian.toggle-status', $item->id) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Ubah status?')">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-outline-{{ $item->is_active ? 'secondary' : 'success' }} btn-sm"
                                                title="{{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.kriteria-penilaian.destroy', $item->id) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Hapus kriteria \'{{ addslashes($item->name) }}\'?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Preview bobot --}}
            <div class="px-3 pb-3">
                <div class="d-flex align-items-center gap-1" style="height:8px;">
                    @foreach($items->sortByDesc('weight') as $item)
                    @php
                        $kat      = $item->kategori ?? '';
                        $katColor = $katColors[$kat] ?? 'secondary';
                        $pct      = $item->weight;
                    @endphp
                    <div class="rounded-pill bg-{{ $katColor }}"
                         style="height:8px;width:{{ $pct }}%;flex-shrink:0;"
                         title="{{ $katLabels[$kat] ?? $kat }}: {{ $pct }}%">
                    </div>
                    @endforeach
                    @if($totalBobot < 100)
                        <div class="rounded-pill bg-light border"
                             style="height:8px;flex-grow:1;"
                             title="Sisa: {{ 100 - $totalBobot }}%"></div>
                    @endif
                </div>
                <div class="d-flex gap-3 mt-1 flex-wrap">
                    @foreach($items as $item)
                    @php $kat = $item->kategori ?? ''; $katColor = $katColors[$kat] ?? 'secondary'; @endphp
                    <small class="text-muted d-flex align-items-center gap-1" style="font-size:.65rem;">
                        <span class="rounded-circle bg-{{ $katColor }} d-inline-block"
                              style="width:8px;height:8px;flex-shrink:0;"></span>
                        {{ $katLabels[$kat] ?? $kat }}: {{ $item->weight }}%
                    </small>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

@empty
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-clipboard-list fa-3x text-muted opacity-25 mb-3 d-block"></i>
            <h6 class="text-muted">Belum ada kriteria penilaian</h6>
            <p class="text-muted small mb-3">Tambahkan kriteria untuk menilai praktikum siswa.</p>
            <a href="{{ route('admin.kriteria-penilaian.create-combined') }}" class="btn btn-success btn-sm me-2">
                <i class="fas fa-layer-group me-1"></i>Tambah Gabungan
            </a>
            <a href="{{ route('admin.kriteria-penilaian.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Tambah Satuan
            </a>
        </div>
    </div>
@endforelse

{{-- No result --}}
<div id="noResultMsg" class="text-center py-4" style="display:none;">
    <i class="fas fa-search fa-2x text-muted opacity-25 mb-2 d-block"></i>
    <span class="text-muted">Tidak ada kriteria yang cocok dengan filter.</span>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Toggle collapse group
    window.toggleGroup = function (btn) {
        const body    = btn.closest('.card').querySelector('.group-body');
        const icon    = btn.querySelector('i');
        const isOpen  = body.style.display !== 'none';
        body.style.display    = isOpen ? 'none' : '';
        icon.classList.toggle('fa-chevron-up',   !isOpen);
        icon.classList.toggle('fa-chevron-down',  isOpen);
    };

    // Filter
    const searchEl  = document.getElementById('searchInput');
    const tingkatEl = document.getElementById('tingkatFilter');
    const statusEl  = document.getElementById('statusFilter');
    const noRes     = document.getElementById('noResultMsg');

    function doFilter() {
        const q  = (searchEl.value || '').toLowerCase().trim();
        const tk = tingkatEl.value;
        const st = statusEl.value;
        let visible = 0;

        document.querySelectorAll('.praktik-group').forEach(function (grp) {
            const judul   = grp.dataset.judul || '';
            const tingkat = grp.dataset.tingkat || '';
            const status  = grp.dataset.status || '';

            // Juga cari di teks nama kriteria di dalam grup
            const innerTxt = grp.textContent.toLowerCase();

            const show = (!q  || judul.includes(q) || innerTxt.includes(q))
                      && (!tk || tingkat === tk)
                      && (!st || status === st);

            grp.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (noRes) noRes.style.display = (visible === 0) ? '' : 'none';
    }

    searchEl.addEventListener('input', doFilter);
    tingkatEl.addEventListener('change', doFilter);
    statusEl.addEventListener('change', doFilter);
});

function resetFilter() {
    document.getElementById('searchInput').value  = '';
    document.getElementById('tingkatFilter').value = '';
    document.getElementById('statusFilter').value = '';
    document.querySelectorAll('.praktik-group').forEach(g => g.style.display = '');
    const noRes = document.getElementById('noResultMsg');
    if (noRes) noRes.style.display = 'none';
}
</script>
@endpush

@endsection
