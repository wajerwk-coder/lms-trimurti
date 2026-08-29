@extends('layouts.siswa')

@section('title', $material->title)
@section('page-title', $material->title)
@section('page-subtitle', $material->subject?->name ?? 'Materi Pembelajaran')

@section('page-actions')
    <a href="{{ route('siswa.materials.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Semua Materi
    </a>
@endsection

@push('css')
<style>
.info-row {
    display: flex; align-items: center; gap: .65rem;
    padding: .5rem 0; border-bottom: 1px solid #f1f5f9;
    font-size: .84rem;
}
.info-row:last-child { border-bottom: none; }
.info-icon {
    width: 28px; height: 28px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: .65rem;
}
.progress-xs { height: 5px; border-radius: 3px; }
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

@php
    $ext = strtolower(pathinfo($material->file_url ?? '', PATHINFO_EXTENSION));
    [$fileIcon, $fileClr, $fileBg] = match(true) {
        $ext === 'pdf'                     => ['fa-file-pdf',        '#dc2626', 'rgba(220,38,38,.09)'],
        in_array($ext,['doc','docx'])       => ['fa-file-word',       '#3b82f6', 'rgba(59,130,246,.09)'],
        in_array($ext,['ppt','pptx'])       => ['fa-file-powerpoint', '#ea580c', 'rgba(234,88,12,.09)'],
        in_array($ext,['xls','xlsx'])       => ['fa-file-excel',      '#16a34a', 'rgba(22,163,74,.09)'],
        in_array($ext,['mp4','avi','mov'])  => ['fa-file-video',      '#0891b2', 'rgba(8,145,178,.09)'],
        in_array($ext,['jpg','jpeg','png']) => ['fa-file-image',      '#7c3aed', 'rgba(124,58,237,.09)'],
        in_array($ext,['zip','rar'])        => ['fa-file-archive',    '#64748b', 'rgba(100,116,139,.09)'],
        !empty($material->video_url)        => ['fa-play-circle',     '#dc2626', 'rgba(220,38,38,.09)'],
        default                             => ['fa-file-alt',        '#64748b', 'rgba(100,116,139,.09)'],
    };
@endphp

<div class="row g-4">

    {{-- ═══ KIRI ═══════════════════════════════════════════════════ --}}
    <div class="col-lg-8">

        {{-- Hero card --}}
        <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="border-radius:14px;">

            {{-- Top bar --}}
            <div style="height:5px;background:{{ $fileClr }};"></div>

            <div class="card-body p-4">

                {{-- Header --}}
                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:58px;height:58px;background:{{ $fileBg }};">
                        <i class="fas {{ $fileIcon }} fa-lg" style="color:{{ $fileClr }};"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="fw-bold mb-2">{{ $material->title }}</h4>
                        <div class="d-flex flex-wrap gap-2">
                            @if($material->subject)
                                <span class="badge fw-semibold"
                                      style="background:rgba(124,58,237,.1);color:#7c3aed;border-radius:20px;padding:.22rem .7rem;font-size:.72rem;">
                                    <i class="fas fa-book me-1"></i>{{ $material->subject->name }}
                                </span>
                            @endif
                            @if($ext)
                                <span class="badge fw-semibold"
                                      style="background:{{ $fileBg }};color:{{ $fileClr }};border-radius:20px;padding:.22rem .7rem;font-size:.72rem;">
                                    {{ strtoupper($ext) }}
                                </span>
                            @endif
                            @if($isDownloaded)
                                <span class="badge fw-semibold"
                                      style="background:rgba(22,163,74,.1);color:#16a34a;border-radius:20px;padding:.22rem .7rem;font-size:.72rem;">
                                    <i class="fas fa-check-circle me-1"></i>Sudah Diunduh
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Deskripsi --}}
                @if($material->content)
                <div class="mb-4">
                    <h6 class="fw-semibold mb-2" style="font-size:.85rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">
                        Deskripsi
                    </h6>
                    <div class="p-3 rounded-3 lh-lg"
                         style="background:#f8fafc;border:1px solid #e8edf2;font-size:.85rem;color:#475569;">
                        {!! nl2br(e(strip_tags($material->content))) !!}
                    </div>
                </div>
                @endif

                {{-- Stats bar --}}
                <div class="row g-0 border rounded-3 overflow-hidden" style="border-color:#e8edf2 !important;">
                    @foreach([
                        ['#7c3aed','fa-eye',      $material->views_count ?? 0,     'Dilihat'],
                        ['#16a34a','fa-download',  $material->downloads_count ?? 0, 'Diunduh'],
                        ['#0891b2','fa-calendar',  optional($material->published_at)->format('d M Y') ?? '—', 'Dipublikasikan'],
                    ] as [$clr, $ic, $v, $l])
                    <div class="col border-end py-3 text-center {{ $loop->last ? 'border-0' : '' }}"
                         style="border-color:#e8edf2 !important;">
                        <div class="fw-bold mb-1 lh-1" style="font-size:1.1rem;color:{{ $clr }};">{{ $v }}</div>
                        <div class="text-muted" style="font-size:.7rem;">
                            <i class="fas {{ $ic }} me-1"></i>{{ $l }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Actions --}}
            <div class="px-4 pb-4">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    @if($material->file_url)
                        <a href="{{ route('siswa.materials.download', $material->id) }}"
                           class="btn fw-semibold"
                           style="border-radius:10px;background:{{ $fileClr }};color:#fff;border:none;">
                            <i class="fas fa-download me-2"></i>Unduh Materi
                            <small class="opacity-75 ms-1">({{ strtoupper($ext) }})</small>
                        </a>
                    @endif
                    @if($material->video_url)
                        <a href="{{ $material->video_url }}" target="_blank" rel="noopener"
                           class="btn fw-semibold"
                           style="border-radius:10px;background:rgba(220,38,38,.1);color:#dc2626;border:1px solid rgba(220,38,38,.25);">
                            <i class="fas fa-play me-2"></i>Tonton Video
                        </a>
                    @endif
                    @if(!$material->file_url && !$material->video_url)
                        <span class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>Tidak ada file yang dapat diunduh.
                        </span>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- ═══ KANAN ════════════════════════════════════════════════════ --}}
    <div class="col-lg-4">

        {{-- Info Guru --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
            <div class="card-header bg-white border-bottom py-3 px-4"
                 style="border-radius:14px 14px 0 0;">
                <h6 class="mb-0 fw-semibold" style="font-size:.88rem;">
                    <i class="fas fa-user-tie me-2" style="color:#7c3aed;"></i>Dibuat Oleh
                </h6>
            </div>
            <div class="card-body px-4 py-3">
                <div class="d-flex align-items-center gap-3">
                    @php
                        $guruName   = $material->guru?->name ?? '—';
                        $guruPhoto  = $material->guru?->photo
                            ? asset('storage/' . $material->guru->photo)
                            : null;
                        $guruInitial = strtoupper(substr($guruName, 0, 1));
                    @endphp
                    @if($guruPhoto)
                        <img src="{{ $guruPhoto }}" alt="{{ $guruName }}"
                             class="rounded-circle flex-shrink-0"
                             style="width:48px;height:48px;object-fit:cover;"
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($guruName) }}&background=7c3aed&color=fff&size=48'">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                             style="width:48px;height:48px;background:linear-gradient(135deg,#7c3aed,#a21caf);font-size:1.1rem;">
                            {{ $guruInitial }}
                        </div>
                    @endif
                    <div>
                        <div class="fw-semibold text-dark">{{ $guruName }}</div>
                        <div class="text-muted" style="font-size:.75rem;">
                            {{ $material->guru?->email ?? '' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info Materi --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
            <div class="card-header bg-white border-bottom py-3 px-4"
                 style="border-radius:14px 14px 0 0;">
                <h6 class="mb-0 fw-semibold" style="font-size:.88rem;">
                    <i class="fas fa-info-circle me-2" style="color:#0891b2;"></i>Informasi
                </h6>
            </div>
            <div class="card-body px-4 py-3">
                @foreach([
                    ['fa-book',         'rgba(124,58,237,.09)', '#7c3aed', 'Mata Pelajaran', $material->subject?->name ?? '—'],
                    ['fa-file',         'rgba(100,116,139,.09)','#64748b', 'Format',         $ext ? strtoupper($ext) : 'Konten teks'],
                    ['fa-calendar-alt', 'rgba(8,145,178,.09)',  '#0891b2', 'Dipublikasikan', optional($material->published_at)->format('d M Y H:i') ?? '—'],
                    ['fa-eye',          'rgba(124,58,237,.09)', '#7c3aed', 'Total Dilihat',  ($material->views_count ?? 0) . 'x'],
                    ['fa-download',     'rgba(22,163,74,.09)',  '#16a34a', 'Total Diunduh',  ($material->downloads_count ?? 0) . 'x'],
                ] as [$ic, $ibg, $iclr, $label, $val])
                <div class="info-row">
                    <div class="info-icon" style="background:{{ $ibg }};">
                        <i class="fas {{ $ic }}" style="color:{{ $iclr }};"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:.67rem;text-transform:uppercase;letter-spacing:.05em;">{{ $label }}</div>
                        <div class="fw-semibold" style="font-size:.84rem;">{{ $val }}</div>
                    </div>
                </div>
                @endforeach

                {{-- Status unduhan --}}
                <div class="mt-3 pt-3 border-top">
                    @if($isDownloaded)
                        <div class="d-flex align-items-center gap-2 p-2 rounded-3"
                             style="background:rgba(22,163,74,.08);">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:26px;height:26px;background:rgba(22,163,74,.15);">
                                <i class="fas fa-check" style="color:#16a34a;font-size:.6rem;"></i>
                            </div>
                            <span class="fw-semibold" style="font-size:.82rem;color:#16a34a;">Sudah kamu unduh</span>
                        </div>
                    @else
                        <div class="d-flex align-items-center gap-2 p-2 rounded-3"
                             style="background:#f8fafc;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:26px;height:26px;background:#e8edf2;">
                                <i class="fas fa-download" style="color:#94a3b8;font-size:.6rem;"></i>
                            </div>
                            <span class="text-muted" style="font-size:.82rem;">Belum pernah diunduh</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Back button --}}
        <a href="{{ route('siswa.materials.index') }}"
           class="btn w-100"
           style="border-radius:10px;background:#f1f5f9;color:#475569;border:none;">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
        </a>

    </div>
</div>

@endsection
