@extends('layouts.guru')

@section('title', 'Buat Penilaian')
@section('page-title', 'Buat Penilaian')
@section('page-subtitle', 'Berikan nilai untuk tugas atau praktikum siswa.')

@section('page-actions')
    <a href="{{ route('guru.penilaian.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>{{ $errors->count() }} kesalahan:</strong>
        <ul class="mb-0 mt-1 ps-3 small">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning text-dark d-flex align-items-center gap-2">
                <i class="fas fa-star"></i>
                <h6 class="mb-0 fw-semibold">Form Penilaian</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('guru.penilaian.store') }}" id="penilaianForm">
                    @csrf

                    {{-- Tipe Penilaian --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Tipe Penilaian <span class="text-danger">*</span></label>
                        <select class="form-select @error('assessment_type') is-invalid @enderror"
                                id="assessment_type" name="assessment_type" required
                                onchange="toggleAssessmentType()">
                            <option value="">— Pilih Tipe Penilaian —</option>
                            <option value="assignment" {{ old('assessment_type') == 'assignment' ? 'selected' : '' }}>
                                <i class="fas fa-tasks"></i> Tugas
                            </option>
                            <option value="practical" {{ old('assessment_type') == 'practical' ? 'selected' : '' }}>
                                <i class="fas fa-flask"></i> Praktikum
                            </option>
                        </select>
                        @error('assessment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Field Tugas --}}
                    <div id="assignment-fields" style="display: none;">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Pilih Tugas <span class="text-danger">*</span></label>
                                <select class="form-select @error('assignment_id') is-invalid @enderror"
                                        id="assignment_id" name="assignment_id">
                                    <option value="">— Pilih Tugas —</option>
                                    @foreach($assignments as $assignment)
                                        <option value="{{ $assignment->id }}" {{ old('assignment_id') == $assignment->id ? 'selected' : '' }}>
                                            {{ $assignment->title }} — {{ $assignment->subject?->name ?? '—' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assignment_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Pilih Siswa <span class="text-danger">*</span></label>
                                <select class="form-select @error('siswa_id') is-invalid @enderror"
                                        id="siswa_id" name="siswa_id">
                                    <option value="">— Pilih Siswa —</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ old('siswa_id') == $student->id ? 'selected' : '' }}>
                                            {{ $student->user?->name ?? "Siswa #$student->id" }}
                                            @if($student->nis) ({{ $student->nis }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('siswa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Field Praktikum --}}
                    <div id="practical-fields" style="display: none;">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Pilih Praktikum <span class="text-danger">*</span></label>
                                <select class="form-select @error('practical_id') is-invalid @enderror"
                                        id="practical_id" name="practical_id">
                                    <option value="">— Pilih Praktikum —</option>
                                    @foreach($practicals as $practical)
                                        <option value="{{ $practical->id }}" {{ old('practical_id') == $practical->id ? 'selected' : '' }}>
                                            {{ $practical->title }} — {{ $practical->subject?->name ?? '—' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('practical_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Pilih Siswa <span class="text-danger">*</span></label>
                                <select class="form-select"
                                        id="siswa_id_practical" name="siswa_id_practical">
                                    <option value="">— Pilih Siswa —</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}">
                                            {{ $student->user?->name ?? "Siswa #$student->id" }}
                                            @if($student->nis) ({{ $student->nis }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Nilai & Grade --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nilai <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('score') is-invalid @enderror"
                                   id="score" name="score"
                                   value="{{ old('score') }}"
                                   min="0" max="100" step="0.5" required placeholder="0–100">
                            @error('score')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Grade</label>
                            <select class="form-select @error('grade') is-invalid @enderror"
                                    id="grade" name="grade">
                                <option value="">— Otomatis —</option>
                                <option value="A">A (85–100)</option>
                                <option value="B">B (70–84)</option>
                                <option value="C">C (55–69)</option>
                                <option value="D">D (40–54)</option>
                                <option value="E">E (0–39)</option>
                            </select>
                            @error('grade')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                <option value="draft" {{ old('status','draft') == 'draft' ? 'selected':'' }}>Draft</option>
                                <option value="final" {{ old('status') == 'final' ? 'selected':'' }}>Final</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Feedback --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Feedback / Catatan</label>
                        <textarea class="form-control @error('feedback') is-invalid @enderror"
                                  id="feedback" name="feedback" rows="4"
                                  placeholder="Berikan umpan balik konstruktif untuk siswa...">{{ old('feedback') }}</textarea>
                        @error('feedback')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('guru.penilaian.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Batal
                        </a>
                        <div class="d-flex gap-2">
                            <button type="reset" class="btn btn-outline-warning">
                                <i class="fas fa-redo me-1"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-warning" id="submitBtn">
                                <i class="fas fa-save me-1"></i>Simpan Penilaian
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Panduan Grade --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2 text-info"></i>Panduan Grade</h6>
            </div>
            <div class="card-body py-2">
                @foreach([
                    ['success', 'A', '85 – 100', 'Sangat Baik'],
                    ['primary', 'B', '70 – 84',  'Baik'],
                    ['warning', 'C', '55 – 69',  'Cukup'],
                    ['danger',  'D', '40 – 54',  'Kurang'],
                    ['secondary','E','0 – 39',   'Sangat Kurang'],
                ] as [$c, $g, $r, $l])
                <div class="d-flex align-items-center justify-content-between py-2 border-bottom small">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-{{ $c }}" style="width:28px;">{{ $g }}</span>
                        <span class="text-muted">{{ $l }}</span>
                    </div>
                    <span class="fw-semibold text-dark">{{ $r }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-lightbulb me-2 text-warning"></i>Tips</h6>
            </div>
            <div class="card-body small text-muted">
                <ul class="ps-3 mb-0">
                    <li class="mb-2">Grade akan otomatis terisi saat nilai dimasukkan</li>
                    <li class="mb-2">Status <strong>Draft</strong> — nilai belum resmi</li>
                    <li class="mb-2">Status <strong>Final</strong> — nilai sudah resmi</li>
                    <li>Feedback membantu siswa memahami kekurangannya</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
function toggleAssessmentType() {
    const type     = document.getElementById('assessment_type').value;
    const aFields  = document.getElementById('assignment-fields');
    const pFields  = document.getElementById('practical-fields');
    const siswaA   = document.getElementById('siswa_id');
    const siswaP   = document.getElementById('siswa_id_practical');
    const assId    = document.getElementById('assignment_id');
    const practId  = document.getElementById('practical_id');

    if (type === 'assignment') {
        aFields.style.display = 'block'; pFields.style.display = 'none';
        siswaA.required = true;  assId.required  = true;
        siswaP.required = false; practId.required = false;
    } else if (type === 'practical') {
        aFields.style.display = 'none'; pFields.style.display = 'block';
        siswaP.required = true;  practId.required = true;
        siswaA.required = false; assId.required   = false;
    } else {
        aFields.style.display = 'none'; pFields.style.display = 'none';
        siswaA.required = false; assId.required   = false;
        siswaP.required = false; practId.required = false;
    }
}

// Auto-calculate grade
document.getElementById('score').addEventListener('input', function () {
    const s = parseFloat(this.value);
    const g = document.getElementById('grade');
    if      (s >= 85) g.value = 'A';
    else if (s >= 70) g.value = 'B';
    else if (s >= 55) g.value = 'C';
    else if (s >= 40) g.value = 'D';
    else if (s >= 0)  g.value = 'E';
    else              g.value = '';
});

// Sync siswa between tugas/praktikum
document.getElementById('siswa_id').addEventListener('change', function () {
    document.getElementById('siswa_id_practical').value = this.value;
});
document.getElementById('siswa_id_practical').addEventListener('change', function () {
    document.getElementById('siswa_id').value = this.value;
});

// Restore type selection on page reload (old input)
document.addEventListener('DOMContentLoaded', function () {
    const typeEl = document.getElementById('assessment_type');
    if (typeEl.value) toggleAssessmentType();

    document.getElementById('penilaianForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
    });
});
</script>
@endpush

@endsection