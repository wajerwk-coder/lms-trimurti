@extends('layouts.siswa')

@section('title', 'Mata Pelajaran')
@section('page-title', 'Mata Pelajaran')
@section('page-subtitle', 'Daftar mata pelajaran untuk kelas Anda.')

@push('css')
<style>
.subject-card {
    border: 1px solid #e8edf2 !important;
    border-radius: 14px !important;
    transition: transform .18s, box-shadow .18s, border-color .18s;
    overflow: hidden;
}
.subject-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 28px rgba(0,0,0,.10) !important;
    border-color: #c7d2fe !important;
}
.subject-top-bar {
    height: 4px;
    border-radius: 14px 14px 0 0;
}
.subject-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}
.stat-mini {
    text-align: center;
    padding: .4rem 0;
    border-right: 1px solid #f1f5f9;
}
.stat-mini:last-child { border-right: none; }
.stat-mini .val { font-weight: 700; font-size: .95rem; line-height: 1; }
.stat-mini .lbl { font-size: .65rem; color: #94a3b8; margin-top: 2px; }

.search-bar .input-group {
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
    transition: border-color .15s, box-shadow .15s;
}
.search-bar .input-group:focus-within {
    border-color: #7c3aed;
    box-shadow: 0 0 0 3px rgba(124,58,237,.1);
}
.search-bar .input-group-text,
.search-bar .form-control {
    border: none; background: transparent; box-shadow: none;
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

{{-- ── Banner kelas ──────────────────────────────── --}}
@if($kelas)
<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="p-4"
             style="background:linear-gradient(135deg,#1e3a8a 0%,#4f46e5 60%,#7c3aed 100%);">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:54px;height:54px;background:rgba(255,255,255,.15);">
                        <i class="fas fa-graduation-cap text-white fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold fs-5 mb-0">{{ $siswaData['kelas'] }}</div>
                        <div class="text-white opacity-75 small">{{ $siswaData['jurusan'] }}</div>
                    </div>
                </div>
                <div class="d-flex gap-4">
                    <div class="text-center">
                        <div class="text-white fw-bold fs-4 lh-1">{{ $subjects->count() }}</div>
                        <div class="text-white opacity-75" style="font-size:.72rem;">Mata Pelajaran</div>
                    </div>
                    <div class="text-center">
                        <div class="text-white fw-bold fs-4 lh-1">{{ $subjects->sum('total_activities') }}</div>
                        <div class="text-white opacity-75" style="font-size:.72rem;">Total Aktivitas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── Search ──────────────────────────────────────── --}}
@if($subjects->isNotEmpty())
<div class="search-bar mb-4">
    <div class="input-group">
        <span class="input-group-text ps-3">
            <i class="fas fa-search text-muted" style="font-size:.85rem;"></i>
        </span>
        <input type="text" id="subjectSearch" class="form-control"
               placeholder="Cari mata pelajaran…" autocomplete="off">
    </div>
</div>
@endif

{{-- ── Empty state ──────────────────────────────────── --}}
@if($subjects->isEmpty())
<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-body text-center py-5">
        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
             style="width:72px;height:72px;background:rgba(124,58,237,.08);">
            <i class="fas fa-book-open fa-2x" style="color:#7c3aed;opacity:.6;"></i>
        </div>
        <h5 class="fw-semibold mb-2">Belum ada mata pelajaran</h5>
        <p class="text-muted mb-0">
            @if(!$kelas)
                Anda belum terdaftar di kelas manapun. Hubungi admin.
            @else
                Belum ada mata pelajaran yang tersedia untuk kelas Anda.
            @endif
        </p>
    </div>
</div>

@else

{{-- ── Grid mata pelajaran ──────────────────────────── --}}
<div class="row g-4" id="subjectGrid">
    @foreach($subjects as $subject)
    @php
        $typeMap = [
            'teori'     => ['color'=>'#3b82f6', 'bg'=>'rgba(59,130,246,.08)',  'icon'=>'fa-chalkboard-teacher', 'label'=>'Teori'],
            'praktikum' => ['color'=>'#d97706', 'bg'=>'rgba(217,119,6,.08)',   'icon'=>'fa-flask',              'label'=>'Praktikum'],
            'campuran'  => ['color'=>'#16a34a', 'bg'=>'rgba(22,163,74,.08)',   'icon'=>'fa-layer-group',        'label'=>'Campuran'],
        ];
        $tm = $typeMap[$subject->type ?? ''] ?? ['color'=>'#7c3aed','bg'=>'rgba(124,58,237,.08)','icon'=>'fa-book','label'=>'Umum'];
    @endphp
    <div class="col-sm-6 col-xl-4 subject-col">
        <div class="card subject-card shadow-sm h-100" data-name="{{ strtolower($subject->name) }}">

            {{-- Top color bar --}}
            <div style="height:4px;background:{{ $tm['color'] }};"></div>

            {{-- Card body --}}
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="subject-icon"
                         style="background:{{ $tm['bg'] }};">
                        <i class="fas {{ $tm['icon'] }}" style="color:{{ $tm['color'] }};"></i>
                    </div>
                    <span class="badge fw-semibold"
                          style="background:{{ $tm['bg'] }};color:{{ $tm['color'] }};border-radius:20px;font-size:.7rem;padding:.25rem .7rem;">
                        {{ $tm['label'] }}
                    </span>
                </div>

                <h6 class="fw-bold mb-1 lh-sm" style="font-size:.95rem;">{{ $subject->name }}</h6>

                @if($subject->code)
                    <span class="badge bg-light text-muted border"
                          style="font-size:.68rem;border-radius:20px;">{{ $subject->code }}</span>
                @endif

                @if($subject->description)
                    <p class="text-muted mt-2 mb-0"
                       style="font-size:.78rem;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                        {{ $subject->description }}
                    </p>
                @endif
            </div>

            {{-- Stats bar --}}
            <div class="border-top mx-3 mb-3"></div>
            <div class="d-flex px-3 pb-3">
                @foreach([
                    [$subject->material_count,   '#3b82f6', 'Materi'],
                    [$subject->assignment_count, '#16a34a', 'Tugas'],
                    [$subject->practical_count,  '#d97706', 'Praktikum'],
                ] as [$cnt, $clr, $lbl])
                <div class="stat-mini flex-fill">
                    <div class="val" style="color:{{ $clr }};">{{ $cnt }}</div>
                    <div class="lbl">{{ $lbl }}</div>
                </div>
                @endforeach
            </div>

            @if($subject->total_activities === 0)
            <div class="px-4 pb-3">
                <div class="text-center py-1 rounded-2 border"
                     style="font-size:.72rem;color:#94a3b8;background:#f8fafc;">
                    <i class="fas fa-hourglass-half me-1"></i>Konten belum tersedia
                </div>
            </div>
            @endif

            {{-- Action --}}
            <div class="px-4 pb-4">
                <a href="{{ route('siswa.pelajaran.show', $subject->id) }}"
                   class="btn w-100 fw-semibold"
                   style="background:{{ $tm['color'] }};color:#fff;border-radius:10px;border:none;">
                    <i class="fas fa-book-open me-2"></i>Buka Pelajaran
                </a>
            </div>

        </div>
    </div>
    @endforeach
</div>

{{-- Empty search result --}}
<div id="emptySearch" class="d-none text-center py-5">
    <i class="fas fa-search fa-2x text-muted opacity-25 mb-3 d-block"></i>
    <p class="text-muted">Tidak ada mata pelajaran yang cocok.</p>
</div>

@endif

@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('subjectSearch');
    const grid  = document.getElementById('subjectGrid');
    const empty = document.getElementById('emptySearch');
    if (!input || !grid) return;

    input.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        let visible = 0;
        grid.querySelectorAll('.subject-col').forEach(function (col) {
            const card  = col.querySelector('.subject-card');
            const match = !q || (card?.dataset.name ?? '').includes(q);
            col.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        if (empty) empty.classList.toggle('d-none', visible > 0);
        if (grid)  grid.style.display = visible > 0 ? '' : 'none';
    });
});
</script>
@endpush
