@extends('layouts.guru')

@section('title', 'Pengumpulan Tugas')
@section('page-title', 'Pengumpulan Tugas')
@section('page-subtitle', 'Daftar tugas yang dikumpulkan oleh siswa.')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Pengumpulan Tugas</li>
@endsection

@push('css')
<style>
.stat-card-sub {
    border: none;
    border-radius: 14px;
    overflow: hidden;
    transition: transform .18s, box-shadow .18s;
}
.stat-card-sub:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 28px rgba(0,0,0,.10) !important;
}
.stat-icon-sub {
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; color: #fff; flex-shrink: 0;
}
.sub-table th {
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: #94a3b8;
    background: #f8fafc;
    border-bottom: 1px solid #e8edf2 !important;
}
.sub-table td { font-size: .85rem; vertical-align: middle; }
.sub-table tr:hover td { background: #f8fafc; }
.avatar-circle {
    width: 36px; height: 36px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .85rem; color: #fff; flex-shrink: 0;
}
.badge-pending  { background:#fef9c3;color:#a16207;border-radius:20px;font-size:.7rem;font-weight:600;padding:.22rem .65rem; }
.badge-graded   { background:#dcfce7;color:#16a34a;border-radius:20px;font-size:.7rem;font-weight:600;padding:.22rem .65rem; }
.filter-bar-sub {
    background: #fff;
    border: 1px solid #e8edf2;
    border-radius: 14px;
    padding: .875rem 1.25rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
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
@if(isset($error))
    <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius:12px;">
        <i class="fas fa-exclamation-circle me-2"></i>{{ $error }}
    </div>
@endif

{{-- ══ STATS ═════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    @foreach([
        ['from'=>'#0891b2','to'=>'#0e7490','icon'=>'fa-inbox',        'val'=>$stats['total_submissions'],'label'=>'Total Pengumpulan','sub'=>'Semua submission'],
        ['from'=>'#d97706','to'=>'#b45309','icon'=>'fa-clock',        'val'=>$stats['pending_grading'],  'label'=>'Belum Dinilai',    'sub'=>'Menunggu penilaian'],
        ['from'=>'#16a34a','to'=>'#15803d','icon'=>'fa-check-circle', 'val'=>$stats['graded'],           'label'=>'Sudah Dinilai',    'sub'=>'Telah diperiksa'],
        ['from'=>'#7c3aed','to'=>'#6d28d9','icon'=>'fa-star',         'val'=>$stats['average_score'],    'label'=>'Rata-rata Nilai',  'sub'=>'Dari semua submission'],
    ] as $s)
    <div class="col-6 col-md-3">
        <div class="card stat-card-sub shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="stat-icon-sub"
                     style="background:linear-gradient(135deg,{{ $s['from'] }},{{ $s['to'] }});">
                    <i class="fas {{ $s['icon'] }}"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark" style="font-size:1.6rem;line-height:1;letter-spacing:-.5px;">
                        {{ $s['val'] }}
                    </div>
                    <div class="fw-semibold text-dark" style="font-size:.8rem;">{{ $s['label'] }}</div>
                    <div class="text-muted" style="font-size:.7rem;">{{ $s['sub'] }}</div>
                </div>
            </div>
            <div style="height:3px;background:linear-gradient(90deg,{{ $s['from'] }},{{ $s['to'] }});"></div>
        </div>
    </div>
    @endforeach
</div>

{{-- ══ FILTER BAR ═════════════════════════════════════════════════════ --}}
<div class="filter-bar-sub">
    <form method="GET" action="{{ route('guru.submissions.index') }}"
          class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">
                <i class="fas fa-filter me-1 text-muted"></i>Status
            </label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua Status</option>
                <option value="graded"   {{ request('status') == 'graded'   ? 'selected' : '' }}>Sudah Dinilai</option>
                <option value="ungraded" {{ request('status') == 'ungraded' ? 'selected' : '' }}>Belum Dinilai</option>
            </select>
        </div>
        <div class="col-md-auto d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm px-3">
                <i class="fas fa-search me-1"></i>Filter
            </button>
            <a href="{{ route('guru.submissions.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fas fa-times me-1"></i>Reset
            </a>
        </div>
    </form>
</div>

{{-- ══ TABLE ════════════════════════════════════════════════════════════ --}}
<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4"
         style="border-radius:14px 14px 0 0;border-bottom:1px solid #e8edf2;">
        <div>
            <h6 class="mb-0 fw-bold">Daftar Pengumpulan</h6>
            @if($allSubmissions instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <small class="text-muted">{{ number_format($allSubmissions->total()) }} submission</small>
            @endif
        </div>
        <a href="{{ route('guru.assignments.index') }}" class="btn btn-sm btn-outline-primary"
           style="border-radius:8px;">
            <i class="fas fa-tasks me-1"></i>Kelola Tugas
        </a>
    </div>

    <div class="card-body p-0">
        @if(($allSubmissions instanceof \Illuminate\Pagination\LengthAwarePaginator ? $allSubmissions->total() : $allSubmissions->count()) > 0)
        <div class="table-responsive">
            <table class="table sub-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">#</th>
                        <th class="py-3">Siswa</th>
                        <th class="py-3">Tugas</th>
                        <th class="py-3">Mata Pelajaran</th>
                        <th class="py-3">Dikumpulkan</th>
                        <th class="text-center py-3">Status</th>
                        <th class="text-center py-3">Nilai</th>
                        <th class="text-center py-3 pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allSubmissions as $i => $sub)
                    @php
                        $siswaName  = $sub->siswa?->name ?? '—';
                        $initial    = strtoupper(substr($siswaName, 0, 1));
                        $colors     = ['#0891b2','#7c3aed','#16a34a','#d97706','#dc2626','#0f766e'];
                        $bgColor    = $colors[abs(crc32($siswaName)) % count($colors)];
                        $isGraded   = !is_null($sub->score);
                        $isLate     = $sub->is_late ?? false;
                    @endphp
                    <tr>
                        <td class="ps-4 text-muted" style="font-size:.8rem;">
                            {{ $allSubmissions->firstItem() + $loop->index }}
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-circle flex-shrink-0"
                                     style="background:{{ $bgColor }};">
                                    {{ $initial }}
                                </div>
                                <div style="min-width:0;">
                                    <div class="fw-semibold text-dark text-truncate"
                                         style="max-width:140px;">
                                        {{ $siswaName }}
                                    </div>
                                    <div class="text-muted" style="font-size:.72rem;">
                                        {{ $sub->siswa?->email ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium text-dark"
                                 style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                                 title="{{ $sub->assignment?->title ?? '—' }}">
                                {{ $sub->assignment?->title ?? '—' }}
                            </div>
                            @if($isLate)
                                <span style="font-size:.68rem;color:#dc2626;font-weight:600;">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Terlambat
                                </span>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size:.8rem;">
                            {{ $sub->assignment?->subject?->name ?? '—' }}
                        </td>
                        <td>
                            <div style="font-size:.8rem;">
                                {{ $sub->submitted_at?->format('d M Y') ?? '—' }}
                            </div>
                            <div class="text-muted" style="font-size:.7rem;">
                                {{ $sub->submitted_at?->format('H:i') ?? '' }}
                            </div>
                        </td>
                        <td class="text-center">
                            @if($isGraded)
                                <span class="badge-graded">
                                    <i class="fas fa-check me-1"></i>Dinilai
                                </span>
                            @else
                                <span class="badge-pending">
                                    <i class="fas fa-clock me-1"></i>Menunggu
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($isGraded)
                                @php
                                    $score = (float) $sub->score;
                                    $scoreColor = $score >= 80 ? '#16a34a' : ($score >= 60 ? '#d97706' : '#dc2626');
                                @endphp
                                <span class="fw-bold" style="font-size:1rem;color:{{ $scoreColor }};">
                                    {{ number_format($score, 0) }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('guru.submissions.show', $sub->id) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   style="border-radius:7px;width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                                   title="Lihat Detail">
                                    <i class="fas fa-eye" style="font-size:.7rem;"></i>
                                </a>
                                @if(!$isGraded)
                                    <a href="{{ route('guru.penilaian.edit', $sub->id) }}"
                                       class="btn btn-sm btn-outline-warning"
                                       style="border-radius:7px;width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                                       title="Beri Nilai">
                                        <i class="fas fa-star" style="font-size:.7rem;"></i>
                                    </a>
                                @else
                                    <a href="{{ route('guru.penilaian.edit', $sub->id) }}"
                                       class="btn btn-sm btn-outline-success"
                                       style="border-radius:7px;width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                                       title="Edit Nilai">
                                        <i class="fas fa-edit" style="font-size:.7rem;"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($allSubmissions instanceof \Illuminate\Pagination\LengthAwarePaginator && $allSubmissions->hasPages())
        <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2 px-4 py-3 border-top">
            <small class="text-muted">
                Menampilkan {{ $allSubmissions->firstItem() }}–{{ $allSubmissions->lastItem() }}
                dari {{ number_format($allSubmissions->total()) }} submission
            </small>
            {{ $allSubmissions->appends(request()->query())->links() }}
        </div>
        @endif

        @else
        <div class="text-center py-5">
            <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center
                         justify-content-center mb-3"
                 style="width:72px;height:72px;">
                <i class="fas fa-inbox text-info fa-2x opacity-75"></i>
            </div>
            <h5 class="text-muted mb-2">Belum Ada Pengumpulan</h5>
            <p class="text-muted small mb-4">
                @if(request('status'))
                    Tidak ada submission dengan filter ini.
                @else
                    Siswa belum mengumpulkan tugas apapun.
                @endif
            </p>
            @if(request()->anyFilled(['status']))
                <a href="{{ route('guru.submissions.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="fas fa-times me-1"></i>Hapus Filter
                </a>
            @endif
            <a href="{{ route('guru.assignments.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Buat Tugas Baru
            </a>
        </div>
        @endif
    </div>
</div>

@endsection
