@extends('layouts.guru')

@section('title', 'Penilaian')
@section('page-title', 'Penilaian')
@section('page-subtitle', 'Kelola penilaian tugas dan praktikum siswa dalam satu halaman.')

@section('page-actions')
    <a href="{{ route('guru.penilaian.nilai-kriteria') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-clipboard-check me-1"></i>Nilai Praktikum (SOP)
    </a>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Penilaian</li>
@endsection

@push('css')
<style>
/* ── Stats ──────────────────────────────────────── */
.stat-pn {
    border: none; border-radius: 14px; overflow: hidden;
    transition: transform .18s, box-shadow .18s;
}
.stat-pn:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(0,0,0,.1) !important; }
.stat-pn-icon {
    width: 44px; height: 44px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; color: #fff; flex-shrink: 0;
}

/* ── Tabs ───────────────────────────────────────── */
.pn-tabs {
    border-bottom: 2px solid #e8edf2;
    margin-bottom: 1.5rem;
    gap: .25rem;
}
.pn-tab-btn {
    padding: .6rem 1.25rem;
    border: none; background: transparent;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    font-size: .875rem; font-weight: 600; color: #64748b;
    cursor: pointer; transition: color .15s, border-color .15s;
    border-radius: 0;
    white-space: nowrap;
}
.pn-tab-btn:hover { color: #0f766e; }
.pn-tab-btn.active {
    color: #0f766e;
    border-bottom-color: #0f766e;
}
.pn-tab-btn .badge {
    font-size: .65rem; padding: .18rem .5rem;
    border-radius: 20px; margin-left: .35rem;
}

/* ── Table ──────────────────────────────────────── */
.pn-table th {
    font-size: .7rem; font-weight: 700; letter-spacing: .06em;
    text-transform: uppercase; color: #94a3b8;
    background: #f8fafc; border-bottom: 1px solid #e8edf2 !important;
}
.pn-table td { font-size: .84rem; vertical-align: middle; }
.pn-table tr:hover td { background: #f8fafc; }

.av-sm {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .78rem; color: #fff; flex-shrink: 0;
}
.badge-dinilai { background:#dcfce7;color:#16a34a;border-radius:20px;font-size:.68rem;font-weight:600;padding:.18rem .6rem; }
.badge-belum   { background:#fef9c3;color:#a16207;border-radius:20px;font-size:.68rem;font-weight:600;padding:.18rem .6rem; }

/* ── Praktikum accordion ────────────────────────── */
.pk-card {
    border: 1px solid #e8edf2 !important; border-radius: 12px !important;
    overflow: hidden; margin-bottom: .75rem;
    transition: box-shadow .15s;
}
.pk-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.07) !important; }
.pk-header {
    padding: .875rem 1.25rem; background: #f8fafc;
    border-bottom: 1px solid #e8edf2;
    cursor: pointer; user-select: none;
    display: flex; align-items: center; gap: .875rem;
}
.pk-header:hover { background: #eff6ff; }
.progress-thin { height: 5px; border-radius: 3px; }

.btn-nilai-sm {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .22rem .65rem; border-radius: 7px;
    font-size: .73rem; font-weight: 600;
    text-decoration: none !important; transition: background .12s;
}
.btn-nilai-new  { background:#fef9c3;color:#a16207;border:1px solid #fde047; }
.btn-nilai-edit { background:#dcfce7;color:#16a34a;border:1px solid #86efac; }
.btn-nilai-new:hover  { background:#fde047;color:#78350f; }
.btn-nilai-edit:hover { background:#bbf7d0;color:#14532d; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm"
     style="border-radius:12px;">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm"
     style="border-radius:12px;">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ══ STATS ═══════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    @foreach([
        ['from'=>'#3b82f6','to'=>'#1d4ed8','icon'=>'fa-tasks',        'val'=>$stats['total_tugas'],         'label'=>'Total Tugas',         'sub'=>'Pengumpulan masuk'],
        ['from'=>'#d97706','to'=>'#b45309','icon'=>'fa-clock',        'val'=>$stats['tugas_belum_dinilai'], 'label'=>'Tugas Belum Dinilai', 'sub'=>'Menunggu penilaian'],
        ['from'=>'#0891b2','to'=>'#0e7490','icon'=>'fa-flask',        'val'=>$stats['total_praktikum'],     'label'=>'Total Praktikum',     'sub'=>'Dibuat oleh kamu'],
        ['from'=>'#d97706','to'=>'#b45309','icon'=>'fa-hourglass',    'val'=>$stats['praktik_belum'],       'label'=>'Praktik Belum Dinilai','sub'=>'Siswa menunggu nilai'],
    ] as $s)
    <div class="col-6 col-md-3">
        <div class="card stat-pn shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="stat-pn-icon"
                     style="background:linear-gradient(135deg,{{ $s['from'] }},{{ $s['to'] }});">
                    <i class="fas {{ $s['icon'] }}"></i>
                </div>
                <div>
                    <div class="fw-black text-dark"
                         style="font-size:1.55rem;line-height:1;letter-spacing:-.5px;">
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

{{-- ══ TABS ════════════════════════════════════════════════════════ --}}
<div class="d-flex pn-tabs">
    <button class="pn-tab-btn {{ $activeTab === 'tugas' ? 'active' : '' }}"
            id="tab-btn-tugas" onclick="switchTab('tugas')">
        <i class="fas fa-tasks me-1"></i>Penilaian Tugas
        @if($stats['tugas_belum_dinilai'] > 0)
            <span class="badge"
                  style="background:#fef9c3;color:#a16207;">
                {{ $stats['tugas_belum_dinilai'] }}
            </span>
        @endif
    </button>
    <button class="pn-tab-btn {{ $activeTab === 'praktik' ? 'active' : '' }}"
            id="tab-btn-praktik" onclick="switchTab('praktik')">
        <i class="fas fa-flask me-1"></i>Penilaian Praktikum
        @if($stats['praktik_belum'] > 0)
            <span class="badge"
                  style="background:#fef9c3;color:#a16207;">
                {{ $stats['praktik_belum'] }}
            </span>
        @endif
    </button>
</div>

{{-- ══ TAB: PENILAIAN TUGAS ════════════════════════════════════════ --}}
<div id="panel-tugas" class="{{ $activeTab === 'tugas' ? '' : 'd-none' }}">
    <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4"
             style="border-radius:14px 14px 0 0;border-bottom:1px solid #e8edf2;">
            <h6 class="mb-0 fw-bold">
                <i class="fas fa-tasks me-2 text-primary"></i>Pengumpulan Tugas
            </h6>
            <a href="{{ route('guru.submissions.index') }}"
               class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:.75rem;">
                <i class="fas fa-external-link-alt me-1"></i>Lihat Detail
            </a>
        </div>
        <div class="card-body p-0">
            @if($assignmentSubmissions->isEmpty())
            <div class="text-center py-5 text-muted">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center
                             justify-content-center mb-3" style="width:64px;height:64px;">
                    <i class="fas fa-tasks text-primary fa-lg opacity-75"></i>
                </div>
                <h6 class="text-muted mb-2">Belum Ada Pengumpulan</h6>
                <p class="small text-muted mb-3">Siswa belum mengumpulkan tugas apapun.</p>
                <a href="{{ route('guru.assignments.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Buat Tugas
                </a>
            </div>
            @else
            <div class="table-responsive">
                <table class="table pn-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4 py-3">Siswa</th>
                            <th class="py-3">Judul Tugas</th>
                            <th class="py-3">Mata Pelajaran</th>
                            <th class="py-3">Dikumpulkan</th>
                            <th class="text-center py-3">Status</th>
                            <th class="text-center py-3">Nilai</th>
                            <th class="text-center pe-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignmentSubmissions as $sub)
                        @php
                            $name     = $sub->siswa?->name ?? '—';
                            $initial  = strtoupper(substr($name, 0, 1));
                            $colors   = ['#3b82f6','#7c3aed','#16a34a','#d97706','#dc2626'];
                            $bgColor  = $colors[abs(crc32($name)) % count($colors)];
                            $isGraded = !is_null($sub->score);
                            $score    = (float) ($sub->score ?? 0);
                            $scoreColor = $score >= 80 ? '#16a34a' : ($score >= 60 ? '#d97706' : '#dc2626');
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="av-sm" style="background:{{ $bgColor }};">{{ $initial }}</div>
                                    <div style="min-width:0;">
                                        <div class="fw-semibold text-dark text-truncate"
                                             style="max-width:140px;">{{ $name }}</div>
                                        <div class="text-muted" style="font-size:.7rem;">
                                            {{ $sub->siswa?->email ?? '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-truncate" style="max-width:170px;"
                                     title="{{ $sub->assignment?->title ?? '—' }}">
                                    {{ $sub->assignment?->title ?? '—' }}
                                </div>
                                @if($sub->is_late)
                                    <span style="font-size:.68rem;color:#dc2626;font-weight:600;">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Terlambat
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted" style="font-size:.8rem;">
                                {{ $sub->assignment?->subject?->name ?? '—' }}
                            </td>
                            <td style="font-size:.8rem;">
                                <div>{{ $sub->submitted_at?->format('d M Y') ?? '—' }}</div>
                                <div class="text-muted">{{ $sub->submitted_at?->format('H:i') ?? '' }}</div>
                            </td>
                            <td class="text-center">
                                @if($isGraded)
                                    <span class="badge-dinilai">
                                        <i class="fas fa-check me-1"></i>Dinilai
                                    </span>
                                @else
                                    <span class="badge-belum">
                                        <i class="fas fa-clock me-1"></i>Menunggu
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($isGraded)
                                    <span class="fw-bold" style="font-size:.95rem;color:{{ $scoreColor }};">
                                        {{ number_format($score, 0) }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('guru.submissions.show', $sub->id) }}"
                                       class="btn btn-sm btn-outline-secondary"
                                       style="border-radius:7px;width:28px;height:28px;padding:0;
                                              display:inline-flex;align-items:center;justify-content:center;"
                                       title="Lihat Detail">
                                        <i class="fas fa-eye" style="font-size:.65rem;"></i>
                                    </a>
                                    <a href="{{ route('guru.penilaian.edit', $sub->id) }}"
                                       class="btn btn-sm {{ $isGraded ? 'btn-outline-success' : 'btn-outline-warning' }}"
                                       style="border-radius:7px;width:28px;height:28px;padding:0;
                                              display:inline-flex;align-items:center;justify-content:center;"
                                       title="{{ $isGraded ? 'Edit Nilai' : 'Beri Nilai' }}">
                                        <i class="fas {{ $isGraded ? 'fa-edit' : 'fa-star' }}"
                                           style="font-size:.65rem;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ══ TAB: PENILAIAN PRAKTIKUM ════════════════════════════════════ --}}
<div id="panel-praktik" class="{{ $activeTab === 'praktik' ? '' : 'd-none' }}">

    @if($practicals->isEmpty())
    <div class="text-center py-5 text-muted"
         style="background:#f8fafc;border-radius:14px;border:2px dashed #e2e8f0;">
        <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center
                     justify-content-center mb-3" style="width:64px;height:64px;">
            <i class="fas fa-flask text-info fa-lg opacity-75"></i>
        </div>
        <h6 class="text-muted mb-2">Belum Ada Praktikum</h6>
        <p class="small text-muted mb-3">Buat praktikum terlebih dahulu.</p>
        <a href="{{ route('guru.praktikum.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Buat Praktikum
        </a>
    </div>
    @else
    @foreach($practicals as $p)
    @php
        $pct      = $p->total_siswa > 0 ? round(($p->sudah_dinilai / $p->total_siswa) * 100) : 0;
        $barColor = $pct >= 80 ? '#16a34a' : ($pct >= 40 ? '#d97706' : '#ef4444');
    @endphp
    <div class="pk-card shadow-sm">
        {{-- Header --}}
        <div class="pk-header"
             data-bs-toggle="collapse"
             data-bs-target="#ppk-{{ $p->id }}"
             aria-expanded="false">
            <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:36px;height:36px;background:linear-gradient(135deg,#0891b2,#0e7490);">
                <i class="fas fa-flask text-white" style="font-size:.8rem;"></i>
            </div>
            <div class="flex-grow-1" style="min-width:0;">
                <div class="fw-semibold text-dark" style="font-size:.88rem;">{{ $p->title }}</div>
                <div class="text-muted" style="font-size:.74rem;">
                    <i class="fas fa-book me-1"></i>{{ $p->subject?->name ?? '—' }}
                    <span class="mx-1">·</span>
                    <i class="fas fa-users me-1"></i>{{ $p->kelas?->name ?? 'Semua Kelas' }}
                </div>
            </div>
            <div class="d-none d-md-block flex-shrink-0 text-end me-3" style="min-width:130px;">
                <div class="d-flex justify-content-between mb-1" style="font-size:.7rem;">
                    <span class="text-muted">Dinilai</span>
                    <span class="fw-semibold">{{ $p->sudah_dinilai }}/{{ $p->total_siswa }}</span>
                </div>
                <div class="progress progress-thin">
                    <div class="progress-bar"
                         style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
                </div>
                <div style="font-size:.66rem;color:{{ $barColor }};font-weight:600;margin-top:2px;">
                    {{ $pct }}%
                </div>
            </div>
            @if($p->rata_rata !== null)
            <div class="d-none d-lg-block flex-shrink-0 text-center me-3" style="min-width:55px;">
                @php
                    $rc = $p->rata_rata >= 80 ? '#16a34a' : ($p->rata_rata >= 60 ? '#d97706' : '#dc2626');
                @endphp
                <div class="fw-bold" style="font-size:1.15rem;color:{{ $rc }};line-height:1;">
                    {{ $p->rata_rata }}
                </div>
                <div class="text-muted" style="font-size:.62rem;">rata-rata</div>
            </div>
            @endif
            <i class="fas fa-chevron-down text-muted flex-shrink-0"
               style="font-size:.62rem;" id="chv-{{ $p->id }}"></i>
        </div>

        {{-- Siswa list --}}
        <div class="collapse" id="ppk-{{ $p->id }}">
            @if($p->siswa_list->isEmpty())
            <div class="text-center py-3 text-muted" style="font-size:.84rem;">
                <i class="fas fa-users me-2 opacity-50"></i>Belum ada siswa di kelas ini.
            </div>
            @else
            <div class="table-responsive">
                <table class="table pn-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4 py-2">Siswa</th>
                            <th class="py-2">Kelas</th>
                            <th class="text-center py-2">Status</th>
                            <th class="text-center py-2">Nilai</th>
                            <th class="text-center pe-4 py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($p->siswa_list as $siswa)
                        @php
                            $sn       = $siswa->user?->name ?? '—';
                            $si       = strtoupper(substr($sn, 0, 1));
                            $colors2  = ['#0891b2','#7c3aed','#16a34a','#d97706','#dc2626','#0f766e'];
                            $sbg      = $colors2[abs(crc32($sn)) % count($colors2)];

                            $nilaiRec = \App\Models\NilaiPraktik::where('practical_id', $p->id)
                                ->where('siswa_id', $siswa->user_id)
                                ->whereNull('criteria_id')
                                ->first();

                            $dinilai  = $nilaiRec && !is_null($nilaiRec->score);
                            $sc2      = $dinilai ? (float) $nilaiRec->score : null;
                            $grade    = match(true) {
                                $sc2 === null => '—',
                                $sc2 >= 90   => 'A', $sc2 >= 80 => 'B',
                                $sc2 >= 70   => 'C', $sc2 >= 60 => 'D',
                                default       => 'E',
                            };
                            $sc2color = match(true) {
                                $sc2 === null => '#94a3b8',
                                $sc2 >= 80   => '#16a34a',
                                $sc2 >= 60   => '#d97706',
                                default       => '#dc2626',
                            };
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="av-sm" style="background:{{ $sbg }};">{{ $si }}</div>
                                    <div style="min-width:0;">
                                        <div class="fw-semibold text-dark text-truncate"
                                             style="max-width:150px;">{{ $sn }}</div>
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
                                @if($dinilai)
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
                                @if($dinilai)
                                    <span class="fw-bold" style="font-size:.9rem;color:{{ $sc2color }};">
                                        {{ number_format($sc2, 0) }}
                                    </span>
                                    <span class="text-muted" style="font-size:.7rem;">({{ $grade }})</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <a href="{{ route('guru.penilaian.nilai-kriteria', [
                                        'practical_id' => $p->id,
                                        'siswa_id'     => $siswa->id,
                                    ]) }}"
                                   class="btn-nilai-sm {{ $dinilai ? 'btn-nilai-edit' : 'btn-nilai-new' }}">
                                    <i class="fas {{ $dinilai ? 'fa-edit' : 'fa-star' }}"
                                       style="font-size:.62rem;"></i>
                                    {{ $dinilai ? 'Edit' : 'Nilai' }}
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex align-items-center justify-content-between px-4 py-2 border-top bg-white"
                 style="font-size:.76rem;">
                <span class="text-muted">
                    {{ $p->sudah_dinilai }}/{{ $p->total_siswa }} siswa dinilai
                </span>
                <a href="{{ route('guru.penilaian.nilai-kriteria', ['practical_id' => $p->id]) }}"
                   class="btn btn-primary btn-sm" style="border-radius:8px;font-size:.72rem;">
                    <i class="fas fa-clipboard-check me-1"></i>Nilai via SOP
                </a>
            </div>
            @endif
        </div>
    </div>
    @endforeach
    @endif
</div>

@endsection

@push('js')
<script>
function switchTab(tab) {
    ['tugas','praktik'].forEach(function(t) {
        document.getElementById('panel-' + t).classList.toggle('d-none', t !== tab);
        document.getElementById('tab-btn-' + t).classList.toggle('active', t === tab);
    });
    // Update URL tanpa reload
    const url = new URL(window.location);
    url.searchParams.set('tab', tab);
    history.replaceState(null, '', url);
}

// Rotate chevron on collapse toggle
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function (btn) {
        const targetId = btn.getAttribute('data-bs-target').replace('#ppk-', '');
        const chevron  = document.getElementById('chv-' + targetId);
        btn.addEventListener('click', function () {
            const expanded = btn.getAttribute('aria-expanded') === 'true';
            if (chevron) chevron.style.transform = expanded ? 'rotate(0deg)' : 'rotate(180deg)';
        });
    });
});
</script>
@endpush
