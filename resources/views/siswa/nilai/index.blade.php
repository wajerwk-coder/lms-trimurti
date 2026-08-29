@extends('layouts.siswa')

@section('title', 'Rekap Nilai')
@section('page-title', 'Rekap Nilai')
@section('page-subtitle', 'Ringkasan nilai tugas dan praktikum saya.')

@section('page-actions')
    <a href="{{ route('siswa.nilai.export') }}" class="btn btn-outline-success btn-sm">
        <i class="fas fa-file-csv me-1"></i>Export CSV
    </a>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@php
    $practicalAvg  = $stats['practical_avg']  ?? 0;
    $assignmentAvg = $stats['assignment_avg'] ?? 0;
    $overallAvg    = $stats['overall_avg']    ?? 0;
    $totalPractical   = $stats['total_practical_scores']   ?? 0;
    $totalAssignment  = $stats['total_graded_assignments'] ?? 0;

    $overallColor = $overallAvg >= 80 ? 'success' : ($overallAvg >= 60 ? 'warning' : 'danger');
    $aColor       = $assignmentAvg >= 80 ? 'success' : ($assignmentAvg >= 60 ? 'warning' : 'danger');
    $pColor       = $practicalAvg  >= 80 ? 'success' : ($practicalAvg  >= 60 ? 'warning' : 'danger');
    $overallGrade = $overallAvg >= 90 ? 'A' : ($overallAvg >= 80 ? 'B' : ($overallAvg >= 70 ? 'C' : ($overallAvg >= 60 ? 'D' : 'E')));
@endphp

{{-- Stats Cards --}}
<div class="row g-3 mb-4">

    {{-- Nilai rata-rata keseluruhan --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100"
             style="border-top: 4px solid var(--bs-{{ $overallColor }}) !important;">
            <div class="card-body text-center py-4">
                <div class="small text-muted fw-semibold mb-2 text-uppercase" style="font-size:.72rem;letter-spacing:.05em;">
                    Nilai Rata-rata Keseluruhan
                </div>
                <div class="d-flex align-items-baseline justify-content-center gap-2 mb-2">
                    <span class="fw-bold text-{{ $overallColor }}" style="font-size:2.5rem;line-height:1;">
                        {{ number_format($overallAvg, 1) }}
                    </span>
                    <span class="text-muted small">/100</span>
                </div>
                <div class="progress mb-2" style="height:6px;border-radius:3px;">
                    <div class="progress-bar bg-{{ $overallColor }}" style="width:{{ min($overallAvg,100) }}%"></div>
                </div>
                <span class="badge bg-{{ $overallColor }} px-3 py-1">Grade {{ $overallGrade }}</span>
            </div>
        </div>
    </div>

    {{-- Tugas --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100"
             style="border-top: 4px solid var(--bs-{{ $aColor }}) !important;">
            <div class="card-body text-center py-4">
                <div class="small text-muted fw-semibold mb-2 text-uppercase" style="font-size:.72rem;letter-spacing:.05em;">
                    Rata-rata Tugas
                </div>
                <div class="d-flex align-items-baseline justify-content-center gap-2 mb-2">
                    <span class="fw-bold text-{{ $aColor }}" style="font-size:2.5rem;line-height:1;">
                        {{ number_format($assignmentAvg, 1) }}
                    </span>
                    <span class="text-muted small">/100</span>
                </div>
                <div class="progress mb-2" style="height:6px;border-radius:3px;">
                    <div class="progress-bar bg-{{ $aColor }}" style="width:{{ min($assignmentAvg,100) }}%"></div>
                </div>
                <div class="small text-muted">{{ $totalAssignment }} tugas dinilai</div>
            </div>
        </div>
    </div>

    {{-- Praktikum --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100"
             style="border-top: 4px solid var(--bs-{{ $pColor }}) !important;">
            <div class="card-body text-center py-4">
                <div class="small text-muted fw-semibold mb-2 text-uppercase" style="font-size:.72rem;letter-spacing:.05em;">
                    Rata-rata Praktikum
                </div>
                <div class="d-flex align-items-baseline justify-content-center gap-2 mb-2">
                    <span class="fw-bold text-{{ $pColor }}" style="font-size:2.5rem;line-height:1;">
                        {{ number_format($practicalAvg, 1) }}
                    </span>
                    <span class="text-muted small">/100</span>
                </div>
                <div class="progress mb-2" style="height:6px;border-radius:3px;">
                    <div class="progress-bar bg-{{ $pColor }}" style="width:{{ min($practicalAvg,100) }}%"></div>
                </div>
                <div class="small text-muted">{{ $totalPractical }} praktikum dinilai</div>
            </div>
        </div>
    </div>

</div>

{{-- Tabs --}}
<ul class="nav nav-pills gap-1 mb-3" id="nilaiTabs">
    <li class="nav-item">
        <button class="nav-link active px-4 fw-semibold" data-bs-toggle="tab"
                data-bs-target="#tab-tugas">
            <i class="fas fa-tasks me-2"></i>Tugas
            @if($assignmentScores->total())
                <span class="badge bg-primary bg-opacity-20 text-primary ms-1">{{ $assignmentScores->total() }}</span>
            @endif
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link px-4 fw-semibold" data-bs-toggle="tab"
                data-bs-target="#tab-praktik">
            <i class="fas fa-flask me-2"></i>Praktikum
            @if($practicalScores->total())
                <span class="badge bg-warning bg-opacity-20 text-warning ms-1">{{ $practicalScores->total() }}</span>
            @endif
        </button>
    </li>
</ul>

<div class="tab-content">

    {{-- TAB TUGAS --}}
    <div class="tab-pane fade show active" id="tab-tugas">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Judul Tugas</th>
                                <th>Mata Pelajaran</th>
                                <th class="text-center" style="width:90px;">Nilai</th>
                                <th class="text-center" style="width:80px;">Grade</th>
                                <th style="width:110px;">Tanggal</th>
                                <th class="pe-4" style="width:130px;">Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignmentScores as $score)
                            @php
                                $val = (float)($score->score ?? 0);
                                $max = (float)($score->assignment?->max_score ?? 100);
                                $pct = $max > 0 ? min(100, ($val / $max) * 100) : 0;
                                [$gl, $gc] = $val >= 90 ? ['A','success'] : ($val >= 80 ? ['B','primary'] : ($val >= 70 ? ['C','info'] : ($val >= 60 ? ['D','warning'] : ['E','danger'])));
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold">{{ $score->assignment?->title ?? '—' }}</div>
                                    @if($score->feedback)
                                        <small class="text-muted">
                                            <i class="fas fa-comment-dots me-1"></i>{{ Str::limit($score->feedback, 50) }}
                                        </small>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $score->assignment?->subject?->name ?? '—' }}</td>
                                <td class="text-center fw-bold text-{{ $gc }} fs-6">{{ number_format($val, 0) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $gc }} px-2">{{ $gl }}</span>
                                </td>
                                <td class="text-muted">{{ $score->updated_at?->format('d/m/Y') ?? '—' }}</td>
                                <td class="pe-4">
                                    <div class="progress" style="height:6px;border-radius:3px;">
                                        <div class="progress-bar bg-{{ $gc }}" style="width:{{ $pct }}%"></div>
                                    </div>
                                    <div class="text-muted text-end" style="font-size:.68rem;">{{ number_format($pct, 0) }}%</div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-tasks fa-2x text-muted opacity-25 mb-3 d-block"></i>
                                    <p class="text-muted mb-0">Belum ada nilai tugas.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($assignmentScores->hasPages())
                <div class="card-footer bg-white border-top">{{ $assignmentScores->links() }}</div>
            @endif
        </div>
    </div>

    {{-- TAB PRAKTIKUM --}}
    <div class="tab-pane fade" id="tab-praktik">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Judul Praktikum</th>
                                <th>Mata Pelajaran</th>
                                <th class="text-center" style="width:90px;">Nilai</th>
                                <th class="text-center" style="width:80px;">Grade</th>
                                <th style="width:110px;">Tanggal</th>
                                <th class="pe-4" style="width:130px;">Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($practicalScores as $score)
                            @php
                                $val = (float)($score->score ?? 0);
                                $pct = min(100, $val);
                                [$gl, $gc] = $val >= 90 ? ['A','success'] : ($val >= 80 ? ['B','primary'] : ($val >= 70 ? ['C','info'] : ($val >= 60 ? ['D','warning'] : ['E','danger'])));
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold">{{ $score->practical?->title ?? '—' }}</div>
                                    @if($score->feedback)
                                        <small class="text-muted">
                                            <i class="fas fa-comment-dots me-1"></i>{{ Str::limit($score->feedback, 50) }}
                                        </small>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $score->practical?->subject?->name ?? '—' }}</td>
                                <td class="text-center fw-bold text-{{ $gc }} fs-6">{{ number_format($val, 1) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $gc }} px-2">{{ $gl }}</span>
                                </td>
                                <td class="text-muted">
                                    {{ ($score->graded_at ?? $score->created_at)?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="pe-4">
                                    <div class="progress" style="height:6px;border-radius:3px;">
                                        <div class="progress-bar bg-{{ $gc }}" style="width:{{ $pct }}%"></div>
                                    </div>
                                    <div class="text-muted text-end" style="font-size:.68rem;">{{ number_format($pct, 0) }}%</div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-flask fa-2x text-muted opacity-25 mb-3 d-block"></i>
                                    <p class="text-muted mb-0">Belum ada nilai praktikum.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($practicalScores->hasPages())
                <div class="card-footer bg-white border-top">{{ $practicalScores->links() }}</div>
            @endif
        </div>
    </div>

</div>

@push('css')
<style>
.nav-pills .nav-link { color:#6c757d; font-size:.875rem; border-radius:8px; }
.nav-pills .nav-link.active { background-color:#4f46e5; color:#fff; }
</style>
@endpush

@endsection
