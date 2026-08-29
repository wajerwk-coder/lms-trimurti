@extends('layouts.siswa')

@section('title', 'Materi Pembelajaran')
@section('page-title', 'Materi Pembelajaran')
@section('page-subtitle', 'Materi yang diberikan guru untuk kelas Anda.')

@push('css')
<style>
/* ── Stats ─────────────────────────────────────────────── */
.mat-stat {
    border: none; border-radius: 14px; overflow: hidden;
    transition: transform .18s, box-shadow .18s;
}
.mat-stat:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(0,0,0,.09) !important; }
.mat-stat-icon {
    width: 44px; height: 44px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; color: #fff; flex-shrink: 0;
}

/* ── Material cards ─────────────────────────────────────── */
.mat-card {
    border: 1px solid #e8edf2 !important;
    border-radius: 14px !important;
    transition: transform .18s, box-shadow .18s, border-color .18s;
    overflow: hidden;
}
.mat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0,0,0,.10) !important;
    border-color: #c7d2fe !important;
}

/* ── Filter bar ─────────────────────────────────────────── */
.filter-bar {
    background: #fff;
    border: 1px solid #e8edf2;
    border-radius: 14px;
    padding: .875rem 1.25rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}
.filter-bar .input-group {
    border: 1.5px solid #e2e8f0; border-radius: 9px;
    overflow: hidden; background: #fff;
    transition: border-color .15s;
}
.filter-bar .input-group:focus-within {
    border-color: #7c3aed;
    box-shadow: 0 0 0 3px rgba(124,58,237,.09);
}
.filter-bar .input-group-text,
.filter-bar .form-control { border: none; background: transparent; box-shadow: none; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:12px;">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:12px;">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ══ STATS ════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    @foreach([
        ['from'=>'#7c3aed','to'=>'#6d28d9','icon'=>'fa-book',         'val'=>$materials->total(), 'label'=>'Total Materi',    'sub'=>'Tersedia untuk kamu'],
        ['from'=>'#16a34a','to'=>'#15803d','icon'=>'fa-download',     'val'=>$downloadedCount ?? 0,'label'=>'Sudah Diunduh',  'sub'=>'Oleh kamu'],
        ['from'=>'#0891b2','to'=>'#0e7490','icon'=>'fa-clock',        'val'=>$recentCount ?? 0,   'label'=>'Terbaru',         'sub'=>'7 hari terakhir'],
        ['from'=>'#d97706','to'=>'#b45309','icon'=>'fa-chalkboard-teacher','val'=>$subjects->count()??0,'label'=>'Mata Pelajaran','sub'=>'Tersedia'],
    ] as $s)
    <div class="col-6 col-md-3">
        <div class="card mat-stat shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="mat-stat-icon"
                     style="background:linear-gradient(135deg,{{ $s['from'] }},{{ $s['to'] }});">
                    <i class="fas {{ $s['icon'] }}"></i>
                </div>
                <div>
                    <div class="fw-black text-dark" style="font-size:1.55rem;line-height:1;letter-spacing:-.5px;">
                        {{ $s['val'] }}
                    </div>
                    <div class="fw-semibold text-dark" style="font-size:.78rem;">{{ $s['label'] }}</div>
                    <div class="text-muted" style="font-size:.68rem;">{{ $s['sub'] }}</div>
                </div>
            </div>
            <div style="height:3px;background:linear-gradient(90deg,{{ $s['from'] }},{{ $s['to'] }});"></div>
        </div>
    </div>
    @endforeach
</div>

{{-- ══ FILTER ═══════════════════════════════════════════════ --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('siswa.materials.index') }}"
          class="row g-2 align-items-end">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text ps-3">
                    <i class="fas fa-search text-muted" style="font-size:.8rem;"></i>
                </span>
                <input type="text" name="search" class="form-control"
                       placeholder="Cari judul materi…"
                       value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-4">
            <select name="subject" class="form-select" style="border-radius:9px;border:1.5px solid #e2e8f0;">
                <option value="">Semua Mata Pelajaran</option>
                @foreach($subjects ?? [] as $subj)
                    <option value="{{ $subj->id }}"
                        {{ request('subject') == $subj->id ? 'selected' : '' }}>
                        {{ $subj->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill" style="border-radius:9px;">
                <i class="fas fa-search me-1"></i>Cari
            </button>
            @if(request('search') || request('subject'))
                <a href="{{ route('siswa.materials.index') }}"
                   class="btn btn-outline-secondary" style="border-radius:9px;">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </div>
    </form>
</div>

{{-- ══ GRID MATERI ══════════════════════════════════════════ --}}
<div class="row g-3">
    @forelse($materials as $material)
    @php
        $ext = strtolower(pathinfo($material->file_url ?? '', PATHINFO_EXTENSION));
        [$fileIcon, $fileClr, $fileBg] = match(true) {
            $ext === 'pdf'                     => ['fa-file-pdf',        '#dc2626','rgba(220,38,38,.09)'],
            in_array($ext,['doc','docx'])       => ['fa-file-word',       '#3b82f6','rgba(59,130,246,.09)'],
            in_array($ext,['ppt','pptx'])       => ['fa-file-powerpoint', '#ea580c','rgba(234,88,12,.09)'],
            in_array($ext,['xls','xlsx'])       => ['fa-file-excel',      '#16a34a','rgba(22,163,74,.09)'],
            in_array($ext,['mp4','avi','mov'])  => ['fa-file-video',      '#0891b2','rgba(8,145,178,.09)'],
            in_array($ext,['jpg','jpeg','png']) => ['fa-file-image',      '#7c3aed','rgba(124,58,237,.09)'],
            in_array($ext,['zip','rar'])        => ['fa-file-archive',    '#64748b','rgba(100,116,139,.09)'],
            !empty($material->video_url)        => ['fa-play-circle',     '#dc2626','rgba(220,38,38,.09)'],
            default                             => ['fa-file-alt',        '#64748b','rgba(100,116,139,.09)'],
        };
        $isDownloaded = $material->downloads->isNotEmpty();
    @endphp
    <div class="col-md-6 col-xl-4">
        <div class="card mat-card shadow-sm h-100">

            {{-- Top bar --}}
            <div style="height:4px;background:{{ $fileClr }};"></div>

            <div class="card-body p-4">

                {{-- Header --}}
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:44px;height:44px;background:{{ $fileBg }};">
                        <i class="fas {{ $fileIcon }} fa-sm" style="color:{{ $fileClr }};"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <h6 class="fw-bold mb-1 text-truncate" style="font-size:.9rem;"
                            title="{{ $material->title }}">
                            {{ $material->title }}
                        </h6>
                        <div class="text-muted" style="font-size:.76rem;">
                            <i class="fas fa-user-tie me-1"></i>{{ $material->guru?->name ?? '—' }}
                        </div>
                    </div>
                    @if($isDownloaded)
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:22px;height:22px;background:rgba(22,163,74,.12);"
                             title="Sudah diunduh">
                            <i class="fas fa-check" style="color:#16a34a;font-size:.55rem;"></i>
                        </div>
                    @endif
                </div>

                {{-- Subject badge --}}
                @if($material->subject)
                    <div class="mb-2">
                        <span class="badge fw-semibold"
                              style="background:rgba(124,58,237,.1);color:#7c3aed;border-radius:20px;font-size:.7rem;padding:.2rem .6rem;">
                            <i class="fas fa-book me-1"></i>{{ $material->subject->name }}
                        </span>
                    </div>
                @endif

                {{-- Content preview --}}
                @if($material->content)
                    <p class="text-muted mb-0 lh-sm"
                       style="font-size:.79rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                        {{ strip_tags($material->content) }}
                    </p>
                @endif
            </div>

            {{-- Footer --}}
            <div class="px-4 pb-4">
                <div class="d-flex justify-content-between align-items-center mb-3"
                     style="font-size:.74rem;color:#94a3b8;">
                    <span>
                        <i class="fas fa-download me-1"></i>{{ $material->downloads_count ?? 0 }}x
                    </span>
                    <span>
                        <i class="fas fa-calendar me-1"></i>
                        {{ optional($material->published_at)->format('d M Y') ?? '—' }}
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('siswa.materials.show', $material->id) }}"
                       class="btn btn-sm flex-fill"
                       style="border-radius:8px;border:1.5px solid {{ $fileClr }};color:{{ $fileClr }};background:transparent;">
                        <i class="fas fa-eye me-1"></i>Detail
                    </a>
                    @if($material->file_url)
                        <a href="{{ route('siswa.materials.download', $material->id) }}"
                           class="btn btn-sm flex-fill"
                           style="border-radius:8px;background:{{ $fileClr }};color:#fff;border:none;">
                            <i class="fas fa-download me-1"></i>Unduh
                        </a>
                    @endif
                    @if($material->video_url)
                        <a href="{{ $material->video_url }}" target="_blank" rel="noopener"
                           class="btn btn-sm flex-shrink-0"
                           style="border-radius:8px;background:rgba(220,38,38,.1);color:#dc2626;border:1px solid rgba(220,38,38,.2);"
                           title="Tonton Video">
                            <i class="fas fa-play"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-body text-center py-5">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                     style="width:72px;height:72px;background:rgba(124,58,237,.08);">
                    <i class="fas fa-folder-open fa-2x" style="color:#7c3aed;opacity:.6;"></i>
                </div>
                <h5 class="fw-semibold text-muted mb-2">
                    {{ request('search') || request('subject') ? 'Tidak ditemukan' : 'Belum ada materi' }}
                </h5>
                <p class="text-muted small mb-3">
                    {{ request('search') || request('subject')
                        ? 'Tidak ada materi yang cocok dengan pencarian Anda.'
                        : 'Materi dari guru akan tampil di sini.' }}
                </p>
                @if(request('search') || request('subject'))
                    <a href="{{ route('siswa.materials.index') }}"
                       class="btn btn-outline-primary btn-sm" style="border-radius:8px;">
                        <i class="fas fa-times me-1"></i>Hapus Filter
                    </a>
                @endif
            </div>
        </div>
    </div>
    @endforelse
</div>

{{-- ══ PAGINATION ═══════════════════════════════════════════ --}}
@if($materials->hasPages())
<div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2 mt-4">
    <small class="text-muted">
        Menampilkan {{ $materials->firstItem() }}–{{ $materials->lastItem() }}
        dari {{ number_format($materials->total()) }} materi
    </small>
    {{ $materials->appends(request()->query())->links() }}
</div>
@endif

@endsection
