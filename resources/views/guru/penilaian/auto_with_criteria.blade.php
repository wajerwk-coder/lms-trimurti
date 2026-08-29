@extends('layouts.guru')

@section('title', 'Penilaian Otomatis SOP')
@section('page-title', 'Penilaian Otomatis SOP')
@section('page-subtitle', 'Sistem penilaian praktik berdasarkan Standar Operasional Prosedur.')

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
                </div>
                <div class="col-md-6">
                    <label for="practical_id" class="form-label small fw-semibold">Praktik <span class="text-danger">*</span></label>
                    <select name="practical_id" id="practical_id" class="form-select" required>
                        <option value="">— Pilih Praktik —</option>
                        @foreach($practicals as $practical)
                            <option value="{{ $practical->id }}" 
                                    data-title="{{ $practical->title }}"
                                    data-subject="{{ $practical->subject->name ?? 'N/A' }}"
                                    data-max-score="{{ $practical->max_score }}"
                                    data-class="{{ $practical->kelas->name ?? 'N/A' }}">
                                {{ $practical->title }} - {{ $practical->subject->name ?? 'N/A' }} ({{ $practical->kelas->name ?? 'N/A' }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assessment Form -->
    <form id="autoAssessmentForm" method="POST" action="{{ route('guru.penilaian.auto-criteria.save') }}">
        @csrf
        
        <!-- Practical Info Card -->
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

        <!-- SOP Assessment Criteria -->
        <div class="card shadow mb-4" id="assessmentCriteria" style="display: none;">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="fas fa-clipboard-check me-2"></i>Kriteria SOP
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Persiapan SOP -->
                    <div class="col-md-4">
                        <h6 class="text-center mb-3 text-info">
                            <i class="fas fa-clipboard-list me-2"></i>Persiapan SOP (35%)
                        </h6>
                        <div class="list-group">
                            <label class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <input type="checkbox" name="kriteria_nilai[prep_1]" value="1" class="me-2">
                                        Ceklis daftar bahan
                                    </span>
                                    <small class="text-muted">10%</small>
                                </div>
                            </label>
                            <label class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <input type="checkbox" name="kriteria_nilai[prep_2]" value="1" class="me-2">
                                        Persiapan alat kerja
                                    </span>
                                    <small class="text-muted">10%</small>
                                </div>
                            </label>
                            <label class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <input type="checkbox" name="kriteria_nilai[prep_3]" value="1" class="me-2">
                                        Pemahaman SOP
                                    </span>
                                    <small class="text-muted">15%</small>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Pelaksanaan SOP -->
                    <div class="col-md-4">
                        <h6 class="text-center mb-3 text-success">
                            <i class="fas fa-play-circle me-2"></i>Pelaksanaan SOP (45%)
                        </h6>
                        <div class="list-group">
                            <label class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <input type="checkbox" name="kriteria_nilai[exec_1]" value="1" class="me-2">
                                        Mengikuti prosedur
                                    </span>
                                    <small class="text-muted">20%</small>
                                </div>
                            </label>
                            <label class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <input type="checkbox" name="kriteria_nilai[exec_2]" value="1" class="me-2">
                                        Keamanan kerja
                                    </span>
                                    <small class="text-muted">15%</small>
                                </div>
                            </label>
                            <label class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <input type="checkbox" name="kriteria_nilai[exec_3]" value="1" class="me-2">
                                        Dokumentasi proses
                                    </span>
                                    <small class="text-muted">10%</small>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Evaluasi SOP -->
                    <div class="col-md-4">
                        <h6 class="text-center mb-3 text-warning">
                            <i class="fas fa-check-circle me-2"></i>Evaluasi SOP (20%)
                        </h6>
                        <div class="list-group">
                            <label class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <input type="checkbox" name="kriteria_nilai[eval_1]" value="1" class="me-2">
                                        Hasil sesuai standar
                                    </span>
                                    <small class="text-muted">15%</small>
                                </div>
                            </label>
                            <label class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <input type="checkbox" name="kriteria_nilai[eval_2]" value="1" class="me-2">
                                        Laporan evaluasi
                                    </span>
                                    <small class="text-muted">5%</small>
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
                    <i class="fas fa-calculator me-2"></i>Hasil Penilaian SOP
                </h6>
            </div>
            <div class="card-body text-center">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card border-left-primary">
                            <div class="card-body">
                                <h5 class="text-primary" id="prepScore">0%</h5>
                                <small>Persiapan SOP</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-success">
                            <div class="card-body">
                                <h5 class="text-success" id="execScore">0%</h5>
                                <small>Pelaksanaan SOP</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-warning">
                            <div class="card-body">
                                <h5 class="text-warning" id="evalScore">0%</h5>
                                <small>Evaluasi SOP</small>
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
                <i class="fas fa-save me-2"></i>Simpan Penilaian SOP
            </button>
        </div>
    </form>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let selectedPractical = null;
    const practicalSel = document.getElementById('practical_id');
    function show(id) { const el = document.getElementById(id); if (el) el.style.display = 'block'; }
    function hide(id) { const el = document.getElementById(id); if (el) el.style.display = 'none'; }
    function setText(id, val) { const el = document.getElementById(id); if (el) el.textContent = val; }

    if (practicalSel) {
        practicalSel.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (this.value) {
                selectedPractical = { id: this.value, title: opt.dataset.title, subject: opt.dataset.subject, maxScore: opt.dataset.maxScore };
                setText('practicalTitle',   selectedPractical.title);
                setText('practicalSubject', selectedPractical.subject);
                setText('practicalMaxScore', selectedPractical.maxScore);
                show('practicalInfo'); show('assessmentCriteria'); show('submitSection');
            } else {
                selectedPractical = null;
                hide('practicalInfo'); hide('assessmentCriteria'); hide('scoreDisplay'); hide('submitSection');
            }
        });
    }

    document.querySelectorAll('input[name^="kriteria_nilai"]').forEach(function (cb) {
        cb.addEventListener('change', calculateScore);
    });

    const weights = { 'prep_1':0.10,'prep_2':0.10,'prep_3':0.15,'exec_1':0.20,'exec_2':0.15,'exec_3':0.10,'eval_1':0.15,'eval_2':0.05 };

    function calculateScore() {
        if (!selectedPractical) return;
        let prep = 0, exec = 0, eval_ = 0, total = 0;
        Object.keys(weights).forEach(function (key) {
            const cb = document.querySelector('input[name="kriteria_nilai[' + key + ']"]');
            if (cb && cb.checked) {
                const w = weights[key] * 100;
                total += w;
                if (key.startsWith('prep')) prep  += w;
                if (key.startsWith('exec')) exec  += w;
                if (key.startsWith('eval')) eval_ += w;
            }
        });
        setText('prepScore',  Math.round(prep)  + '%');
        setText('execScore',  Math.round(exec)  + '%');
        setText('evalScore',  Math.round(eval_) + '%');
        setText('totalScore', total.toFixed(1));
        let grade = 'E';
        if      (total >= 90) grade = 'A';
        else if (total >= 80) grade = 'B';
        else if (total >= 70) grade = 'C';
        else if (total >= 60) grade = 'D';
        setText('gradeDisplay', grade);
        if (total > 0) show('scoreDisplay');
    }
});
</script>
@endpush
@endsection