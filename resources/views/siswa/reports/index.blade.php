@extends('layouts.siswa')

@section('title', 'Laporan & Nilai')
@section('page-title', 'Laporan & Nilai Saya')
@section('page-subtitle', 'Ringkasan akademik dan riwayat penilaian.')

@section('content')

{{-- Stats Cards --}}
<div class="row g-3 mb-4">
    @foreach([
        ['info',    'fa-tasks',          $totalAssignments   ?? 0, 'Total Tugas'],
        ['success', 'fa-check-circle',   $gradedAssignments  ?? 0, 'Tugas Dinilai'],
        ['warning', 'fa-flask',          $totalPracticals    ?? 0, 'Praktikum'],
        ['primary', 'fa-calendar-check', ($presentAttendances ?? 0) . '/' . ($totalAttendances ?? 0), 'Kehadiran'],
    ] as [$color, $icon, $val, $label])
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-{{ $color }} bg-opacity-10 flex-shrink-0">
                    <i class="fas {{ $icon }} text-{{ $color }} fa-lg"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0">{{ $val }}</div>
                    <small class="text-muted">{{ $label }}</small>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-4" id="reportTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" id="tugas-tab" data-bs-toggle="tab"
                data-bs-target="#tab-tugas" type="button" role="tab">
            <i class="fas fa-tasks me-1"></i>Nilai Tugas
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="praktik-tab" data-bs-toggle="tab"
                data-bs-target="#tab-praktik" type="button" role="tab">
            <i class="fas fa-flask me-1"></i>Nilai Praktikum
        </button>
    </li>
</ul>

<div class="tab-content" id="reportTabContent">

    {{-- Tab Tugas --}}
    <div class="tab-pane fade show active" id="tab-tugas" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Judul Tugas</th>
                                <th>Mata Pelajaran</th>
                                <th class="text-center">Nilai</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignmentSubmissions ?? [] as $submission)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $submission->assignment?->title ?? '—' }}</td>
                                <td class="text-muted">{{ $submission->assignment?->subject?->name ?? '—' }}</td>
                                <td class="text-center">
                                    @if($submission->score !== null)
                                        @php
                                            $s = $submission->score;
                                            $c = $s >= 90 ? 'success' : ($s >= 75 ? 'primary' : ($s >= 60 ? 'warning' : 'danger'));
                                        @endphp
                                        <span class="badge bg-{{ $c }} fs-6">{{ $s }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($submission->score !== null)
                                        <span class="badge bg-success">Dinilai</span>
                                    @else
                                        <span class="badge bg-secondary">Belum dinilai</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-tasks fa-2x opacity-25 mb-2 d-block"></i>
                                    Belum ada tugas.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab Praktikum --}}
    <div class="tab-pane fade" id="tab-praktik" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Judul Praktikum</th>
                                <th>Mata Pelajaran</th>
                                <th class="text-center">Nilai</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($practicalScores ?? [] as $score)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $score->practical?->title ?? '—' }}</td>
                                <td class="text-muted">{{ $score->practical?->subject?->name ?? '—' }}</td>
                                <td class="text-center">
                                    @if($score->score !== null)
                                        @php
                                            $s = $score->score;
                                            $c = $s >= 90 ? 'success' : ($s >= 75 ? 'primary' : ($s >= 60 ? 'warning' : 'danger'));
                                        @endphp
                                        <span class="badge bg-{{ $c }} fs-6">{{ $s }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($score->score !== null)
                                        <span class="badge bg-success">Dinilai</span>
                                    @else
                                        <span class="badge bg-secondary">Belum dinilai</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-flask fa-2x opacity-25 mb-2 d-block"></i>
                                    Belum ada nilai praktikum.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
