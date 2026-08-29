@extends('layouts.guru')

@section('title', $assignment->title)
@section('page-title', $assignment->title)
@section('page-subtitle', ($assignment->subject?->name ?? ($assignment->getClassSubject()?->subject_name ?? '—')))

@section('page-actions')
    <a href="{{ route('guru.assignments.edit', $assignment->id) }}" class="btn btn-warning btn-sm me-1">
        <i class="fas fa-edit me-1"></i>Edit
    </a>
    <a href="{{ route('guru.assignments.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
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
<div class="row g-4">

    {{-- Kolom Kiri --}}
    <div class="col-lg-8">

        {{-- Detail Tugas --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-tasks me-2 text-primary"></i>Detail Tugas
                </h6>
                @if($assignment->is_published)
                    <span class="badge bg-success">Dipublikasikan</span>
                @else
                    <span class="badge bg-warning text-dark">Draft</span>
                @endif
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small mb-1">Deskripsi</div>
                    <div class="bg-light rounded-2 p-3 small">{{ $assignment->description }}</div>
                </div>
                @if($assignment->instructions)
                <div class="mb-3">
                    <div class="text-muted small mb-1">Instruksi Detail</div>
                    <div class="bg-light rounded-2 p-3 small">
                        @if($assignment->formatted_instructions)
                            {!! $assignment->formatted_instructions !!}
                        @else
                            {{ $assignment->instructions }}
                        @endif
                    </div>
                </div>
                @endif
                @if($assignment->file_url ?? $assignment->file)
                @php $fileToShow = $assignment->file_url ?? $assignment->file; @endphp
                <div class="d-flex align-items-center gap-2 p-3 bg-primary bg-opacity-10 rounded-2 small">
                    <i class="fas fa-paperclip text-primary flex-shrink-0"></i>
                    <span class="flex-grow-1 text-muted">Lampiran:</span>
                    <a href="{{ asset('storage/assignments/' . $fileToShow) }}" download target="_blank"
                       class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download me-1"></i>Unduh
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Submission Terbaru --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-inbox me-2 text-success"></i>Pengumpulan Terbaru
                </h6>
                <a href="{{ route('guru.penilaian.index', ['assignment_id' => $assignment->id]) }}"
                   class="btn btn-outline-primary btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @forelse($recentSubmissions as $submission)
                <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom small">
                    <img src="{{ $submission->student?->avatar_url ?? asset('images/default-avatar.png') }}"
                         class="rounded-circle flex-shrink-0"
                         style="width:34px;height:34px;object-fit:cover;" alt=""
                         onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-semibold text-truncate">{{ $submission->student?->name ?? 'N/A' }}</div>
                        <div class="text-muted" style="font-size:.72rem;">
                            {{ $submission->submitted_at?->diffForHumans() ?? '—' }}
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        @php
                            $s = $submission->status ?? 'submitted';
                            $bc = ['graded'=>'success','submitted'=>'primary','late'=>'warning'][$s] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $bc }}" style="font-size:.7rem;">{{ ucfirst($s) }}</span>
                        @if($submission->score !== null)
                            <div class="fw-bold text-primary mt-1" style="font-size:.8rem;">
                                {{ $submission->score }}/{{ $assignment->max_score }}
                            </div>
                        @endif
                    </div>
                    <a href="{{ route('guru.penilaian.edit', $submission->id) }}"
                       class="btn btn-warning btn-sm flex-shrink-0" style="font-size:.72rem;padding:.2rem .5rem;">
                        Nilai
                    </a>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-2x opacity-25 mb-2 d-block"></i>
                    <small>Belum ada pengumpulan.</small>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Kolom Kanan --}}
    <div class="col-lg-4">

        {{-- Stats --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-chart-bar me-2 text-info"></i>Statistik
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach([
                        ['primary', $assignment->submissions_count ?? 0, 'Dikumpulkan'],
                        ['success', $assignment->graded_count ?? 0,      'Dinilai'],
                        ['warning', $assignment->pending_count ?? 0,     'Belum Dinilai'],
                        ['danger',  $assignment->missing_count ?? 0,     'Belum Kumpul'],
                    ] as [$c, $v, $l])
                    <div class="col-6">
                        <div class="text-center p-2 rounded-2 bg-{{ $c }} bg-opacity-10">
                            <div class="h4 fw-bold mb-0 text-{{ $c }}">{{ $v }}</div>
                            <div class="text-muted" style="font-size:.7rem;">{{ $l }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Info --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-info-circle me-2 text-secondary"></i>Info
                </h6>
            </div>
            <div class="card-body small">
                @php
                    $csData = $assignment->getClassSubject();
                    $infoRows = [
                        ['Mata Pelajaran', $csData->subject_name ?? $assignment->subject?->name ?? '—'],
                        ['Kelas',          $csData->class_name  ?? '—'],
                        ['Nilai Maks.',    $assignment->max_score],
                        ['Deadline',       $assignment->due_date?->format('d/m/Y H:i') ?? '—'],
                        ['Terlambat',      $assignment->allow_late ? 'Diizinkan' : 'Tidak'],
                        ['Dibuat',         $assignment->created_at->format('d M Y')],
                    ];
                @endphp
                @foreach($infoRows as [$label, $val])
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">{{ $label }}</span>
                    <span class="fw-semibold text-dark">{{ $val }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Aksi --}}
        <div class="d-flex flex-column gap-2">
            <a href="{{ route('guru.assignments.edit', $assignment->id) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit me-1"></i>Edit Tugas
            </a>
            <a href="{{ route('guru.penilaian.index', ['assignment_id' => $assignment->id]) }}"
               class="btn btn-outline-primary btn-sm">
                <i class="fas fa-star me-1"></i>Beri Penilaian
            </a>
            <a href="{{ route('guru.assignments.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>

</div>
@endsection