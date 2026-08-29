@extends('layouts.guru')

@section('title', 'Penilaian Praktikum — SOP Checklist')
@section('page-title', 'Penilaian Praktikum')
@section('page-subtitle', 'Penilaian otomatis berdasarkan kriteria SOP yang ditetapkan admin.')

@section('page-actions')
    <a href="{{ route('guru.penilaian.index') }}" class="btn btn-outline-secondary btn-sm">
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
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>{{ $errors->count() }} kesalahan:</strong>
        <ul class="mb-0 mt-1 ps-3 small">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ═══ STEP 1: Pilih Praktikum & Siswa ═══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-white py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="fas fa-sliders-h me-2"></i>Langkah 1 — Pilih Praktikum & Siswa
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('guru.penilaian.nilai-kriteria') }}" id="selectorForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">
                        Praktikum <span class="text-danger">*</span>
                    </label>
                    <select name="practical_id" id="practicalSelect" class="form-select" required>
                        <option value="">— Pilih Praktikum —</option>
                        @foreach($practicals as $p)
                            <option value="{{ $p->id }}"
                                {{ $selectedPractical == $p->id ? 'selected' : '' }}>
                                {{ $p->title }}
                                @if($p->subject) ({{ $p->subject->name }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">
                        Siswa <span class="text-danger">*</span>
                    </label>
                    <select name="siswa_id" id="siswaSelect" class="form-select" required>
                        <option value="">— Pilih Siswa —</option>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}"
                                {{ $selectedSiswa == $s->id ? 'selected' : '' }}>
                                {{ $s->user?->name ?? "Siswa #$s->id" }}
                                @if($s->kelas) — {{ $s->kelas->name }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Muat Kriteria
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if($selectedPractical && $selectedSiswa)

@php
    $siswaObj   = $students->firstWhere('id', $selectedSiswa);
    $siswaName  = $siswaObj?->user?->name ?? "Siswa #$selectedSiswa";
    $kelasName  = $siswaObj?->kelas?->name ?? '—';
@endphp

{{-- Info praktikum & siswa --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning bg-opacity-10 p-3 flex-shrink-0">
                    <i class="fas fa-flask text-warning fa-lg"></i>
                </div>
                <div>
                    <div class="small text-muted">Praktikum</div>
                    <div class="fw-semibold">{{ $practical?->title ?? '—' }}</div>
                    <div class="small text-muted">{{ $practical?->subject?->name ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info bg-opacity-10 p-3 flex-shrink-0">
                    <i class="fas fa-user-graduate text-info fa-lg"></i>
                </div>
                <div>
                    <div class="small text-muted">Siswa</div>
                    <div class="fw-semibold">{{ $siswaName }}</div>
                    <div class="small text-muted">{{ $kelasName }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($kriteriaByCat->isEmpty())
    {{-- Tidak ada kriteria untuk mata praktik ini --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-clipboard-list fa-3x text-muted opacity-25 mb-3 d-block"></i>
            <h6 class="text-muted">Belum ada kriteria penilaian</h6>
            <p class="text-muted small mb-3">
                Admin belum menambahkan kriteria untuk mata praktik
                <strong>{{ $practical?->subject?->name ?? '—' }}</strong>.
            </p>
            <a href="{{ route('admin.kriteria-penilaian.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Tambah Kriteria (Admin)
            </a>
        </div>
    </div>
@else

{{-- ═══ STEP 2: Form Penilaian ═══ --}}
<form action="{{ route('guru.penilaian.nilai-kriteria.store') }}" method="POST" id="penilaianForm">
    @csrf
    <input type="hidden" name="practical_id" value="{{ $selectedPractical }}">
    <input type="hidden" name="siswa_id"     value="{{ $selectedSiswa }}">

    {{-- Live Score Panel --}}
    <div class="card border-0 shadow-sm border-start border-warning border-3 mb-4">
        <div class="card-body d-flex align-items-center justify-content-between gap-4 flex-wrap">
            <div>
                <div class="small text-muted fw-semibold">NILAI AKHIR (otomatis)</div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span class="display-5 fw-bold text-primary" id="liveScore">0</span>
                    <span class="text-muted">/ 100</span>
                    <span class="badge fs-6" id="liveGrade" style="min-width:40px;">—</span>
                </div>
                <div class="progress mt-2" style="height:8px;width:220px;">
                    <div class="progress-bar" id="liveBar" style="width:0%"></div>
                </div>
            </div>
            <div class="small text-muted text-end">
                <div>Nilai dihitung otomatis</div>
                <div>berdasarkan SOP yang dicentang</div>
                <div class="mt-1 text-dark fw-semibold" id="checkedCount">0 item dicentang</div>
            </div>
        </div>
    </div>

    {{-- Kriteria per Kategori --}}
    @php
        $katColors = [
            'persiapan'   => 'info',
            'pelaksanaan' => 'primary',
            'hasil'       => 'success',
            'sikap'       => 'warning',
        ];
        $katLabels = [
            'persiapan'   => 'Persiapan',
            'pelaksanaan' => 'Pelaksanaan',
            'hasil'       => 'Hasil',
            'sikap'       => 'Sikap Profesional',
        ];
    @endphp

    @foreach($kriteriaByCat as $kategori => $kriteriaList)
    @php
        $katColor = $katColors[$kategori] ?? 'secondary';
        $katLabel = $katLabels[$kategori] ?? ucfirst($kategori);
        $totalBobot = $kriteriaList->sum('weight');
    @endphp
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-{{ $katColor }} {{ $kategori === 'sikap' ? 'text-dark' : 'text-white' }} d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="fas fa-check-circle me-2"></i>{{ $katLabel }}
            </h6>
            <span class="badge bg-white {{ $kategori === 'sikap' ? 'text-dark' : 'text-' . $katColor }}">
                Bobot: {{ $totalBobot }}%
            </span>
        </div>
        <div class="card-body p-0">
            @foreach($kriteriaList as $ki => $kriteria)
            @php
                $sopList     = is_array($kriteria->sop_checklist) ? $kriteria->sop_checklist : [];
                // Nilai yang sudah ada
                $existing    = $existingScores->get($kriteria->id);
                $existingData = $existing
                    ? (is_string($existing->feedback) ? json_decode($existing->feedback, true) : [])
                    : [];
                $checkedSop  = $existingData['checked_sop'] ?? [];
            @endphp
            <div class="border-bottom p-4 kriteria-block" data-kriteria-id="{{ $kriteria->id }}"
                 data-weight="{{ $kriteria->weight }}" data-total-sop="{{ count($sopList) }}">

                {{-- Input hidden untuk kriteria id --}}
                <input type="hidden" name="kriteria[{{ $ki }}][id]" value="{{ $kriteria->id }}">

                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-semibold mb-1">{{ $kriteria->name }}</h6>
                        @if($kriteria->description)
                            <p class="text-muted small mb-0">{{ $kriteria->description }}</p>
                        @endif
                    </div>
                    <div class="text-end flex-shrink-0 ms-3">
                        <span class="badge bg-{{ $katColor }} bg-opacity-15 text-{{ $katColor }} fw-semibold px-2 py-1">
                            Bobot {{ $kriteria->weight }}%
                        </span>
                        <div class="small text-muted mt-1">
                            Nilai:
                            <strong class="text-primary kriteria-score" id="score-{{ $kriteria->id }}">
                                {{ $existing ? number_format((float)$existing->score, 1) : '0' }}
                            </strong>/100
                        </div>
                    </div>
                </div>

                {{-- SOP Checklist --}}
                @if(count($sopList))
                <div class="bg-light rounded-2 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-muted">SOP CHECKLIST</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-xs btn-outline-success btn-sm py-0 px-2 check-all-btn"
                                    data-kriteria="{{ $ki }}" onclick="checkAll({{ $ki }})">
                                <i class="fas fa-check-double me-1"></i>Semua
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary btn-sm py-0 px-2"
                                    onclick="uncheckAll({{ $ki }})">
                                <i class="fas fa-times me-1"></i>Reset
                            </button>
                        </div>
                    </div>
                    <div class="row g-2">
                        @foreach($sopList as $si => $sopItem)
                        <div class="col-12">
                            <div class="form-check d-flex align-items-start gap-2 p-2 rounded-2 sop-item
                                        {{ in_array($si, array_map('intval', $checkedSop)) ? 'bg-success bg-opacity-10' : 'bg-white' }}"
                                 id="sop-wrap-{{ $ki }}-{{ $si }}">
                                <input class="form-check-input mt-1 sop-checkbox flex-shrink-0"
                                       type="checkbox"
                                       name="kriteria[{{ $ki }}][checklist][]"
                                       value="{{ $si }}"
                                       id="sop-{{ $ki }}-{{ $si }}"
                                       data-kriteria-idx="{{ $ki }}"
                                       data-sop-wrap="sop-wrap-{{ $ki }}-{{ $si }}"
                                       {{ in_array($si, array_map('intval', $checkedSop)) ? 'checked' : '' }}
                                       onchange="updateScore(this)">
                                <label class="form-check-label small cursor-pointer flex-grow-1"
                                       for="sop-{{ $ki }}-{{ $si }}">
                                    <span class="fw-medium">{{ $si + 1 }}.</span> {{ $sopItem }}
                                </label>
                                <span class="badge rounded-pill {{ in_array($si, array_map('intval', $checkedSop)) ? 'bg-success' : 'bg-light text-muted' }}
                                            flex-shrink-0" id="badge-{{ $ki }}-{{ $si }}">
                                    <i class="fas fa-{{ in_array($si, array_map('intval', $checkedSop)) ? 'check' : 'minus' }}"></i>
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    {{-- Progress bar checklist --}}
                    <div class="mt-2">
                        @php $checkedCount = count($checkedSop); @endphp
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span class="checked-label-{{ $ki }}">{{ $checkedCount }}/{{ count($sopList) }} item</span>
                            <span class="checked-pct-{{ $ki }}">{{ count($sopList) > 0 ? round($checkedCount/count($sopList)*100) : 0 }}%</span>
                        </div>
                        <div class="progress" style="height:5px;">
                            <div class="progress-bar bg-{{ $katColor }} checklist-progress-{{ $ki }}"
                                 style="width:{{ count($sopList) > 0 ? round($checkedCount/count($sopList)*100) : 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
                @else
                    <div class="text-muted small fst-italic">Tidak ada SOP checklist untuk kriteria ini.</div>
                @endif

            </div>{{-- /kriteria-block --}}
            @endforeach
        </div>
    </div>
    @endforeach

    {{-- Catatan & Submit --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <label class="form-label fw-semibold">Catatan / Umpan Balik</label>
            <textarea name="feedback" rows="3" class="form-control"
                      placeholder="Catatan tambahan untuk siswa (opsional)..."></textarea>
        </div>
    </div>

    <div class="d-flex gap-3 align-items-center">
        <button type="submit" class="btn btn-success btn-lg fw-semibold px-4" id="submitBtn">
            <i class="fas fa-save me-2"></i>Simpan Penilaian
        </button>
        <div class="text-muted small">
            Nilai akhir: <strong class="text-primary" id="submitScore">0</strong> /100
            &nbsp;·&nbsp; Grade: <strong id="submitGrade">—</strong>
        </div>
    </div>

</form>
@endif

@endif {{-- end if selectedPractical && selectedSiswa --}}

@push('css')
<style>
.cursor-pointer { cursor: pointer; }
.sop-item { transition: background 0.15s; border: 1px solid #e9ecef; }
.sop-item:hover { background: #f8f9fa !important; border-color: #dee2e6; }
.form-check-input { cursor: pointer; width: 1.15em; height: 1.15em; }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Data kriteria dari DOM ─────────────────────────────────────────────
    const kriteriaBlocks = document.querySelectorAll('.kriteria-block');

    // Precompute per-kriteria data
    const kriteriaMap = {}; // idx → { weight, totalSop, checkboxes }
    kriteriaBlocks.forEach(block => {
        const idx       = null; // detect by checkbox group
    });

    // ── Update score on checkbox change ───────────────────────────────────
    window.updateScore = function (checkbox) {
        const wrap   = document.getElementById(checkbox.dataset.sopWrap);
        const badge  = document.getElementById('badge-' + checkbox.id.replace('sop-',''));

        // Toggle highlight
        if (checkbox.checked) {
            wrap.classList.remove('bg-white');
            wrap.classList.add('bg-success', 'bg-opacity-10');
            badge.className = 'badge rounded-pill bg-success flex-shrink-0';
            badge.innerHTML = '<i class="fas fa-check"></i>';
        } else {
            wrap.classList.remove('bg-success', 'bg-opacity-10');
            wrap.classList.add('bg-white');
            badge.className = 'badge rounded-pill bg-light text-muted flex-shrink-0';
            badge.innerHTML = '<i class="fas fa-minus"></i>';
        }

        recalcAll();
    };

    // ── Check/uncheck all SOP in a kriteria ───────────────────────────────
    window.checkAll = function (ki) {
        document.querySelectorAll(`[data-kriteria-idx="${ki}"]`).forEach(cb => {
            cb.checked = true;
            updateScore(cb);
        });
    };

    window.uncheckAll = function (ki) {
        document.querySelectorAll(`[data-kriteria-idx="${ki}"]`).forEach(cb => {
            cb.checked = false;
            updateScore(cb);
        });
    };

    // ── Recalculate all scores ─────────────────────────────────────────────
    function recalcAll() {
        let finalScore  = 0;
        let totalChecked = 0;

        kriteriaBlocks.forEach(block => {
            const kriteriaId = block.dataset.kriteriaId;
            const weight     = parseFloat(block.dataset.weight) || 0;
            const totalSop   = parseInt(block.dataset.totalSop) || 0;

            // Find ki (kriteria array index) from hidden input
            const hiddenInput = block.querySelector('input[name^="kriteria"][name$="[id]"]');
            if (!hiddenInput) return;
            const match = hiddenInput.name.match(/kriteria\[(\d+)\]/);
            if (!match) return;
            const ki = match[1];

            const checkboxes = block.querySelectorAll('.sop-checkbox');
            let checked      = 0;
            checkboxes.forEach(cb => { if (cb.checked) checked++; });
            totalChecked += checked;

            // Nilai kriteria ini
            const nilaiKriteria = totalSop > 0 ? (checked / totalSop) * 100 : 0;
            const contribution  = (nilaiKriteria * weight / 100);
            finalScore += contribution;

            // Update per-kriteria score display
            const scoreEl = document.getElementById(`score-${kriteriaId}`);
            if (scoreEl) scoreEl.textContent = nilaiKriteria.toFixed(1);

            // Update progress bar
            const progressBar = block.querySelector(`.checklist-progress-${ki}`);
            const labelEl     = block.querySelector(`.checked-label-${ki}`);
            const pctEl       = block.querySelector(`.checked-pct-${ki}`);
            if (progressBar) progressBar.style.width = (totalSop > 0 ? (checked/totalSop)*100 : 0) + '%';
            if (labelEl)     labelEl.textContent = `${checked}/${totalSop} item`;
            if (pctEl)       pctEl.textContent   = (totalSop > 0 ? Math.round(checked/totalSop*100) : 0) + '%';
        });

        finalScore = Math.round(finalScore * 10) / 10;

        // Update live panel
        const liveScore = document.getElementById('liveScore');
        const liveGrade = document.getElementById('liveGrade');
        const liveBar   = document.getElementById('liveBar');
        const countEl   = document.getElementById('checkedCount');
        const submitScore = document.getElementById('submitScore');
        const submitGrade = document.getElementById('submitGrade');

        if (liveScore)   liveScore.textContent   = finalScore.toFixed(1);
        if (submitScore) submitScore.textContent  = finalScore.toFixed(1);
        if (countEl)     countEl.textContent      = `${totalChecked} item dicentang`;

        const grade = getGrade(finalScore);
        const color = getGradeColor(grade);

        if (liveGrade) { liveGrade.textContent = grade; liveGrade.className = `badge fs-6 bg-${color}`; }
        if (submitGrade) submitGrade.textContent = grade;
        if (liveBar) { liveBar.style.width = Math.min(finalScore, 100) + '%'; liveBar.className = `progress-bar bg-${color}`; }
    }

    function getGrade(score) {
        if (score >= 90) return 'A';
        if (score >= 80) return 'B';
        if (score >= 70) return 'C';
        if (score >= 60) return 'D';
        return 'E';
    }

    function getGradeColor(grade) {
        return { A: 'success', B: 'primary', C: 'info', D: 'warning', E: 'danger' }[grade] ?? 'secondary';
    }

    // Initial calculation (from existing values)
    recalcAll();

    // ── Submit spinner ─────────────────────────────────────────────────────
    const form      = document.getElementById('penilaianForm');
    const submitBtn = document.getElementById('submitBtn');
    if (form && submitBtn) {
        form.addEventListener('submit', function () {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
        });
    }
    window.addEventListener('pageshow', function (e) {
        if (e.persisted && submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Simpan Penilaian';
        }
    });

    // ── Auto-submit selector ───────────────────────────────────────────────
    document.getElementById('practicalSelect')?.addEventListener('change', function () {
        if (this.value && document.getElementById('siswaSelect')?.value) {
            document.getElementById('selectorForm').submit();
        }
    });
    document.getElementById('siswaSelect')?.addEventListener('change', function () {
        if (this.value && document.getElementById('practicalSelect')?.value) {
            document.getElementById('selectorForm').submit();
        }
    });
});
</script>
@endpush

@endsection
