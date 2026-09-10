@extends('layouts.admin')

@section('title', 'Rekapitulasi Nilai')
@section('page-title', 'Rekapitulasi Nilai')
@section('page-subtitle', 'Pantau nilai praktikum dan tugas seluruh siswa')

@section('content')
<div class="container-fluid px-0">

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-flask text-primary fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ $stats['total_practical'] }}</div>
                        <small class="text-muted">Nilai Praktikum</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-tasks text-success fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ $stats['total_assignment'] }}</div>
                        <small class="text-muted">Nilai Tugas</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-info bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-chart-bar text-info fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ $stats['avg_practical'] }}</div>
                        <small class="text-muted">Rata-rata Praktikum</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-warning bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-star text-warning fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ $stats['avg_assignment'] }}</div>
                        <small class="text-muted">Rata-rata Tugas</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.scores.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-sm-4 col-md-3">
                        <label class="form-label small fw-semibold mb-1">Siswa</label>
                        <select name="siswa_id" class="form-select form-select-sm">
                            <option value="">Semua Siswa</option>
                            @foreach($siswas as $s)
                                <option value="{{ $s->id }}" {{ request('siswa_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-4 col-md-3">
                        <label class="form-label small fw-semibold mb-1">Kelas</label>
                        <select name="kelas_id" class="form-select form-select-sm">
                            <option value="">Semua Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-4 col-md-3">
                        <label class="form-label small fw-semibold mb-1">Mata Pelajaran</label>
                        <select name="subject_id" class="form-select form-select-sm">
                            <option value="">Semua Mapel</option>
                            @foreach($subjects as $subj)
                                <option value="{{ $subj->id }}" {{ request('subject_id') == $subj->id ? 'selected' : '' }}>
                                    {{ $subj->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-12 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.scores.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Nav tabs --}}
    <ul class="nav nav-tabs mb-0" id="scoreTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-practical" type="button">
                <i class="fas fa-flask me-1"></i>Nilai Praktikum
                <span class="badge bg-primary ms-1">{{ method_exists($practicalScores,'total') ? $practicalScores->total() : 0 }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-assignment" type="button">
                <i class="fas fa-tasks me-1"></i>Nilai Tugas
                <span class="badge bg-success ms-1">{{ method_exists($assignmentScores,'total') ? $assignmentScores->total() : 0 }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- Tab Praktikum --}}
        <div class="tab-pane fade show active" id="tab-practical">
            <div class="card border-0 shadow-sm border-top-0" style="border-radius:0 0 12px 12px;">
                <div class="card-body p-0">
                    @if(method_exists($practicalScores,'count') && $practicalScores->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Siswa</th>
                                    <th>Praktikum</th>
                                    <th>Mata Pelajaran</th>
                                    <th class="text-center">Nilai</th>
                                    <th class="text-center">Grade</th>
                                    <th>Dinilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($practicalScores as $score)
                                @php
                                    $val   = (float)($score->score ?? 0);
                                    $grade = $val >= 90 ? 'A' : ($val >= 80 ? 'B' : ($val >= 70 ? 'C' : ($val >= 60 ? 'D' : 'E')));
                                    $gc    = ['A'=>'success','B'=>'primary','C'=>'info','D'=>'warning','E'=>'danger'][$grade];
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold">{{ $score->siswa?->name ?? '—' }}</div>
                                        <small class="text-muted">{{ $score->siswa?->email ?? '' }}</small>
                                    </td>
                                    <td>{{ $score->practical?->title ?? '—' }}</td>
                                    <td>
                                        @if($score->practical?->subject)
                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                {{ $score->practical->subject->name }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-{{ $gc }}">{{ $val }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $gc }}">{{ $grade }}</span>
                                    </td>
                                    <td class="text-muted small">
                                        {{ $score->graded_at ? \Carbon\Carbon::parse($score->graded_at)->format('d/m/Y') : ($score->created_at ? \Carbon\Carbon::parse($score->created_at)->format('d/m/Y') : '—') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($practicalScores->hasPages())
                    <div class="card-footer bg-white">
                        {{ $practicalScores->appends(request()->except('p_page'))->links() }}
                    </div>
                    @endif
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-flask fa-3x text-muted opacity-25 mb-3 d-block"></i>
                        <h6 class="text-muted">Belum ada nilai praktikum</h6>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tab Tugas --}}
        <div class="tab-pane fade" id="tab-assignment">
            <div class="card border-0 shadow-sm border-top-0" style="border-radius:0 0 12px 12px;">
                <div class="card-body p-0">
                    @if(method_exists($assignmentScores,'count') && $assignmentScores->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Siswa</th>
                                    <th>Tugas</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Guru</th>
                                    <th class="text-center">Nilai</th>
                                    <th class="text-center">Grade</th>
                                    <th>Dinilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assignmentScores as $score)
                                @php
                                    $val   = (float)($score->score ?? 0);
                                    $grade = $val >= 90 ? 'A' : ($val >= 80 ? 'B' : ($val >= 70 ? 'C' : ($val >= 60 ? 'D' : 'E')));
                                    $gc    = ['A'=>'success','B'=>'primary','C'=>'info','D'=>'warning','E'=>'danger'][$grade];
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold">{{ $score->siswa?->name ?? '—' }}</div>
                                        <small class="text-muted">{{ $score->siswa?->email ?? '' }}</small>
                                    </td>
                                    <td>{{ $score->assignment?->title ?? '—' }}</td>
                                    <td>
                                        @if($score->assignment?->subject)
                                            <span class="badge bg-success bg-opacity-10 text-success">
                                                {{ $score->assignment->subject->name ?? $score->assignment->subject->nama ?? '—' }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $score->assignment?->guru?->name ?? '—' }}</td>
                                    <td class="text-center">
                                        <span class="fw-bold text-{{ $gc }}">{{ $val }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $gc }}">{{ $grade }}</span>
                                    </td>
                                    <td class="text-muted small">
                                        {{ $score->updated_at ? \Carbon\Carbon::parse($score->updated_at)->format('d/m/Y') : '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($assignmentScores->hasPages())
                    <div class="card-footer bg-white">
                        {{ $assignmentScores->appends(request()->except('a_page'))->links() }}
                    </div>
                    @endif
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-tasks fa-3x text-muted opacity-25 mb-3 d-block"></i>
                        <h6 class="text-muted">Belum ada nilai tugas</h6>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- /tab-content --}}

</div>
@endsection
