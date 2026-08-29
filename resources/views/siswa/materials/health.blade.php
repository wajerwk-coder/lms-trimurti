@extends('layouts.siswa')

@section('title', 'Materi Kesehatan')
@section('page-title', 'Materi Kesehatan')
@section('page-subtitle', 'Kumpulan materi pembelajaran bidang kesehatan.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('siswa.materials.index') }}">Materi</a></li>
    <li class="breadcrumb-item active" aria-current="page">Kesehatan</li>
@endsection

@section('content')
<div class="row g-3">
    @forelse($materials as $mat)
    @php
        $ext = strtolower(pathinfo($mat->file_url ?? '', PATHINFO_EXTENSION));
        [$fileIcon, $fileBg, $fileColor] = match(true) {
            in_array($ext, ['pdf'])          => ['fa-file-pdf',  '#fee2e2','#dc2626'],
            in_array($ext, ['doc','docx'])   => ['fa-file-word', '#dbeafe','#3b82f6'],
            in_array($ext, ['ppt','pptx'])   => ['fa-file-powerpoint','#fff7ed','#ea580c'],
            !empty($mat->video_url)          => ['fa-play-circle','#e0f2fe','#0891b2'],
            default                          => ['fa-file-alt',  '#f1f5f9','#64748b'],
        };
    @endphp
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px;border:1px solid #e8edf2!important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-start gap-3 mb-2">
                    <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:38px;height:38px;background:{{ $fileBg }};">
                        <i class="fas {{ $fileIcon }}" style="color:{{ $fileColor }};font-size:.9rem;"></i>
                    </div>
                    <div style="min-width:0;">
                        <div class="fw-semibold text-dark text-truncate" style="font-size:.88rem;">
                            {{ $mat->title }}
                        </div>
                        <div class="text-muted" style="font-size:.75rem;">
                            {{ $mat->subject?->name ?? '—' }}
                        </div>
                    </div>
                </div>
                @if($mat->content)
                <p class="text-muted mb-3"
                   style="font-size:.78rem;line-height:1.5;height:38px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                    {{ strip_tags($mat->content) }}
                </p>
                @endif
                <a href="{{ route('siswa.materials.show', $mat->id) }}"
                   class="btn btn-sm btn-outline-primary w-100" style="border-radius:8px;font-size:.78rem;">
                    <i class="fas fa-eye me-1"></i>Lihat Materi
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5 text-muted">
        <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3"
             style="width:64px;height:64px;">
            <i class="fas fa-book text-info fa-lg opacity-75"></i>
        </div>
        <h6 class="text-muted mb-1">Belum Ada Materi</h6>
        <p class="small mb-3">Materi kesehatan akan tampil di sini.</p>
        <a href="{{ route('siswa.materials.index') }}" class="btn btn-outline-primary btn-sm">
            Lihat Semua Materi
        </a>
    </div>
    @endforelse
</div>
@if(isset($materials) && $materials->hasPages())
<div class="mt-4">{{ $materials->links() }}</div>
@endif
@endsection
