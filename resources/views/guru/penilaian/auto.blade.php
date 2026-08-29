@extends('layouts.guru')

@section('title', 'Penilaian Praktik Otomatis')
@section('page-title', 'Penilaian Praktik Otomatis')
@section('page-subtitle', 'Sistem penilaian praktik dengan checklist kriteria otomatis.')

@section('page-actions')
    <a href="{{ route('guru.penilaian.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')
<div>
    {{-- Pilih Siswa & Praktik --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="fas fa-user-graduate me-2 text-primary"></i>Pilih Siswa dan Praktik
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="student_id" class="form-label small fw-semibold">Siswa <span class="text-danger">*</span></label>
                    <select name="student_id" id="student_id" class="form-select" required>
                        <option value="">— Pilih Siswa —</option>
                        @foreach($students as $student)
                        <option value="{{ $student->id }}"
                                data-name="{{ $student->name }}"
                                data-class="{{ $student->siswa?->kelas?->name ?? 'N/A' }}"
                                data-nis="{{ $student->siswa?->nis ?? 'N/A' }}">
                            {{ $student->name }} — {{ $student->siswa?->nis ?? 'N/A' }} — {{ $student->siswa?->kelas?->name ?? 'N/A' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="practical_id" class="form-label small fw-semibold">Praktik <span class="text-danger">*</span></label>
                    <select name="practical_id" id="practical_id" class="form-select" required>
                        <option value="">— Pilih Praktik —</option>
                        @foreach($practicals as $practical)
                        <option value="{{ $practical->id }}"
                                data-title="{{ $practical->title }}"
                                data-subject="{{ $practical->subject?->name ?? 'N/A' }}"
                                data-max-score="{{ $practical->max_score }}"
                                data-class="{{ $practical->kelas?->name ?? 'N/A' }}">
                            {{ $practical->title }} — {{ $practical->subject?->name ?? 'N/A' }} ({{ $practical->kelas?->name ?? 'N/A' }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Assessment Form --}}
    <form id="autoAssessmentForm" method="POST" action="{{ route('guru.penilaian.auto.save') }}">
        @csrf
        <div class="card shadow mb-4" id="practicalInfo" style="display: none;">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-info">
                    <i class="fas fa-info-circle me-2"></i>Informasi Praktik
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Judul:</strong> <span id="practicalTitle">-</span></p>
                        <p class="mb-2"><strong>Mata Pelajaran:</strong> <span id="practicalSubject">-</span></p>
                        <p class="mb-2"><strong>Nilai Maksimum:</strong> <span id="practicalMaxScore">-</span></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Tanggal:</strong> <input type="date" name="assessment_date" class="form-control" required></p>
                        <p class="mb-2"><strong>Feedback:</strong> <textarea name="feedback" class="form-control" rows="3" placeholder="Berikan feedback untuk siswa..."></textarea></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assessment Criteria -->
        <div class="card shadow mb-4" id="assessmentCriteria" style="display: none;">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="fas fa-clipboard-check me-2"></i>Kriteria Penilaian
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Persiapan -->
                    <div class="col-md-4">
                        <h6 class="text-center mb-3 text-info">
                            <i class="fas fa-clipboard-list me-2"></i>Persiapan (40%)
                        </h6>
                        <div class="list-group">
                            <label class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <input type="checkbox" name="kriteria_nilai[prep_1]" value="1" class="me-2">
                                        Persiapan alat dan bahan
                                    </span>
                                    <small class="text-muted">20%</small>
                                </div>
                            </label>
                            <label class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <input type="checkbox" name="kriteria_nilai[prep_2]" value="1" class="me-2">
                                        Pemahaman prosedur
                                    </span>
                                    <small class="text-muted">15%</small>
                                </div>
                            </label>
                            <label class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <input type="checkbox" name="kriteria_nilai[prep_3]" value="1" class="me-2">
                                        Kebersihan dan kerapian
                                    </span>
                                    <small class="text-muted">15%</small>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Pelaksanaan -->
                    <div class="col-md-4">
                        <h6 class="text-center mb-3 text-success">
                            <i class="fas fa-play-circle me-2"></i>Pelaksanaan (40%)
                        </h6>
                        <div class="list-group">
                            <label class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <input type="checkbox" name="kriteria_nilai[exec_1]" value="1" class="me-2">
                                        Teknik pelaksanaan
                                    </span>
                                    <small class="text-muted">25%</small>
                                </div>
                            </label>
                            <label class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <input type="checkbox" name="kriteria_nilai[exec_2]" value="1" class="me-2">
                                        Keamanan kerja
                                    </span>
                                    <small class="text-muted">20%</small>
                                </div>
                            </label>
                            <label class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <input type="checkbox" name="kriteria_nilai[exec_3]" value="1" class="me-2">
                                        Efisiensi waktu
                                    </span>
                                    <small class="text-muted">20%</small>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Hasil -->
                    <div class="col-md-4">
                        <h6 class="text-center mb-3 text-warning">
                            <i class="fas fa-check-circle me-2"></i>Hasil (20%)
                        </h6>
                        <div class="list-group">
                            <label class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <input type="checkbox" name="kriteria_nilai[result_1]" value="1" class="me-2">
                                        Kualitas hasil
                                    </span>
                                    <small class="text-muted">30%</small>
                                </div>
                            </label>
                            <label class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <input type="checkbox" name="kriteria_nilai[result_2]" value="1" class="me-2">
                                        Laporan praktikum
                                    </span>
                                    <small class="text-muted">20%</small>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Score Display -->
        <div class="card shadow mb-4" id="scoreDisplay" style="display: none;">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-success">
                    <i class="fas fa-calculator me-2"></i>Hasil Penilaian
                </h6>
            </div>
            <div class="card-body text-center">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card border-left-primary">
                            <div class="card-body">
                                <h5 class="text-primary" id="prepScore">0%</h5>
                                <small>Persiapan</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-success">
                            <div class="card-body">
                                <h5 class="text-success" id="execScore">0%</h5>
                                <small>Pelaksanaan</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-warning">
                            <div class="card-body">
                                <h5 class="text-warning" id="resultScore">0%</h5>
                                <small>Hasil</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-info">
                            <div class="card-body">
                                <h5 class="text-info" id="totalScore">0</h5>
                                <small>Total Nilai</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h4 class="text-center">
                        <span class="badge bg-primary badge-lg" id="gradeDisplay">-</span>
                    </h4>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="text-center" id="submitSection" style="display: none;">
            <button type="submit" class="btn btn-primary btn-lg px-5">
                <i class="fas fa-save me-2"></i>Simpan Penilaian
            </button>
        </div>
    </form>
</div>

<!-- Custom Styles -->
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.text-primary {
    color: #4e73df !important;
}

.text-success {
    color: #1cc88a !important;
}

.text-warning {
    color: #f6c23e !important;
}

.text-info {
    color: #36b9cc !important;
}

.bg-primary {
    background-color: #4e73df;
}

.badge-lg {
    font-size: 1.25rem;
    padding: 0.5rem 1rem;
}

.list-group-item {
    border: 1px solid #dee2e6;
    padding: 0.75rem 1rem;
    margin-bottom: 0.25rem;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.list-group-item:hover {
    background-color: #f8f9fc;
    border-color: #4e73df;
}

.list-group-item-action {
    display: block;
    text-decoration: none;
    color: #495057;
}

.fw-bold {
    font-weight: 700;
}

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let selectedPractical = null;

    const practicalSel = document.getElementById('practical_id');
    const practicalInfo = document.getElementById('practicalInfo');
    const scoreDisplay  = document.getElementById('scoreDisplay');
    const assessCrit    = document.getElementById('assessmentCriteria');
    const submitSec     = document.getElementById('submitSection');

    function show(el) { if (el) el.style.display = 'block'; }
    function hide(el) { if (el) el.style.display = 'none'; }
    function setText(id, val) { const el = document.getElementById(id); if (el) el.textContent = val; }

    if (practicalSel) {
        practicalSel.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const id  = this.value;
            if (id) {
                selectedPractical = {
                    id, title: opt.dataset.title,
                    subject: opt.dataset.subject, maxScore: opt.dataset.maxScore
                };
                setText('practicalTitle', selectedPractical.title);
                setText('practicalSubject', selectedPractical.subject);
                setText('practicalMaxScore', selectedPractical.maxScore);
                show(practicalInfo); show(assessCrit); show(submitSec);
            } else {
                selectedPractical = null;
                hide(practicalInfo); hide(assessCrit); hide(scoreDisplay); hide(submitSec);
            }
        });
    }

    // Handle criteria checkboxes
    document.querySelectorAll('input[name^="kriteria_nilai"]').forEach(function (cb) {
        cb.addEventListener('change', calculateScore);
    });

    const criteriaWeights = {
        'prep_1':0.20,'prep_2':0.15,'prep_3':0.15,
        'exec_1':0.25,'exec_2':0.20,'exec_3':0.20,
        'result_1':0.30,'result_2':0.20
    };

    function calculateScore() {
        if (!selectedPractical) return;
        let prep = 0, exec = 0, result = 0, total = 0;

        Object.keys(criteriaWeights).forEach(function (key) {
            const cb = document.querySelector('input[name="kriteria_nilai[' + key + ']"]');
            if (cb && cb.checked) {
                const w = criteriaWeights[key] * 100;
                total += criteriaWeights[key] * 100;
                if (key.startsWith('prep'))   prep   += w;
                if (key.startsWith('exec'))   exec   += w;
                if (key.startsWith('result')) result += w;
            }
        });

        setText('prepScore',   Math.round(prep)   + '%');
        setText('execScore',   Math.round(exec)   + '%');
        setText('resultScore', Math.round(result) + '%');
        setText('totalScore',  total.toFixed(1));

        let grade = 'E';
        if      (total >= 90) grade = 'A';
        else if (total >= 80) grade = 'B';
        else if (total >= 70) grade = 'C';
        else if (total >= 60) grade = 'D';
        setText('gradeDisplay', grade);

        if (total > 0) show(scoreDisplay);
    }
});
</script>
@endpush
@endsection