@extends('layouts.siswa')

@section('title', 'Riwayat Nilai Praktikum')
@section('page-title', 'Riwayat Nilai Praktikum')
@section('page-subtitle', 'Semua nilai praktikum yang sudah kamu terima.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('siswa.praktikum.index') }}">Praktikum</a></li>
    <li class="breadcrumb-item active" aria-current="page">Riwayat Nilai</li>
@endsection

@push('css')
<style>
.hist-tbl th { font-size:.72rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e8edf2!important; }
.hist-tbl td { font-size:.84rem;vertical-align:middle; }
.hist-tbl tr:hover td { background:#f8fafc; }
</style>
@endpush

@section('content')

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([
        ['from'=>'#7c3aed','to'=>'#6d28d9','icon'=>'fa-flask',    'val'=>$stats['total_graded'],  'label'=>'Total Dinilai'],
        ['from'=>'#16a34a','to'=>'#15803d','icon'=>'fa-star',     'val'=>$stats['average_score'], 'label'=>'Rata-rata Nilai'],
        ['from'=>'#0891b2','to'=>'#0e7490','icon'=>'fa-arrow-up', 'val'=>$stats['highest_score'], 'label'=>'Nilai Tertinggi'],
        ['from'=>'#d97706','to'=>'#b45309','icon'=>'fa-arrow-down','val'=>$stats['lowest_score'], 'label'=>'Nilai Terendah'],
    ] as $s)
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;overflow:hidden;">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div style="width:44px;height:44px;border-radius:11px;background:linear-gradient(135deg,{{ $s['from'] }},{{ $s['to'] }});display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;flex-shrink:0;">
                    <i class="fas {{ $s['icon'] }}"></i>
                </div>
                <div>
                    <div class="fw-black text-dark" style="font-size:1.5rem;line-height:1;">
                        {{ is_float($s['val']) ? number_format($s['val'], 1) : $s['val'] }}
                    </div>
                    <div class="text-muted" style="font-size:.73rem;">{{ $s['label'] }}</div>
                </div>
            </div>
            <div style="height:3px;background:linear-gradient(90deg,{{ $s['from'] }},{{ $s['to'] }});"></div>
        </div>
    </div>
    @endforeach
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-body p-0">
        @if($scores->isEmpty())
        <div class="text-center py-5 text-muted">
            <div class="rounded-circle bg-purple bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:64px;height:64px;background:rgba(124,58,237,.1);">
                <i class="fas fa-flask" style="color:#7c3aed;font-size:1.5rem;opacity:.7;"></i>
            </div>
            <h6 class="text-muted mb-1">Belum Ada Nilai Praktikum</h6>
            <p class="small mb-3">Nilai akan muncul setelah guru melakukan penilaian.</p>
            <a href="{{ route('siswa.praktikum.index') }}" class="btn btn-outline-primary btn-sm">
                Lihat Daftar Praktikum
            </a>
        </div>
        @else
        <div class="table-responsive">
            <table class="table hist-tbl align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">Praktikum</th>
                        <th class="py-3">Mata Pelajaran</th>
                        <th class="py-3">Tanggal Dinilai</th>
                        <th class="text-center py-3">Nilai</th>
                        <th class="text-center py-3">Grade</th>
                        <th class="text-center pe-4 py-3">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($scores as $sc)
                    @php
                        $val   = (float) $sc->score;
                        $grade = $val >= 90 ? 'A' : ($val >= 80 ? 'B' : ($val >= 70 ? 'C' : ($val >= 60 ? 'D' : 'E')));
                        $gc    = ['A'=>'success','B'=>'primary','C'=>'warning','D'=>'danger','E'=>'secondary'][$grade];
                        $sc2   = $val >= 80 ? '#16a34a' : ($val >= 60 ? '#d97706' : '#dc2626');
                    @endphp
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $sc->practical?->title ?? '—' }}</td>
                        <td class="text-muted">{{ $sc->practical?->subject?->name ?? '—' }}</td>
                        <td class="text-muted" style="font-size:.8rem;">
                            {{ $sc->graded_at?->format('d M Y') ?? '—' }}
                        </td>
                        <td class="text-center fw-bold" style="color:{{ $sc2 }};font-size:.95rem;">
                            {{ number_format($val, 0) }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $gc }}">{{ $grade }}</span>
                        </td>
                        <td class="text-center pe-4">
                            @if($sc->practical_id)
                            <a href="{{ route('siswa.praktikum.show', $sc->practical_id) }}"
                               class="btn btn-sm btn-outline-primary"
                               style="border-radius:7px;width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                                <i class="fas fa-eye" style="font-size:.65rem;"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($scores->hasPages())
        <div class="px-4 py-3 border-top">{{ $scores->links() }}</div>
        @endif
        @endif
    </div>
</div>
@endsection
