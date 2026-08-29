@extends('layouts.guru')

@section('title', 'Materi Pembelajaran')
@section('page-title', 'Materi Pembelajaran')
@section('page-subtitle', 'Kelola dan bagikan materi pembelajaran untuk siswa.')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Materi</li>
@endsection

@section('page-actions')
    <a href="{{ route('guru.materials.create') }}" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus me-2"></i>Tambah Materi
    </a>
@endsection

@push('css')
<style>
/* ── Stats ─────────────────────────────────────────────── */
.stat-card-mat {
    border: none;
    border-radius: 14px;
    transition: transform .2s, box-shadow .2s;
    overflow: hidden;
}
.stat-card-mat:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 28px rgba(0,0,0,.10) !important;
}
.stat-icon-mat {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    color: #fff;
    flex-shrink: 0;
}
.stat-val-mat {
    font-size: 1.8rem;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -.5px;
}

/* ── Material cards ────────────────────────────────────── */
.mat-card {
    border: 1px solid #e8edf2 !important;
    border-radius: 14px !important;
    transition: transform .18s, box-shadow .18s, border-color .18s;
}
.mat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(8,145,178,.12) !important;
    border-color: #bae6fd !important;
}
.mat-file-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.mat-card .card-header {
    border-radius: 14px 14px 0 0 !important;
    background: #f8fafc !important;
    border-bottom: 1px solid #e8edf2 !important;
    padding: .75rem 1rem;
}
.mat-card .card-footer {
    border-radius: 0 0 14px 14px !important;
    background: #f8fafc !important;
    border-top: 1px solid #f1f5f9 !important;
    padding: .6rem 1rem;
}
.mat-badge-pub {
    background: #dcfce7; color: #16a34a;
    border-radius: 20px; font-size: .7rem; font-weight: 600;
    padding: .2rem .65rem;
}
.mat-badge-draft {
    background: #fef9c3; color: #a16207;
    border-radius: 20px; font-size: .7rem; font-weight: 600;
    padding: .2rem .65rem;
}

/* ── Filter bar ────────────────────────────────────────── */
.filter-bar {
    background: #fff;
    border: 1px solid #e8edf2;
    border-radius: 14px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}
.filter-bar .form-control,
.filter-bar .form-select {
    border-radius: 8px;
    font-size: .85rem;
}

/* ── Empty state ───────────────────────────────────────── */
.empty-state-icon {
    width: 80px; height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e0f2fe, #bae6fd);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1rem;
}
</style>
@endpush

@section('content')

{{-- Flash --}}
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

{{-- ══ STATS ═══════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    @foreach([
        ['from'=>'#0891b2','to'=>'#0e7490','icon'=>'fa-book-open',    'val'=>$materials->total(), 'label'=>'Total Materi',    'sub'=>'Semua materi'],
        ['from'=>'#16a34a','to'=>'#15803d','icon'=>'fa-check-circle', 'val'=>$totalPublished,     'label'=>'Diterbitkan',     'sub'=>'Terlihat siswa'],
        ['from'=>'#ca8a04','to'=>'#a16207','icon'=>'fa-clock',        'val'=>$totalDraft,         'label'=>'Draft',           'sub'=>'Belum diterbitkan'],
        ['from'=>'#7c3aed','to'=>'#6d28d9','icon'=>'fa-download',     'val'=>$totalDownloads,     'label'=>'Total Unduhan',   'sub'=>'Oleh siswa'],
    ] as $s)
    <div class="col-6 col-md-3">
        <div class="card stat-card-mat shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="stat-icon-mat"
                     style="background:linear-gradient(135deg,{{ $s['from'] }},{{ $s['to'] }});">
                    <i class="fas {{ $s['icon'] }}"></i>
                </div>
                <div>
                    <div class="stat-val-mat text-dark">{{ number_format($s['val']) }}</div>
                    <div class="fw-semibold text-dark" style="font-size:.8rem;">{{ $s['label'] }}</div>
                    <div class="text-muted" style="font-size:.7rem;">{{ $s['sub'] }}</div>
                </div>
            </div>
            <div style="height:3px;background:linear-gradient(90deg,{{ $s['from'] }},{{ $s['to'] }});"></div>
        </div>
    </div>
    @endforeach
</div>

{{-- ══ FILTER BAR ══════════════════════════════════════════════════ --}}
<div class="filter-bar">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold mb-1">
                <i class="fas fa-search me-1 text-muted"></i>Cari Materi
            </label>
            <input type="text" id="searchInput" class="form-control"
                   placeholder="Cari judul atau deskripsi...">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">
                <i class="fas fa-book me-1 text-muted"></i>Mata Pelajaran
            </label>
            <select id="subjectFilter" class="form-select">
                <option value="">Semua Mata Pelajaran</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">
                <i class="fas fa-toggle-on me-1 text-muted"></i>Status
            </label>
            <select id="statusFilter" class="form-select">
                <option value="">Semua</option>
                <option value="published">Diterbitkan</option>
                <option value="draft">Draft</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary flex-fill" id="resetFilter">
                <i class="fas fa-times me-1"></i>Reset
            </button>
            <button type="button" class="btn btn-danger d-none" id="bulkDeleteBtn"
                    onclick="doBulkDelete()">
                <i class="fas fa-trash me-1"></i>
                Hapus (<span id="bulkCount">0</span>)
            </button>
        </div>
    </div>
</div>

{{-- ══ SELECT ALL BAR ══════════════════════════════════════════════ --}}
<div class="d-flex align-items-center gap-3 mb-3">
    <div class="form-check">
        <input type="checkbox" id="selectAll" class="form-check-input">
        <label class="form-check-label small fw-semibold" for="selectAll">Pilih Semua</label>
    </div>
    <small id="resultCount" class="text-muted"></small>
</div>

{{-- ══ MATERIALS GRID ══════════════════════════════════════════════ --}}
<div class="row g-3" id="materialsContainer">
    @forelse($materials as $material)
    @php
        $ext = strtolower(pathinfo($material->file_url ?? '', PATHINFO_EXTENSION));
        [$fileIcon, $fileBg, $fileColor] = match(true) {
            in_array($ext, ['pdf'])                     => ['fa-file-pdf',        '#fee2e2', '#dc2626'],
            in_array($ext, ['doc','docx'])              => ['fa-file-word',       '#dbeafe', '#3b82f6'],
            in_array($ext, ['ppt','pptx'])              => ['fa-file-powerpoint', '#fff7ed', '#ea580c'],
            in_array($ext, ['xls','xlsx'])              => ['fa-file-excel',      '#dcfce7', '#16a34a'],
            in_array($ext, ['zip','rar'])               => ['fa-file-archive',    '#f3e8ff', '#7c3aed'],
            in_array($ext, ['mp4','avi','mov','mkv'])   => ['fa-file-video',      '#e0f2fe', '#0891b2'],
            !empty($material->video_url)                => ['fa-play-circle',     '#e0f2fe', '#0891b2'],
            default                                     => ['fa-file-alt',        '#f1f5f9', '#64748b'],
        };
        $isPublished = !is_null($material->published_at);
    @endphp

    <div class="col-xl-4 col-lg-6 mat-col"
         data-subject="{{ $material->subject_id }}"
         data-status="{{ $isPublished ? 'published' : 'draft' }}"
         data-title="{{ strtolower($material->title) }}">
        <div class="card mat-card h-100 shadow-sm">

            {{-- Header --}}
            <div class="card-header d-flex align-items-start gap-3">
                <div class="mat-file-icon flex-shrink-0"
                     style="background:{{ $fileBg }};">
                    <i class="fas {{ $fileIcon }}" style="color:{{ $fileColor }};"></i>
                </div>
                <div class="flex-grow-1" style="min-width:0;">
                    <h6 class="mb-1 fw-semibold text-dark text-truncate"
                        title="{{ $material->title }}" style="font-size:.88rem;">
                        {{ $material->title }}
                    </h6>
                    <div class="text-muted" style="font-size:.75rem;">
                        <i class="fas fa-book me-1"></i>
                        {{ $material->subject?->name ?? '—' }}
                    </div>
                </div>
                <span class="{{ $isPublished ? 'mat-badge-pub' : 'mat-badge-draft' }} flex-shrink-0">
                    {{ $isPublished ? 'Terbit' : 'Draft' }}
                </span>
            </div>

            {{-- Body --}}
            <div class="card-body py-3 px-3">
                {{-- Deskripsi --}}
                <p class="text-muted mb-3"
                   style="font-size:.8rem;line-height:1.55;height:52px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                    {{ strip_tags($material->content ?? 'Tidak ada deskripsi.') }}
                </p>

                {{-- Meta info --}}
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-1">
                            <div class="rounded-2 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                 style="width:26px;height:26px;">
                                <i class="fas fa-download text-primary" style="font-size:.65rem;"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark" style="font-size:.78rem;">
                                    {{ number_format($material->downloads_count ?? 0) }}
                                </div>
                                <div class="text-muted" style="font-size:.65rem;">Unduhan</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-1">
                            <div class="rounded-2 bg-info bg-opacity-10 d-flex align-items-center justify-content-center"
                                 style="width:26px;height:26px;">
                                <i class="fas fa-calendar text-info" style="font-size:.65rem;"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark" style="font-size:.78rem;">
                                    {{ $material->created_at->format('d M Y') }}
                                </div>
                                <div class="text-muted" style="font-size:.65rem;">Ditambahkan</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="d-flex gap-2">
                    <a href="{{ route('guru.materials.show', $material->id) }}"
                       class="btn btn-sm btn-primary flex-fill" style="border-radius:8px;">
                        <i class="fas fa-eye me-1"></i>Lihat
                    </a>
                    <a href="{{ route('guru.materials.edit', $material->id) }}"
                       class="btn btn-sm btn-outline-secondary" style="border-radius:8px;"
                       title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    @if($material->file_url)
                    <a href="{{ route('guru.materials.download', $material->id) }}"
                       class="btn btn-sm btn-outline-success" style="border-radius:8px;"
                       title="Unduh">
                        <i class="fas fa-download"></i>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Footer --}}
            <div class="card-footer d-flex align-items-center justify-content-between">
                <div class="form-check mb-0">
                    <input type="checkbox" class="form-check-input mat-check"
                           value="{{ $material->id }}" id="mc{{ $material->id }}">
                    <label class="form-check-label small text-muted" for="mc{{ $material->id }}">
                        Pilih
                    </label>
                </div>

                <div class="d-flex gap-1">
                    {{-- Toggle publish --}}
                    <form method="POST"
                          action="{{ route('guru.materials.toggle-publish', $material->id) }}"
                          class="d-inline">
                        @csrf
                        <button type="submit"
                                class="btn btn-sm {{ $isPublished ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                style="border-radius:7px;"
                                title="{{ $isPublished ? 'Sembunyikan' : 'Terbitkan' }}">
                            <i class="fas {{ $isPublished ? 'fa-eye-slash' : 'fa-eye' }}" style="font-size:.72rem;"></i>
                        </button>
                    </form>
                    {{-- Hapus --}}
                    <form method="POST"
                          action="{{ route('guru.materials.destroy', $material->id) }}"
                          class="d-inline"
                          onsubmit="return confirm('Hapus materi \'{{ addslashes($material->title) }}\'?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                style="border-radius:7px;" title="Hapus">
                            <i class="fas fa-trash" style="font-size:.72rem;"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center py-5">
            <div class="empty-state-icon">
                <i class="fas fa-book-open fa-2x text-info opacity-75"></i>
            </div>
            <h5 class="text-muted mb-2">Belum Ada Materi</h5>
            <p class="text-muted small mb-4">Mulai dengan menambahkan materi pembelajaran pertama untuk siswa.</p>
            <a href="{{ route('guru.materials.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah Materi
            </a>
        </div>
    </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($materials->hasPages())
<div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2 mt-4">
    <small class="text-muted">
        Menampilkan {{ $materials->firstItem() }}–{{ $materials->lastItem() }}
        dari {{ number_format($materials->total()) }} materi
    </small>
    {{ $materials->links() }}
</div>
@endif

@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const searchInput  = document.getElementById('searchInput');
    const subjectFil   = document.getElementById('subjectFilter');
    const statusFil    = document.getElementById('statusFilter');
    const resetBtn     = document.getElementById('resetFilter');
    const selectAll    = document.getElementById('selectAll');
    const bulkDeleteBtn= document.getElementById('bulkDeleteBtn');
    const bulkCount    = document.getElementById('bulkCount');
    const resultCount  = document.getElementById('resultCount');
    const cols         = document.querySelectorAll('.mat-col');

    // ── Filter ─────────────────────────────────────────
    function filterCards() {
        const q  = (searchInput?.value ?? '').toLowerCase().trim();
        const sj = subjectFil?.value  ?? '';
        const st = statusFil?.value   ?? '';
        let visible = 0;

        cols.forEach(function (col) {
            const title   = col.dataset.title   ?? '';
            const subject = col.dataset.subject ?? '';
            const status  = col.dataset.status  ?? '';
            const text    = col.textContent.toLowerCase();

            const ok = (!q  || text.includes(q))
                    && (!sj || subject === sj)
                    && (!st || status  === st);

            col.style.display = ok ? '' : 'none';
            if (ok) visible++;
        });

        if (resultCount) {
            resultCount.textContent = visible < cols.length
                ? `Menampilkan ${visible} dari ${cols.length} materi`
                : '';
        }
    }

    if (searchInput) searchInput.addEventListener('input',  filterCards);
    if (subjectFil)  subjectFil.addEventListener('change',  filterCards);
    if (statusFil)   statusFil.addEventListener('change',   filterCards);

    if (resetBtn) resetBtn.addEventListener('click', function () {
        if (searchInput) searchInput.value = '';
        if (subjectFil)  subjectFil.value  = '';
        if (statusFil)   statusFil.value   = '';
        filterCards();
    });

    // ── Checkboxes ─────────────────────────────────────
    function updateBulk() {
        const checked = document.querySelectorAll('.mat-check:checked').length;
        if (bulkCount)    bulkCount.textContent = checked;
        if (bulkDeleteBtn) bulkDeleteBtn.classList.toggle('d-none', checked === 0);
        if (selectAll) {
            const all = document.querySelectorAll('.mat-check').length;
            selectAll.indeterminate = checked > 0 && checked < all;
            selectAll.checked       = checked === all && all > 0;
        }
    }

    document.querySelectorAll('.mat-check').forEach(cb => {
        cb.addEventListener('change', updateBulk);
    });

    if (selectAll) selectAll.addEventListener('change', function () {
        document.querySelectorAll('.mat-check').forEach(cb => cb.checked = this.checked);
        updateBulk();
    });

    // ── Bulk Delete ─────────────────────────────────────
    window.doBulkDelete = function () {
        const ids = Array.from(document.querySelectorAll('.mat-check:checked')).map(cb => cb.value);
        if (!ids.length) return;
        if (!confirm(`Hapus ${ids.length} materi yang dipilih? Tindakan ini tidak dapat dibatalkan.`)) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("guru.materials.bulk-delete") }}';

        const csrf = document.createElement('input');
        csrf.type = 'hidden'; csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        form.appendChild(csrf);

        ids.forEach(id => {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
            form.appendChild(inp);
        });
        document.body.appendChild(form);
        form.submit();
    };

    // Init count
    updateBulk();
    filterCards();
});
</script>
@endpush
