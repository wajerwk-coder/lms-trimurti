@extends('layouts.guru')

@section('title', 'Laporan Absensi')
@section('page-title', 'Laporan Absensi')
@section('page-subtitle', 'Rekap kehadiran siswa per mata pelajaran.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('guru.laporan.index') }}">Laporan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Absensi</li>
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
.av-sm {
    width:32px;height:32px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-weight:700;font-size:.78rem;color:#fff;flex-shrink:0;
}
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:12px;">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ── Stats ──────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @foreach([
        ['from'=>'#16a34a','to'=>'#15803d','icon'=>'fa-check-circle', 'val'=>$stats['present_count']   ?? 0,           'label'=>'Hadir'],
        ['from'=>'#d97706','to'=>'#b45309','icon'=>'fa-clock',        'val'=>$stats['absent_count']    ?? 0,           'label'=>'Tidak Hadir'],
        ['from'=>'#0891b2','to'=>'#0e7490','icon'=>'fa-notes-medical','val'=>$stats['izin_count']      ?? 0,           'label'=>'Izin / Sakit'],
        ['from'=>'#7c3aed','to'=>'#6d28d9','icon'=>'fa-percentage',   'val'=>($stats['attendance_rate'] ?? 0) . '%',   'label'=>'Tingkat Kehadiran'],
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
    <form id="filterForm" method="GET" action="{{ route('guru.laporan.absensi') }}"
          class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">Dari Tanggal</label>
            <input type="date" name="start_date" class="form-control form-control-sm"
                   value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">Sampai Tanggal</label>
            <input type="date" name="end_date" class="form-control form-control-sm"
                   value="{{ request('end_date', now()->format('Y-m-d')) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">Kelas</label>
            <select name="kelas" class="form-select form-select-sm">
                <option value="">Semua Kelas</option>
                @foreach(\App\Models\Kelas::orderBy('name')->get() as $k)
                    <option value="{{ $k->id }}" {{ request('kelas') == $k->id ? 'selected' : '' }}>
                        {{ $k->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                <i class="fas fa-search me-1"></i>Filter
            </button>
            <a href="{{ route('guru.laporan.absensi') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

{{-- ── Export PDF ─────────────────────────────────────────────── --}}
<div class="d-flex justify-content-end mb-3">
    <form method="POST" action="{{ route('guru.laporan.generate') }}" class="d-inline">
        @csrf
        <input type="hidden" name="type" value="absensi">
        <input type="hidden" name="format" value="pdf">
        <input type="hidden" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
        <input type="hidden" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}">
        <input type="hidden" name="kelas" value="{{ request('kelas') }}">
        <button type="submit" class="btn btn-danger btn-sm" style="border-radius:8px;">
            <i class="fas fa-file-pdf me-1"></i>Ekspor PDF
        </button>
    </form>
</div>

{{-- ── Tabel Detail Kehadiran ────────────────────────────────── --}}
<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4"
         style="border-radius:14px 14px 0 0;border-bottom:1px solid #e8edf2;">
        <h6 class="mb-0 fw-bold">
            <i class="fas fa-calendar-check me-2 text-info"></i>Detail Kehadiran Siswa
        </h6>
        @if(isset($attendances) && method_exists($attendances, 'total'))
            <span class="badge bg-secondary">{{ $attendances->total() }} record</span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table lap-tbl align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">Siswa</th>
                        <th class="py-3">Tanggal</th>
                        <th class="py-3">Mata Pelajaran</th>
                        <th class="text-center py-3">Status</th>
                        <th class="py-3">Keterangan</th>
                        <th class="py-3">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances ?? [] as $att)
                    @php
                        $siswaName = $att->siswa?->name ?? '—';
                        $initial   = strtoupper(substr($siswaName, 0, 1));
                        $colors    = ['#0891b2','#7c3aed','#16a34a','#d97706','#dc2626','#0f766e'];
                        $avBg      = $colors[abs(crc32($siswaName)) % count($colors)];
                        $statusColors = ['hadir'=>'success','izin'=>'warning','sakit'=>'info','alpha'=>'danger'];
                        $sc        = $statusColors[$att->status ?? ''] ?? 'secondary';
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="av-sm" style="background:{{ $avBg }};">{{ $initial }}</div>
                                <div>
                                    <div class="fw-semibold text-dark" style="font-size:.84rem;">{{ $siswaName }}</div>
                                    @php $siswaProfile = \App\Models\Siswa::where('user_id', $att->siswa?->id)->with('kelas')->first(); @endphp
                                    <div class="text-muted" style="font-size:.7rem;">
                                        {{ $siswaProfile?->kelas?->name ?? '' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:.82rem;">
                            {{ \Carbon\Carbon::parse($att->date ?? $att->tanggal)->format('d M Y') }}
                        </td>
                        <td class="text-muted" style="font-size:.82rem;">
                            {{ $att->subject?->name ?? '—' }}
                        </td>
                        <td class="text-center">
                            @php
                                $statusLabels = ['hadir'=>'Hadir','izin'=>'Izin','sakit'=>'Sakit','alpha'=>'Alpha'];
                                $sl = $statusLabels[$att->status ?? ''] ?? ucfirst($att->status ?? '—');
                            @endphp
                            <span class="badge bg-{{ $sc }}"
                                  style="border-radius:20px;font-size:.68rem;padding:.2rem .65rem;">
                                {{ $sl }}
                            </span>
                        </td>
                        <td class="text-muted" style="font-size:.8rem;">
                            {{ $att->note ?? $att->keterangan ?? '—' }}
                        </td>
                        <td class="text-muted" style="font-size:.8rem;">
                            {{ $att->recorder?->name ?? $att->createdBy?->name ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3"
                                 style="width:64px;height:64px;">
                                <i class="fas fa-calendar-times text-info fa-lg opacity-75"></i>
                            </div>
                            <div>Tidak ada data absensi untuk periode ini.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($attendances) && method_exists($attendances, 'hasPages') && $attendances->hasPages())
    <div class="card-footer bg-white border-top px-4 py-3">
        {{ $attendances->appends(request()->query())->links() }}
    </div>
    @endif
</div>

@endsection
