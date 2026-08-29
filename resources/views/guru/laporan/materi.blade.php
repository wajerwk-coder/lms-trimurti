@extends('layouts.guru')

@section('title', 'Laporan Materi')
@section('page-title', 'Laporan Materi')
@section('page-subtitle', 'Data unggahan materi dan statistik unduhan.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('guru.laporan.index') }}">Laporan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Materi</li>
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
        ['from'=>'#16a34a','to'=>'#15803d','icon'=>'fa-book-open',  'val'=>$materials->total()                   ?? 0, 'label'=>'Total Materi'],
        ['from'=>'#0891b2','to'=>'#0e7490','icon'=>'fa-download',   'val'=>$materialStats['total_downloads']     ?? 0, 'label'=>'Total Unduhan'],
        ['from'=>'#7c3aed','to'=>'#6d28d9','icon'=>'fa-eye',        'val'=>$materialStats['total_views']         ?? 0, 'label'=>'Total Ditampilkan'],
        ['from'=>'#d97706','to'=>'#b45309','icon'=>'fa-trophy',     'val'=>Str::limit($materialStats['most_downloaded']?->title ?? '—', 18), 'label'=>'Terpopuler'],
    ] as $s)
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;overflow:hidden;">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div style="width:44px;height:44px;border-radius:11px;background:linear-gradient(135deg,{{ $s['from'] }},{{ $s['to'] }});display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;flex-shrink:0;">
                    <i class="fas {{ $s['icon'] }}"></i>
                </div>
                <div style="min-width:0;">
                    <div class="fw-black text-dark text-truncate"
                         style="font-size:{{ strlen((string)$s['val']) > 10 ? '1rem' : '1.5rem' }};line-height:1.2;letter-spacing:-.5px;"
                         title="{{ $s['val'] }}">
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
    <form method="GET" action="{{ route('guru.laporan.materi') }}" class="row g-2 align-items-end">
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
            <label class="form-label small fw-semibold mb-1">Mata Pelajaran</label>
            <select name="subject_id" class="form-select form-select-sm">
                <option value="">Semua Mata Pelajaran</option>
                @foreach(\App\Models\Subject::where('is_active', true)->orderBy('name')->get() as $s2)
                    <option value="{{ $s2->id }}" {{ request('subject_id') == $s2->id ? 'selected' : '' }}>
                        {{ $s2->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                <i class="fas fa-search me-1"></i>Filter
            </button>
            <a href="{{ route('guru.laporan.materi') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

{{-- ── Export PDF ─────────────────────────────────────────────── --}}
<div class="d-flex justify-content-end mb-3">
    <form method="POST" action="{{ route('guru.laporan.generate') }}" class="d-inline">
        @csrf
        <input type="hidden" name="type" value="materi">
        <input type="hidden" name="format" value="pdf">
        <input type="hidden" name="start_date" value="{{ $filters['start_date'] }}">
        <input type="hidden" name="end_date" value="{{ $filters['end_date'] }}">
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
            <i class="fas fa-book-open me-2 text-success"></i>Daftar Materi
        </h6>
        <span class="badge bg-secondary">{{ $materials->total() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table lap-tbl align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">Judul Materi</th>
                        <th class="py-3">Mata Pelajaran</th>
                        <th class="text-center py-3">Unduhan</th>
                        <th class="text-center py-3">Ditampilkan</th>
                        <th class="py-3">Tanggal Upload</th>
                        <th class="text-center pe-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materials as $m)
                    @php
                        $ext = strtolower(pathinfo($m->file_url ?? '', PATHINFO_EXTENSION));
                        [$fileIcon, $fileColor] = match(true) {
                            in_array($ext, ['pdf'])          => ['fa-file-pdf',        '#dc2626'],
                            in_array($ext, ['doc','docx'])   => ['fa-file-word',       '#3b82f6'],
                            in_array($ext, ['ppt','pptx'])   => ['fa-file-powerpoint', '#ea580c'],
                            in_array($ext, ['xls','xlsx'])   => ['fa-file-excel',      '#16a34a'],
                            !empty($m->video_url)            => ['fa-play-circle',     '#0891b2'],
                            default                          => ['fa-file-alt',        '#64748b'],
                        };
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas {{ $fileIcon }} fa-sm" style="color:{{ $fileColor }};flex-shrink:0;"></i>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $m->title }}</div>
                                    @if($m->file_url)
                                        <div class="text-muted" style="font-size:.72rem;">
                                            {{ $ext ? strtoupper($ext) : 'File' }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-muted" style="font-size:.82rem;">
                            {{ $m->subject?->name ?? '—' }}
                        </td>
                        <td class="text-center fw-semibold" style="color:#16a34a;">
                            {{ $m->downloads_count ?? 0 }}
                        </td>
                        <td class="text-center text-muted">{{ $m->views_count ?? 0 }}</td>
                        <td style="font-size:.82rem;color:#64748b;">
                            {{ $m->created_at?->format('d M Y') ?? '—' }}
                        </td>
                        <td class="text-center pe-4">
                            @if($m->published_at)
                                <span class="badge" style="background:#dcfce7;color:#16a34a;border-radius:20px;font-size:.68rem;">Terbit</span>
                            @else
                                <span class="badge" style="background:#f1f5f9;color:#64748b;border-radius:20px;font-size:.68rem;">Draft</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3"
                                 style="width:64px;height:64px;">
                                <i class="fas fa-book-open text-success fa-lg opacity-75"></i>
                            </div>
                            <div>Tidak ada materi dalam periode ini.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($materials->hasPages())
    <div class="card-footer bg-white border-top px-4 py-3">
        {{ $materials->appends(request()->query())->links() }}
    </div>
    @endif
</div>

@endsection
