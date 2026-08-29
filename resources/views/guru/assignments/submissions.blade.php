@extends('layouts.guru')

@section('title', 'Submissions — ' . $assignment->title)
@section('page-title', 'Submissions Tugas')
@section('page-subtitle', $assignment->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('guru.assignments.index') }}">Tugas</a></li>
    <li class="breadcrumb-item"><a href="{{ route('guru.assignments.show', $assignment->id) }}">{{ Str::limit($assignment->title, 30) }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Submissions</li>
@endsection

@section('page-actions')
    <a href="{{ route('guru.assignments.show', $assignment->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali ke Tugas
    </a>
@endsection

@push('css')
<style>
.sub-tbl th {
    font-size:.7rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;
    color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e8edf2!important;
}
.sub-tbl td { font-size:.84rem;vertical-align:middle; }
.sub-tbl tr:hover td { background:#f8fafc; }
.av-sm {
    width:32px;height:32px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-weight:700;font-size:.78rem;color:#fff;flex-shrink:0;
}
.badge-graded { background:#dcfce7;color:#16a34a;border-radius:20px;font-size:.68rem;font-weight:600;padding:.18rem .6rem; }
.badge-late   { background:#fee2e2;color:#dc2626;border-radius:20px;font-size:.68rem;font-weight:600;padding:.18rem .6rem; }
.badge-pending{ background:#fef9c3;color:#a16207;border-radius:20px;font-size:.68rem;font-weight:600;padding:.18rem .6rem; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:12px;">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([
        ['from'=>'#0891b2','to'=>'#0e7490','icon'=>'fa-inbox',        'val'=>$stats['total_submissions'],'label'=>'Total Submission'],
        ['from'=>'#16a34a','to'=>'#15803d','icon'=>'fa-check-circle', 'val'=>$stats['graded_count'],      'label'=>'Sudah Dinilai'],
        ['from'=>'#d97706','to'=>'#b45309','icon'=>'fa-clock',        'val'=>$stats['total_submissions'] - $stats['graded_count'],'label'=>'Belum Dinilai'],
        ['from'=>'#7c3aed','to'=>'#6d28d9','icon'=>'fa-star',         'val'=>$stats['average_score'],     'label'=>'Rata-rata Nilai'],
    ] as $s)
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;overflow:hidden;">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div style="width:44px;height:44px;border-radius:11px;background:linear-gradient(135deg,{{ $s['from'] }},{{ $s['to'] }});display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;flex-shrink:0;">
                    <i class="fas {{ $s['icon'] }}"></i>
                </div>
                <div>
                    <div class="fw-black text-dark" style="font-size:1.5rem;line-height:1;letter-spacing:-.5px;">{{ $s['val'] }}</div>
                    <div class="text-muted" style="font-size:.75rem;">{{ $s['label'] }}</div>
                </div>
            </div>
            <div style="height:3px;background:linear-gradient(90deg,{{ $s['from'] }},{{ $s['to'] }});"></div>
        </div>
    </div>
    @endforeach
</div>

{{-- Info tugas --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
    <div class="card-body py-3 px-4">
        <div class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="fw-bold text-dark">{{ $assignment->title }}</div>
                <div class="text-muted small">
                    <i class="fas fa-book me-1"></i>{{ $assignment->subject?->name ?? '—' }}
                    @if($assignment->kelas)
                        &nbsp;·&nbsp;<i class="fas fa-users me-1"></i>{{ $assignment->kelas->name }}
                    @endif
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Deadline</div>
                <div class="fw-semibold {{ $assignment->due_date?->isPast() ? 'text-danger' : 'text-dark' }}">
                    {{ $assignment->due_date?->format('d M Y, H:i') ?? '—' }}
                </div>
            </div>
            <div class="col-md-3 text-md-end">
                <span class="badge {{ $assignment->is_published ? 'bg-success' : 'bg-warning text-dark' }}">
                    {{ $assignment->is_published ? 'Dipublikasikan' : 'Draft' }}
                </span>
                <div class="text-muted small mt-1">Maks: {{ $assignment->max_score ?? 100 }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4"
         style="border-radius:14px 14px 0 0;border-bottom:1px solid #e8edf2;">
        <h6 class="mb-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i>Daftar Submission</h6>
        <small class="text-muted">{{ number_format($submissions->total()) }} submission</small>
    </div>
    <div class="card-body p-0">
        @if($submissions->isEmpty())
        <div class="text-center py-5 text-muted">
            <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;">
                <i class="fas fa-inbox text-info fa-lg opacity-75"></i>
            </div>
            <h6 class="text-muted mb-1">Belum Ada Submission</h6>
            <p class="small">Siswa belum mengumpulkan tugas ini.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table sub-tbl align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">Siswa</th>
                        <th class="py-3">Dikumpulkan</th>
                        <th class="py-3">File</th>
                        <th class="text-center py-3">Status</th>
                        <th class="text-center py-3">Nilai</th>
                        <th class="text-center pe-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submissions as $sub)
                    @php
                        $name    = $sub->siswa?->name ?? '—';
                        $initial = strtoupper(substr($name, 0, 1));
                        $colors  = ['#0891b2','#7c3aed','#16a34a','#d97706','#dc2626'];
                        $bgColor = $colors[abs(crc32($name)) % count($colors)];
                        $graded  = !is_null($sub->score);
                        $isLate  = $sub->is_late ?? false;
                        $score   = (float) ($sub->score ?? 0);
                        $sc      = $score >= 80 ? '#16a34a' : ($score >= 60 ? '#d97706' : '#dc2626');
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="av-sm" style="background:{{ $bgColor }};">{{ $initial }}</div>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $name }}</div>
                                    <div class="text-muted" style="font-size:.72rem;">{{ $sub->siswa?->email ?? '—' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size:.82rem;">{{ $sub->submitted_at?->format('d M Y') ?? '—' }}</div>
                            <div class="text-muted" style="font-size:.7rem;">{{ $sub->submitted_at?->format('H:i') ?? '' }}</div>
                        </td>
                        <td>
                            @if($sub->file_path)
                                <a href="{{ Storage::url($sub->file_path) }}" target="_blank" download
                                   class="text-primary" style="font-size:.78rem;">
                                    <i class="fas fa-paperclip me-1"></i>
                                    {{ Str::limit(basename($sub->file_path), 20) }}
                                </a>
                            @elseif($sub->submission_text ?? $sub->content)
                                <span class="text-muted" style="font-size:.78rem;">
                                    <i class="fas fa-align-left me-1"></i>Teks
                                </span>
                            @else
                                <span class="text-muted" style="font-size:.78rem;">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($graded)
                                <span class="badge-graded"><i class="fas fa-check me-1"></i>Dinilai</span>
                            @elseif($isLate)
                                <span class="badge-late"><i class="fas fa-exclamation me-1"></i>Terlambat</span>
                            @else
                                <span class="badge-pending"><i class="fas fa-clock me-1"></i>Menunggu</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($graded)
                                <span class="fw-bold" style="color:{{ $sc }};font-size:.95rem;">
                                    {{ number_format($score, 0) }}
                                </span>
                                @if($sub->feedback)
                                    <i class="fas fa-comment text-muted ms-1" style="font-size:.65rem;"
                                       title="{{ $sub->feedback }}"></i>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('guru.submissions.show', $sub->id) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   style="border-radius:7px;width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                                   title="Lihat Detail">
                                    <i class="fas fa-eye" style="font-size:.65rem;"></i>
                                </a>
                                {{-- Inline grade form --}}
                                <button type="button"
                                        class="btn btn-sm {{ $graded ? 'btn-outline-success' : 'btn-outline-warning' }}"
                                        style="border-radius:7px;width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                                        title="{{ $graded ? 'Edit Nilai' : 'Beri Nilai' }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#gradeModal-{{ $sub->id }}">
                                    <i class="fas {{ $graded ? 'fa-edit' : 'fa-star' }}" style="font-size:.65rem;"></i>
                                </button>
                            </div>

                            {{-- Grade Modal --}}
                            <div class="modal fade" id="gradeModal-{{ $sub->id }}" tabindex="-1"
                                 aria-labelledby="gradeLabel-{{ $sub->id }}">
                                <div class="modal-dialog modal-sm modal-dialog-centered">
                                    <div class="modal-content border-0 shadow" style="border-radius:14px;">
                                        <div class="modal-header border-0 pb-0">
                                            <h6 class="modal-title fw-bold" id="gradeLabel-{{ $sub->id }}">
                                                {{ $graded ? 'Edit' : 'Beri' }} Nilai
                                            </h6>
                                            <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST"
                                              action="{{ route('guru.assignments.grade', [$assignment->id, $sub->id]) }}">
                                            @csrf
                                            <div class="modal-body py-2">
                                                <div class="text-muted small mb-2">{{ $name }}</div>
                                                <label class="form-label small fw-semibold">
                                                    Nilai (0–{{ $assignment->max_score ?? 100 }})
                                                </label>
                                                <input type="number" name="score" class="form-control"
                                                       min="0" max="{{ $assignment->max_score ?? 100 }}"
                                                       value="{{ $sub->score }}" required
                                                       style="border-radius:8px;">
                                                <label class="form-label small fw-semibold mt-2">Feedback</label>
                                                <textarea name="feedback" class="form-control" rows="2"
                                                          style="border-radius:8px;resize:none;">{{ $sub->feedback }}</textarea>
                                            </div>
                                            <div class="modal-footer border-0 pt-0">
                                                <button type="submit" class="btn btn-success btn-sm w-100"
                                                        style="border-radius:8px;">
                                                    <i class="fas fa-check me-1"></i>Simpan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($submissions->hasPages())
        <div class="d-flex align-items-center justify-content-between px-4 py-3 border-top">
            <small class="text-muted">
                {{ $submissions->firstItem() }}–{{ $submissions->lastItem() }}
                dari {{ number_format($submissions->total()) }}
            </small>
            {{ $submissions->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

@endsection
