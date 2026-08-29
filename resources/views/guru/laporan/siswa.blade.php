@extends('layouts.guru')

@section('title', 'Laporan Siswa')
@section('page-title', 'Laporan Siswa')
@section('page-subtitle', 'Rekap kehadiran dan nilai per siswa.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('guru.laporan.index') }}">Laporan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Siswa</li>
@endsection

@push('css')
<style>
.lap-tbl th { font-size:.72rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e8edf2!important; }
.lap-tbl td { font-size:.84rem;vertical-align:middle; }
.lap-tbl tr:hover td { background:#f8fafc; }
.filter-bar { background:#fff;border:1px solid #e8edf2;border-radius:14px;padding:.875rem 1.25rem;margin-bottom:1.25rem; }
.av-sm { width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.78rem;color:#fff;flex-shrink:0; }
.progress-xs { height:5px;border-radius:3px; }
</style>
@endpush

@section('content')

{{-- Filter --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('guru.laporan.siswa') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">Dari Tanggal</label>
            <input type="date" name="start_date" class="form-control form-control-sm"
                   value="{{ $filters['start_date'] }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">Sampai Tanggal</label>
            <input type="date" name="end_date" class="form-control form-control-sm"
                   value="{{ $filters['end_date'] }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">Kelas</label>
            <select name="kelas_id" class="form-select form-select-sm">
                <option value="">Semua Kelas</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}" {{ $filters['kelas_id'] == $k->id ? 'selected' : '' }}>
                        {{ $k->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                <i class="fas fa-search me-1"></i>Filter
            </button>
            <a href="{{ route('guru.laporan.siswa') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4"
         style="border-radius:14px 14px 0 0;border-bottom:1px solid #e8edf2;">
        <h6 class="mb-0 fw-bold">
            <i class="fas fa-users me-2 text-primary"></i>Data Siswa
        </h6>
        <small class="text-muted">{{ $siswaData->count() }} siswa</small>
    </div>
    <div class="card-body p-0">
        @if($siswaData->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fas fa-users fa-2x opacity-25 mb-2 d-block"></i>
            Tidak ada data siswa untuk filter ini.
        </div>
        @else
        <div class="table-responsive">
            <table class="table lap-tbl align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">Siswa</th>
                        <th class="py-3">Kelas</th>
                        <th class="text-center py-3">Kehadiran</th>
                        <th class="text-center py-3">% Hadir</th>
                        <th class="text-center py-3">Rata Tugas</th>
                        <th class="text-center py-3">Rata Praktik</th>
                        <th class="text-center py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($siswaData as $s)
                    @php
                        $name     = $s->user?->name ?? '—';
                        $initial  = strtoupper(substr($name, 0, 1));
                        $colors   = ['#0891b2','#7c3aed','#16a34a','#d97706','#dc2626'];
                        $bgColor  = $colors[abs(crc32($name)) % count($colors)];
                        $pctColor = $s->pct_hadir >= 80 ? '#16a34a' : ($s->pct_hadir >= 60 ? '#d97706' : '#dc2626');
                        $avgT     = $s->avg_tugas;
                        $avgP     = $s->avg_praktik;
                        $avgAll   = collect([$avgT, $avgP])->filter()->avg();
                        $status   = $avgAll >= 70 && $s->pct_hadir >= 75 ? 'Baik' : ($s->pct_hadir < 60 ? 'Perlu Perhatian' : 'Cukup');
                        $statC    = ['Baik'=>'success','Cukup'=>'warning','Perlu Perhatian'=>'danger'][$status];
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="av-sm" style="background:{{ $bgColor }};">{{ $initial }}</div>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $name }}</div>
                                    <div class="text-muted" style="font-size:.7rem;">
                                        NIS: {{ $s->nis ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted">{{ $s->kelas?->name ?? '—' }}</td>
                        <td class="text-center">
                            <div style="font-size:.85rem;">{{ $s->hadir }}/{{ $s->total_absensi }}</div>
                        </td>
                        <td class="text-center">
                            <div class="fw-bold" style="color:{{ $pctColor }};font-size:.9rem;">
                                {{ $s->pct_hadir }}%
                            </div>
                            <div class="progress progress-xs mt-1">
                                <div class="progress-bar" style="width:{{ $s->pct_hadir }}%;background:{{ $pctColor }};"></div>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($avgT !== null)
                                <span class="fw-bold" style="color:{{ $avgT >= 80 ? '#16a34a' : ($avgT >= 60 ? '#d97706' : '#dc2626') }};">
                                    {{ number_format($avgT, 1) }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($avgP !== null)
                                <span class="fw-bold" style="color:{{ $avgP >= 80 ? '#16a34a' : ($avgP >= 60 ? '#d97706' : '#dc2626') }};">
                                    {{ number_format($avgP, 1) }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $statC }}">{{ $status }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@endsection
