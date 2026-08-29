@extends('layouts.guru')

@section('title', 'Laporan Nilai')
@section('page-title', 'Laporan Nilai')
@section('page-subtitle', 'Rekap nilai tugas dan praktikum siswa.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('guru.laporan.index') }}">Laporan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Nilai</li>
@endsection

@push('css')
<style>
.lap-tbl th { font-size:.72rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e8edf2!important; }
.lap-tbl td { font-size:.84rem;vertical-align:middle; }
.lap-tbl tr:hover td { background:#f8fafc; }
.filter-bar { background:#fff;border:1px solid #e8edf2;border-radius:14px;padding:.875rem 1.25rem;margin-bottom:1.25rem; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:12px;">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([
        ['from'=>'#3b82f6','to'=>'#1d4ed8','icon'=>'fa-tasks',    'val'=>$nilaiTugas->count(),           'label'=>'Nilai Tugas Tercatat'],
        ['from'=>'#7c3aed','to'=>'#6d28d9','icon'=>'fa-flask',    'val'=>$nilaiPraktik->count(),          'label'=>'Nilai Praktik Tercatat'],
        ['from'=>'#16a34a','to'=>'#15803d','icon'=>'fa-star',     'val'=>$stats['avg_tugas'],             'label'=>'Rata-rata Nilai Tugas'],
        ['from'=>'#0891b2','to'=>'#0e7490','icon'=>'fa-chart-bar','val'=>$stats['avg_praktik'],           'label'=>'Rata-rata Nilai Praktik'],
    ] as $s)
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;overflow:hidden;">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div style="width:44px;height:44px;border-radius:11px;background:linear-gradient(135deg,{{ $s['from'] }},{{ $s['to'] }});display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;flex-shrink:0;">
                    <i class="fas {{ $s['icon'] }}"></i>
                </div>
                <div>
                    <div class="fw-black text-dark" style="font-size:1.5rem;line-height:1;">{{ $s['val'] }}</div>
                    <div class="text-muted" style="font-size:.73rem;">{{ $s['label'] }}</div>
                </div>
            </div>
            <div style="height:3px;background:linear-gradient(90deg,{{ $s['from'] }},{{ $s['to'] }});"></div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('guru.laporan.nilai') }}" class="row g-2 align-items-end">
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
            <a href="{{ route('guru.laporan.nilai') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-3" id="nilaiTabs">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-tugas-nilai">
            <i class="fas fa-tasks me-1"></i>Nilai Tugas
            <span class="badge bg-primary ms-1">{{ $nilaiTugas->count() }}</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-praktik-nilai">
            <i class="fas fa-flask me-1"></i>Nilai Praktikum
            <span class="badge bg-purple ms-1" style="background:#7c3aed!important;">{{ $nilaiPraktik->count() }}</span>
        </button>
    </li>
</ul>

<div class="tab-content">
    {{-- Tab Nilai Tugas --}}
    <div class="tab-pane fade show active" id="tab-tugas-nilai">
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-body p-0">
                @if($nilaiTugas->isEmpty())
                <div class="text-center py-4 text-muted">Belum ada nilai tugas pada periode ini.</div>
                @else
                <div class="table-responsive">
                    <table class="table lap-tbl align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">Siswa</th>
                                <th class="py-3">Tugas</th>
                                <th class="py-3">Mata Pelajaran</th>
                                <th class="py-3">Dikumpulkan</th>
                                <th class="text-center py-3">Nilai</th>
                                <th class="text-center py-3">Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($nilaiTugas as $n)
                            @php
                                $score = (float)$n->score;
                                $grade = $score >= 90 ? 'A' : ($score >= 80 ? 'B' : ($score >= 70 ? 'C' : ($score >= 60 ? 'D' : 'E')));
                                $gc    = ['A'=>'success','B'=>'primary','C'=>'warning','D'=>'danger','E'=>'secondary'][$grade];
                            @endphp
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $n->siswa?->name ?? '—' }}</td>
                                <td>{{ $n->assignment?->title ?? '—' }}</td>
                                <td class="text-muted">{{ $n->assignment?->subject?->name ?? '—' }}</td>
                                <td class="text-muted" style="font-size:.8rem;">{{ $n->submitted_at?->format('d M Y') ?? '—' }}</td>
                                <td class="text-center fw-bold" style="color:{{ $score >= 80 ? '#16a34a' : ($score >= 60 ? '#d97706' : '#dc2626') }};">
                                    {{ number_format($score, 0) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $gc }}">{{ $grade }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Tab Nilai Praktik --}}
    <div class="tab-pane fade" id="tab-praktik-nilai">
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-body p-0">
                @if($nilaiPraktik->isEmpty())
                <div class="text-center py-4 text-muted">Belum ada nilai praktikum pada periode ini.</div>
                @else
                <div class="table-responsive">
                    <table class="table lap-tbl align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">Siswa</th>
                                <th class="py-3">Praktikum</th>
                                <th class="py-3">Mata Pelajaran</th>
                                <th class="py-3">Dinilai</th>
                                <th class="text-center py-3">Nilai</th>
                                <th class="text-center py-3">Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($nilaiPraktik as $np)
                            @php
                                $score2 = (float)$np->score;
                                $grade2 = $score2 >= 90 ? 'A' : ($score2 >= 80 ? 'B' : ($score2 >= 70 ? 'C' : ($score2 >= 60 ? 'D' : 'E')));
                                $gc2    = ['A'=>'success','B'=>'primary','C'=>'warning','D'=>'danger','E'=>'secondary'][$grade2];
                            @endphp
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $np->siswa?->name ?? '—' }}</td>
                                <td>{{ $np->practical?->title ?? '—' }}</td>
                                <td class="text-muted">{{ $np->practical?->subject?->name ?? '—' }}</td>
                                <td class="text-muted" style="font-size:.8rem;">{{ $np->graded_at?->format('d M Y') ?? '—' }}</td>
                                <td class="text-center fw-bold" style="color:{{ $score2 >= 80 ? '#16a34a' : ($score2 >= 60 ? '#d97706' : '#dc2626') }};">
                                    {{ number_format($score2, 0) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $gc2 }}">{{ $grade2 }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
