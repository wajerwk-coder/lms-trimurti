@extends('layouts.guru')

@section('title', 'Edit Penilaian')
@section('page-title', 'Edit Penilaian')
@section('page-subtitle', 'Perbarui nilai dan umpan balik untuk siswa.')

@section('page-actions')
    <a href="{{ route('guru.penilaian.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>{{ $errors->count() }} kesalahan:</strong>
        <ul class="mb-0 mt-1 ps-3 small">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('guru.penilaian.update', $submission->id) }}" method="POST" id="assessmentForm">
    @csrf
    @method('PUT')

    <div class="row g-4">
        {{-- Kiri: Info + Form --}}
        <div class="col-lg-8">

            {{-- Info Penilaian --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Informasi Penilaian</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-semibold">JENIS PENILAIAN</label>
                            <div class="fw-medium">
                                @if(isset($submission->assignment_id) && $submission->assignment_id)
                                    <span class="badge bg-primary"><i class="fas fa-tasks me-1"></i>Tugas</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="fas fa-flask me-1"></i>Praktikum</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-semibold">MATA PELAJARAN</label>
                            <div class="fw-medium">
                                @if(isset($submission->assignment_id) && $submission->assignment_id)
                                    {{ $submission->assignment->subject->name ?? '—' }}
                                @else
                                    {{ $submission->practical->subject->name ?? '—' }}
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-semibold">NAMA SISWA</label>
                            <div class="fw-medium">{{ $submission->siswa?->name ?? '—' }}</div>
                            <small class="text-muted">NIS: {{ $submission->siswa?->nis ?? '—' }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-semibold">JUDUL AKTIVITAS</label>
                            <div class="fw-medium">
                                @if(isset($submission->assignment_id) && $submission->assignment_id)
                                    {{ $submission->assignment->title ?? '—' }}
                                @else
                                    {{ $submission->practical->title ?? '—' }}
                                @endif
                            </div>
                        </div>
                        @if($submission->submitted_at)
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-semibold">WAKTU PENGUMPULAN</label>
                            <div class="fw-medium">{{ $submission->submitted_at->format('d M Y H:i') }}</div>
                            <small class="text-muted">{{ $submission->submitted_at->diffForHumans() }}</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- File submission (jika ada) --}}
            @if(isset($submission->file_path) && $submission->file_path)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-file me-2"></i>File Pengumpulan</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                        <div class="bg-primary rounded-3 p-2 me-3">
                            <i class="fas fa-file-alt text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">{{ basename($submission->file_path) }}</div>
                            <small class="text-muted">
                                @if(isset($submission->file_size) && $submission->file_size)
                                    {{ number_format($submission->file_size / 1024, 1) }} KB
                                @else
                                    —
                                @endif
                            </small>
                        </div>
                        <a href="{{ asset('storage/assignment_submissions/' . basename($submission->file_path)) }}" download
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download me-1"></i>Unduh
                        </a>
                    </div>
                    @if($submission->submission_text ?? null)
                        <div class="mt-3">
                            <label class="form-label small text-muted fw-semibold">TEKS JAWABAN</label>
                            <div class="p-3 bg-light rounded-3 small" style="white-space: pre-wrap;">{{ $submission->submission_text }}</div>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Form Penilaian --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-star me-2"></i>Beri Nilai</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="score" class="form-label fw-bold">
                                Nilai <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control form-control-lg @error('score') is-invalid @enderror"
                                       id="score" name="score"
                                       value="{{ old('score', $submission->score) }}"
                                       min="0" max="100" step="0.5" required
                                       placeholder="0 - 100">
                                <span class="input-group-text fw-bold text-primary" id="scoreDisplay">
                                    {{ $submission->score ? number_format($submission->score, 1) : '0' }}
                                </span>
                            </div>
                            <div id="gradeDisplay" class="mt-2">
                                @php
                                    $score = $submission->score ?? 0;
                                    $grade = $score >= 90 ? ['A', 'success'] : ($score >= 80 ? ['B', 'primary'] : ($score >= 70 ? ['C', 'warning text-dark'] : ($score >= 60 ? ['D', 'danger'] : ['E', 'secondary'])));
                                @endphp
                                <span class="badge bg-{{ $grade[1] }} fs-6">Grade {{ $grade[0] }}</span>
                            </div>
                            @error('score')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nilai Maksimal</label>
                            <input type="number" class="form-control form-control-lg bg-light" readonly
                                   value="@if(isset($submission->assignment_id) && $submission->assignment_id){{ $submission->assignment->max_score ?? 100 }}@else{{ $submission->practical->max_score ?? 100 }}@endif">
                            <small class="text-muted">Ditentukan oleh aktivitas</small>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="fw-semibold">Persentase Nilai</small>
                                <small class="text-muted" id="percentText">0%</small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" id="progressBar" role="progressbar"
                                     style="width: {{ $submission->score ?? 0 }}%"></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="feedback" class="form-label fw-bold">Umpan Balik</label>
                            <textarea class="form-control @error('feedback') is-invalid @enderror"
                                      id="feedback" name="feedback" rows="4"
                                      placeholder="Berikan umpan balik konstruktif untuk siswa...">{{ old('feedback', $submission->feedback) }}</textarea>
                            @error('feedback')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kanan: Aksi & Summary --}}
        <div class="col-lg-4">
            {{-- Current Score --}}
            <div class="card border-0 shadow-sm mb-4 text-center">
                <div class="card-body py-4">
                    <div class="mb-2"><small class="text-muted fw-semibold">NILAI SAAT INI</small></div>
                    <div class="display-4 fw-bold text-primary mb-1">{{ $submission->score ?? '—' }}</div>
                    <div class="text-muted small">dari 100</div>
                    @if($submission->score)
                        @php
                            $s = $submission->score;
                            $g = $s >= 90 ? ['A','success'] : ($s >= 80 ? ['B','primary'] : ($s >= 70 ? ['C','warning'] : ($s >= 60 ? ['D','danger'] : ['E','secondary'])));
                        @endphp
                        <div class="mt-2">
                            <span class="badge bg-{{ $g[1] }} px-3 py-2 fs-6">Grade {{ $g[0] }}</span>
                        </div>
                    @else
                        <div class="mt-2"><span class="badge bg-secondary px-3 py-2">Belum Dinilai</span></div>
                    @endif
                </div>
            </div>

            {{-- Panduan Grade --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0 fw-semibold small"><i class="fas fa-list me-2 text-primary"></i>Panduan Grade</h6>
                </div>
                <div class="card-body py-2 px-3">
                    @foreach([['A','success','≥ 90'],['B','primary','80 – 89'],['C','warning text-dark','70 – 79'],['D','danger','60 – 69'],['E','secondary','< 60']] as [$g,$c,$r])
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="badge bg-{{ $c }}" style="width:30px;">{{ $g }}</span>
                            <span class="small text-muted">{{ $r }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-warning fw-semibold" id="submitBtn">
                    <i class="fas fa-save me-1"></i>Perbarui Penilaian
                </button>
                <a href="{{ route('guru.penilaian.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>

            {{-- Meta info --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body small text-muted">
                    <div class="mb-1"><i class="fas fa-clock me-1"></i>Terakhir diperbarui:</div>
                    <div class="fw-medium">{{ $submission->updated_at ? $submission->updated_at->format('d M Y H:i') : '—' }}</div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const scoreInput   = document.getElementById('score');
    const scoreDisplay = document.getElementById('scoreDisplay');
    const gradeDisplay = document.getElementById('gradeDisplay');
    const progressBar  = document.getElementById('progressBar');
    const percentText  = document.getElementById('percentText');

    const grades = [
        [90, 'A', 'success'],
        [80, 'B', 'primary'],
        [70, 'C', 'warning text-dark'],
        [60, 'D', 'danger'],
        [ 0, 'E', 'secondary'],
    ];

    function getGrade(score) {
        for (const [min, letter, color] of grades) {
            if (score >= min) return { letter, color };
        }
        return { letter: 'E', color: 'secondary' };
    }

    function update() {
        const val = parseFloat(scoreInput.value) || 0;
        const pct = Math.min(val, 100);
        const { letter, color } = getGrade(val);

        scoreDisplay.textContent = val.toFixed(1);
        percentText.textContent  = pct.toFixed(1) + '%';
        progressBar.style.width  = pct + '%';
        progressBar.className    = 'progress-bar bg-' + (val >= 70 ? 'success' : val >= 60 ? 'warning' : 'danger');
        gradeDisplay.innerHTML   = `<span class="badge bg-${color} fs-6">Grade ${letter}</span>`;
    }

    scoreInput.addEventListener('input', update);
    update();

    // Loading state on submit
    document.getElementById('assessmentForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
    });
});
</script>
@endpush

@endsection