@extends('layouts.guru')

@section('title', 'Penilaian Praktikum')
@section('page-title', 'Penilaian Praktikum')
@section('page-subtitle', 'Nilai siswa berdasarkan SOP checklist kriteria yang ditetapkan admin.')

@section('page-actions')
    <a href="{{ route('guru.praktikum.create') }}" class="btn btn-outline-primary btn-sm me-2">
        <i class="fas fa-plus me-1"></i>Buat Praktikum
    </a>
    <a href="{{ route('guru.penilaian.nilai-kriteria') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-star me-1"></i>Nilai Sekarang
    </a>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Penilaian Praktikum</li>
@endsection

@push('css')
<style>
/* ── Stats ──────────────────────────────────────── */
.stat-pk {
    border: none; border-radius: 14px;
    overflow: hidden; transition: transform .18s, box-shadow .18s;
}
.stat-pk:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(0,0,0,.1) !important; }
.stat-pk-icon {
    width: 46px; height: 46px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; color: #fff; flex-shrink: 0;
}

/* ── Praktikum accordion ────────────────────────── */
.pk-card {
    border: 1px solid #e8edf2 !important;
    border-radius: 14px !important;
    overflow: hidden;
    margin-bottom: 1rem;
    transition: box-shadow .18s;
}
.pk-card:hover { box-shadow: 0 6px 20px rgba(8,145,178,.1) !important; }
.pk-header {
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-bottom: 1px solid #e8edf2;
    cursor: pointer;
    user-select: none;
}
.pk-header:hover { background: #eff6ff; }

/* Progress bar siswa */
.progress-thin { height: 5px; border-radius: 3px; }

/* ── Siswa table ────────────────────────────────── */
.siswa-table th {
    font-size: .7rem; font-weight: 700; letter-spacing: .06em;
    text-transform: uppercase; color: #94a3b8;
    background: #f8fafc; border-bottom: 1px solid #e8edf2 !important;
}
.siswa-table td { font-size: .84rem; vertical-align: middle; }
.siswa-table tr:hover td { background: #f8fafc; }

.avatar-sm-pk {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .78rem; color: #fff; flex-shrink: 0;
}

.badge-dinilai { background:#dcfce7;color:#16a34a;border-radius:20px;font-size:.68rem;font-weight:600;padding:.18rem .6rem; }
.badge-belum   { background:#fef9c3;color:#a16207;border-radius:20px;font-size:.68rem;font-weight:600;padding:.18rem .6rem; }

.btn-nilai {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .28rem .7rem; border-radius: 7px;
    font-size: .75rem; font-weight: 600;
    text-decoration: none !important; white-space: nowrap;
    transition: background .12s, transform .1s;
}
.btn-nilai:hover { transform: translateY(-1px); }
.btn-nilai-new  { background:#fef9c3;color:#a16207;border:1px solid #fde047; }
.btn-nilai-edit { background:#dcfce7;color:#16a34a;border:1px solid #86efac; }
.btn-nilai-new:hover  { background:#fde047;color:#78350f; }
.btn-nilai-edit:hover { background:#bbf7d0;color:#14532d; }

/* Empty state */
.empty-pk { background:#f8fafc;border-radius:14px;border:2px dashed #e2e8f0; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:12px;">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ══ STATS ═══════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    @foreach([
        ['from'=>'#0891b2','to'=>'#0e7490','icon'=>'fa-flask',        'val'=>$stats['total_praktikum'],  'label'=>'Total Praktikum',  'sub'=>'Dibuat oleh kamu'],
        ['from'=>'#16a34a','to'=>'#15803d','icon'=>'fa-check-circle', 'val'=>$stats['total_dinilai'],    'label'=>'Sudah Dinilai',    'sub'=>'Siswa telah dinilai'],
        ['from'=>'#d97706','to'=>'#b45309','icon'=>'fa-clock',        'val'=>$stats['total_belum'],      'label'=>'Belum Dinilai',    'sub'=>'Menunggu penilaian'],
        ['from'=>'#7c3aed','to'=>'#6d28d9','icon'=>'fa-star',         'val'=>$stats['rata_rata_global'], 'label'=>'Rata-rata Nilai',  'sub'=>'Semua praktikum'],
    ] as $s)
    <div class="col-6 col-md-3">
        <div class="card stat-pk shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="stat-pk-icon"
                     style="background:linear-gradient(135deg,{{ $s['from'] }},{{ $s['to'] }});">
                    <i class="fas {{ $s['icon'] }}"></i>
                </div>
                <div>
                    <div class="fw-black text-dark"
                         style="font-size:1.65rem;line-height:1;letter-spacing:-.5px;">
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

{{-- ══ FILTER ══════════════════════════════════════════════════════ --}}
<form method="GET" action="{{ route('guru.penilaian-praktik.index') }}"
      class="d-flex gap-2 mb-4 align-items-center flex-wrap">
    <div class="input-group" style="max-width:320px;">
        <span class="input-group-text bg-white border-end-0">
            <i class="fas fa-search text-muted" style="font-size:.8rem;"></i>
        </span>
        <input type="text" name="search" class="form-control border-start-0"
               placeholder="Cari judul praktikum..." value="{{ request('search') }}"
               style="font-size:.85rem;">
    </div>
    <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
    @if(request('search'))
        <a href="{{ route('guru.penilaian-praktik.index') }}"
           class="btn btn-outline-secondary btn-sm px-3">Reset</a>
    @endif
</form>

{{-- ══ PRAKTIKUM LIST ══════════════════════════════════════════════ --}}
@forelse($practicals as $p)
@php
    $pct = $p->total_siswa > 0
        ? round(($p->sudah_dinilai / $p->total_siswa) * 100)
        : 0;
    $barColor = $pct >= 80 ? '#16a34a' : ($pct >= 40 ? '#d97706' : '#ef4444');
    $isPublished = $p->is_published;
@endphp

<div class="pk-card shadow-sm">
    {{-- Header accordion --}}
    <div class="pk-header d-flex align-items-center gap-3"
         data-bs-toggle="collapse"
         data-bs-target="#pk-{{ $p->id }}"
         aria-expanded="false">

        {{-- Ikon --}}
        <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:40px;height:40px;background:linear-gradient(135deg,#0891b2,#0e7490);">
            <i class="fas fa-flask text-white" style="font-size:.9rem;"></i>
        </div>

        {{-- Info --}}
        <div class="flex-grow-1" style="min-width:0;">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-semibold text-dark" style="font-size:.9rem;">
                    {{ $p->title }}
                </span>
                @if($isPublished)
                    <span style="background:#dcfce7;color:#16a34a;border-radius:20px;font-size:.66rem;font-weight:600;padding:.15rem .55rem;">
                        <i class="fas fa-eye me-1"></i>Terbit
                    </span>
                @else
                    <span style="background:#f1f5f9;color:#64748b;border-radius:20px;font-size:.66rem;font-weight:600;padding:.15rem .55rem;">
                        <i class="fas fa-eye-slash me-1"></i>Draft
                    </span>
                @endif
            </div>
            <div class="text-muted d-flex gap-3 flex-wrap" style="font-size:.76rem;margin-top:2px;">
                <span><i class="fas fa-book me-1"></i>{{ $p->subject?->name ?? '—' }}</span>
                <span><i class="fas fa-users me-1"></i>{{ $p->kelas?->name ?? 'Semua Kelas' }}</span>
                @if($p->due_date)
                    <span><i class="fas fa-calendar me-1"></i>{{ $p->due_date->format('d M Y') }}</span>
                @endif
            </div>
        </div>

        {{-- Progress --}}
        <div class="d-none d-md-block flex-shrink-0 text-end" style="min-width:150px;">
            <div class="d-flex justify-content-between mb-1" style="font-size:.72rem;">
                <span class="text-muted">Dinilai</span>
                <span class="fw-semibold">{{ $p->sudah_dinilai }}/{{ $p->total_siswa }}</span>
            </div>
            <div class="progress progress-thin">
                <div class="progress-bar"
                     style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
            </div>
            <div style="font-size:.68rem;color:{{ $barColor }};font-weight:600;margin-top:2px;">
                {{ $pct }}% selesai
            </div>
        </div>

        {{-- Rata-rata --}}
        <div class="d-none d-lg-block flex-shrink-0 text-center" style="min-width:70px;">
            @if($p->rata_rata !== null)
                @php
                    $rataColor = $p->rata_rata >= 80 ? '#16a34a' : ($p->rata_rata >= 60 ? '#d97706' : '#dc2626');
                @endphp
                <div class="fw-bold" style="font-size:1.3rem;color:{{ $rataColor }};line-height:1;">
                    {{ $p->rata_rata }}
                </div>
                <div class="text-muted" style="font-size:.65rem;">rata-rata</div>
            @else
                <div class="text-muted" style="font-size:.8rem;">—</div>
                <div class="text-muted" style="font-size:.65rem;">rata-rata</div>
            @endif
        </div>

        {{-- Chevron --}}
        <i class="fas fa-chevron-down text-muted flex-shrink-0"
           style="font-size:.65rem;transition:transform .2s;"
           id="chevron-{{ $p->id }}"></i>
    </div>

    {{-- Siswa list (collapsible) --}}
    <div class="collapse" id="pk-{{ $p->id }}">
        @if($p->siswa_list->isEmpty())
            <div class="text-center py-4 text-muted" style="font-size:.85rem;">
                <i class="fas fa-users me-2 opacity-50"></i>
                Belum ada siswa di kelas ini.
            </div>
        @else
        <div class="table-responsive">
            <table class="table siswa-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">Siswa</th>
                        <th class="py-3">Kelas</th>
                        <th class="text-center py-3">Status</th>
                        <th class="text-center py-3">Nilai</th>
                        <th class="text-center pe-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($p->siswa_list as $siswa)
                    @php
                        $siswaName  = $siswa->user?->name ?? '—';
                        $initial    = strtoupper(substr($siswaName, 0, 1));
                        $colors     = ['#0891b2','#7c3aed','#16a34a','#d97706','#dc2626','#0f766e'];
                        $avatarBg   = $colors[abs(crc32($siswaName)) % count($colors)];

                        // Cari nilai summary (criteria_id IS NULL)
                        $nilaiRecord = \App\Models\NilaiPraktik::where('practical_id', $p->id)
                            ->where('siswa_id', $siswa->user_id)
                            ->whereNull('criteria_id')
                            ->first();

                        $sudahDinilai = $nilaiRecord && !is_null($nilaiRecord->score);
                        $score        = $sudahDinilai ? (float) $nilaiRecord->score : null;
                        $grade        = match(true) {
                            $score === null => '—',
                            $score >= 90    => 'A',
                            $score >= 80    => 'B',
                            $score >= 70    => 'C',
                            $score >= 60    => 'D',
                            default         => 'E',
                        };
                        $scoreColor = match(true) {
                            $score === null  => '#94a3b8',
                            $score >= 80     => '#16a34a',
                            $score >= 60     => '#d97706',
                            default          => '#dc2626',
                        };
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm-pk" style="background:{{ $avatarBg }};">
                                    {{ $initial }}
                                </div>
                                <div style="min-width:0;">
                                    <div class="fw-semibold text-dark text-truncate"
                                         style="max-width:160px;">
                                        {{ $siswaName }}
                                    </div>
                                    @if($siswa->nis)
                                        <div class="text-muted" style="font-size:.7rem;">
                                            NIS: {{ $siswa->nis }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-muted" style="font-size:.8rem;">
                            {{ $siswa->kelas?->name ?? '—' }}
                        </td>
                        <td class="text-center">
                            @if($sudahDinilai)
                                <span class="badge-dinilai">
                                    <i class="fas fa-check me-1"></i>Dinilai
                                </span>
                            @else
                                <span class="badge-belum">
                                    <i class="fas fa-clock me-1"></i>Belum
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($sudahDinilai)
                                <span class="fw-bold" style="font-size:.95rem;color:{{ $scoreColor }};">
                                    {{ number_format($score, 0) }}
                                </span>
                                <span class="text-muted" style="font-size:.72rem;">
                                    ({{ $grade }})
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            <a href="{{ route('guru.penilaian.nilai-kriteria', [
                                    'practical_id' => $p->id,
                                    'siswa_id'     => $siswa->id,
                                ]) }}"
                               class="btn-nilai {{ $sudahDinilai ? 'btn-nilai-edit' : 'btn-nilai-new' }}">
                                <i class="fas {{ $sudahDinilai ? 'fa-edit' : 'fa-star' }}"
                                   style="font-size:.65rem;"></i>
                                {{ $sudahDinilai ? 'Edit Nilai' : 'Nilai' }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- Footer ringkasan --}}
        <div class="d-flex align-items-center justify-content-between px-4 py-2 border-top bg-white"
             style="font-size:.78rem;">
            <span class="text-muted">
                {{ $p->sudah_dinilai }} dari {{ $p->total_siswa }} siswa sudah dinilai
            </span>
            <a href="{{ route('guru.penilaian.nilai-kriteria', ['practical_id' => $p->id]) }}"
               class="btn btn-primary btn-sm" style="border-radius:8px;font-size:.75rem;">
                <i class="fas fa-star me-1"></i>Nilai Semua Siswa
            </a>
        </div>
        @endif
    </div>
</div>

@empty
<div class="empty-pk text-center py-5 px-3">
    <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center
                 justify-content-center mb-3"
         style="width:72px;height:72px;">
        <i class="fas fa-flask text-info fa-2x opacity-75"></i>
    </div>
    <h5 class="text-muted mb-2">Belum Ada Praktikum</h5>
    <p class="text-muted small mb-4">
        @if(request('search'))
            Tidak ada praktikum dengan kata kunci "<strong>{{ request('search') }}</strong>".
        @else
            Buat praktikum terlebih dahulu agar bisa melakukan penilaian.
        @endif
    </p>
    <div class="d-flex justify-content-center gap-2">
        @if(request('search'))
            <a href="{{ route('guru.penilaian-praktik.index') }}"
               class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-times me-1"></i>Reset
            </a>
        @endif
        <a href="{{ route('guru.praktikum.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Buat Praktikum
        </a>
    </div>
</div>
@endforelse

@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Rotate chevron on collapse toggle
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function (btn) {
        const targetId = btn.getAttribute('data-bs-target').replace('#', '');
        const pkId     = targetId.replace('pk-', '');
        const chevron  = document.getElementById('chevron-' + pkId);

        btn.addEventListener('click', function () {
            const isExpanded = btn.getAttribute('aria-expanded') === 'true';
            if (chevron) {
                chevron.style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(180deg)';
            }
        });
    });

    // Auto-expand if ada yang belum dinilai dan hanya 1 praktikum
    const cards = document.querySelectorAll('.collapse');
    if (cards.length === 1) {
        const bs = new bootstrap.Collapse(cards[0], { show: true });
        const parentId = cards[0].id.replace('pk-', '');
        const chevron  = document.getElementById('chevron-' + parentId);
        if (chevron) chevron.style.transform = 'rotate(180deg)';
    }
});
</script>
@endpush
