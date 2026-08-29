@extends('layouts.guru')

@section('title', 'Laporan Praktikum')
@section('page-title', 'Laporan Praktikum')
@section('page-subtitle', 'Data sesi praktikum dan penilaian siswa.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('guru.laporan.index') }}">Laporan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Praktikum</li>
@endsection

@section('page-actions')
    <a href="{{ route('guru.laporan.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@push('css')
<style>
.lap-tbl th {
    font-size:.72rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
    color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e8edf2!important;
}
.lap-tbl td { font-size:.84rem;vertical-align:middle; }
.lap-tbl tr:hover td { background:#f8fafc; }
.filter-bar {
    background:#fff;border:1px solid #e8edf2;border-radius:14px;
    padding:.875rem 1.25rem;margin-bottom:1.25rem;
    box-shadow:0 2px 8px rgba(0,0,0,.04);
}
</style>
@endpush

@section('content')

{{-- ── Stats ──────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @foreach([
        ['from'=>'#d97706','to'=>'#b45309','icon'=>'fa-flask',        'val'=>$practicals->total()                             ?? 0, 'label'=>'Total Praktikum'],
        ['from'=>'#16a34a','to'=>'#15803d','icon'=>'fa-users',        'val'=>$practicalStats['total_siswa']                   ?? 0, 'label'=>'Siswa Dinilai'],
        ['from'=>'#7c3aed','to'=>'#6d28d9','icon'=>'fa-star',         'val'=>number_format($practicalStats['average_score'] ?? 0, 1), 'label'=>'Rata-rata Nilai'],
        ['from'=>'#0891b2','to'=>'#0e7490','icon'=>'fa-clipboard-check','val'=>$practicalStats['total_graded']                ?? 0, 'label'=>'Total Penilaian'],
    ] as $s)
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;overflow:hidden;">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div style="width:44px;height:44px;border-radius:11px;background:linear-gradient(135deg,{{ $s['from'] }},{{ $s['to'] }});display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;flex-shrink:0;">
                    <i class="fas {{ $s['icon'] }}"></i>
                </div>
                <div>
                    <div class="fw-black text-dark" style="font-size:1.5rem;line-height:1;letter-spacing:-.5px;">
                        {{ $s['val'] }}
                    </div>
                    <div class="text-muted" style="font-size:.73rem;">{{ $s['label'] }}</div>
                </div>
            </div>
            <div style="height:3px;background:linear-gradient(90deg,{{ $s['from'] }},{{ $s['to'] }});"></div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Filter ─────────────────────────────────────────────────── --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('guru.laporan.praktik') }}" class="row g-2 align-items-end">
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
            <select name="kelas" class="form-select form-select-sm">
                <option value="">Semua Kelas</option>
                @foreach($classes as $id => $name)
                    <option value="{{ $id }}" {{ ($filters['kelas'] ?? '') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                <i class="fas fa-search me-1"></i>Filter
            </button>
            <a href="{{ route('guru.laporan.praktik') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

{{-- ── Export PDF ─────────────────────────────────────────────── --}}
<div class="d-flex justify-content-end mb-3">
    <form method="POST" action="{{ route('guru.laporan.generate') }}" class="d-inline">
        @csrf
        <input type="hidden" name="type" value="praktik">
        <input type="hidden" name="format" value="pdf">
        <input type="hidden" name="start_date" value="{{ $filters['start_date'] }}">
        <input type="hidden" name="end_date" value="{{ $filters['end_date'] }}">
        <input type="hidden" name="kelas" value="{{ $filters['kelas'] ?? '' }}">
        <button type="submit" class="btn btn-danger btn-sm" style="border-radius:8px;">
            <i class="fas fa-file-pdf me-1"></i>Ekspor PDF
        </button>
    </form>
</div>

{{-- ── Tabel ──────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4"
         style="border-radius:14px 14px 0 0;border-bottom:1px solid #e8edf2;">
        <h6 class="mb-0 fw-bold">
            <i class="fas fa-flask me-2 text-warning"></i>Daftar Praktikum
        </h6>
        <span class="badge bg-secondary">{{ $practicals->total() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table lap-tbl align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">Judul Praktikum</th>
                        <th class="py-3">Mata Pelajaran</th>
                        <th class="py-3">Kelas</th>
                        <th class="py-3">Batas Waktu</th>
                        <th class="text-center py-3">Penilaian</th>
                        <th class="text-center py-3">Rata-rata</th>
                        <th class="text-center pe-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($practicals as $p)
                    @php
                        $isPast   = $p->due_date?->isPast();
                        $avgScore = \App\Models\NilaiPraktik::where('practical_id', $p->id)
                            ->whereNull('criteria_id')->whereNotNull('score')->avg('score');
                        $sc2 = $avgScore >= 80 ? '#16a34a' : ($avgScore >= 60 ? '#d97706' : '#dc2626');
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold text-dark">{{ $p->title }}</div>
                            <div class="text-muted" style="font-size:.75rem;">
                                {{ Str::limit($p->description ?? '', 55) }}
                            </div>
                        </td>
                        <td class="text-muted" style="font-size:.82rem;">{{ $p->subject?->name ?? '—' }}</td>
                        <td class="text-muted" style="font-size:.82rem;">{{ $p->kelas?->name ?? 'Semua' }}</td>
                        <td style="font-size:.82rem;">
                            <div class="{{ $isPast ? 'text-danger' : 'text-dark' }}">
                                {{ $p->due_date?->format('d M Y') ?? '—' }}
                            </div>
                            @if($isPast)
                                <span style="font-size:.68rem;color:#dc2626;font-weight:600;">Lewat deadline</span>
                            @endif
                        </td>
                        <td class="text-center fw-semibold">{{ $p->scores_count ?? 0 }}</td>
                        <td class="text-center">
                            @if($avgScore !== null)
                                <span class="fw-bold" style="color:{{ $sc2 }};">{{ number_format($avgScore, 1) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            @if($p->is_published)
                                <span class="badge" style="background:#dcfce7;color:#16a34a;border-radius:20px;font-size:.68rem;">Terbit</span>
                            @else
                                <span class="badge" style="background:#f1f5f9;color:#64748b;border-radius:20px;font-size:.68rem;">Draft</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3"
                                 style="width:64px;height:64px;">
                                <i class="fas fa-flask text-warning fa-lg opacity-75"></i>
                            </div>
                            <div>Tidak ada data praktikum dalam periode ini.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($practicals->hasPages())
    <div class="card-footer bg-white border-top px-4 py-3">
        {{ $practicals->appends(request()->query())->links() }}
    </div>
    @endif
</div>

@endsection
