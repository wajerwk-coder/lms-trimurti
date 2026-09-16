@extends('layouts.guru')

@section('title', 'Penilaian Praktikum — SOP Checklist')
@section('page-title', 'Penilaian Praktikum')
@section('page-subtitle', 'Pilih praktikum — semua siswa di kelas langsung muncul untuk dinilai.')

@section('page-actions')
    <a href="{{ route('guru.penilaian.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@push('css')
<style>
.siswa-tab-btn         { cursor:pointer; transition:.15s; }
.siswa-tab-btn.active  { background:#4f46e5 !important; color:#fff !important;
                         border-color:#4f46e5 !important; }
.sop-item              { border:1px solid #e9ecef; transition:background .12s; }
.sop-item:hover        { background:#f8f9fa !important; border-color:#dee2e6; }
.sop-item.checked-item { background:rgba(34,197,94,.08) !important;
                         border-color:rgba(34,197,94,.3) !important; }
.score-badge           { min-width:52px; text-align:center; font-size:.85rem; }
.tab-pane-siswa        { display:none; }
.tab-pane-siswa.active { display:block; }
.sticky-score-bar      { position:sticky; top:64px; z-index:10;
                         background:#fff; border-bottom:1px solid #e9ecef; }
</style>
@endpush

@section('content')

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

{{-- ═══ Step 1: Pilih Praktikum ═══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-white py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="fas fa-flask me-2"></i>Langkah 1 — Pilih Praktikum
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('guru.penilaian.nilai-kriteria') }}" id="selectorForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-9">
                    <label class="form-label fw-semibold">
                        Praktikum <span class="text-danger">*</span>
                    </label>
                    <select name="practical_id" id="practicalSelect" class="form-select" required
                            onchange="this.form.submit()">
                        <option value="">— Pilih Praktikum —</option>
                        @foreach($practicals as $p)
                            <option value="{{ $p->id }}"
                                {{ $selectedPractical == $p->id ? 'selected' : '' }}>
                                {{ $p->title }}
                                @if($p->subject) · {{ $p->subject->name }} @endif
                                @if($p->kelas)   · {{ $p->kelas->name }}   @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-users me-1"></i>Muat Siswa
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if($selectedPractical && $practical)

{{-- Info praktikum --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex align-items-center gap-4 flex-wrap">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-warning bg-opacity-10 p-3 flex-shrink-0">
                <i class="fas fa-flask text-warning fa-lg"></i>
            </div>
            <div>
                <div class="small text-muted">Praktikum</div>
                <div class="fw-bold">{{ $practical->title }}</div>
                <div class="small text-muted">{{ $practical->subject?->name ?? '—' }}</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-info bg-opacity-10 p-3 flex-shrink-0">
                <i class="fas fa-school text-info fa-lg"></i>
            </div>
            <div>
                <div class="small text-muted">Kelas</div>
                <div class="fw-bold">{{ $practical->kelas?->name ?? '—' }}</div>
                <div class="small text-muted">{{ $siswaList->count() }} siswa</div>
            </div>
        </div>
        @if($kriteriaByCat->isNotEmpty())
        <div class="ms-auto">
            <span class="badge bg-success px-3 py-2">
                <i class="fas fa-check-circle me-1"></i>
                {{ $kriteriaByCat->flatten()->count() }} kriteria tersedia
            </span>
        </div>
        @endif
    </div>
</div>

@if($siswaList->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-users-slash fa-3x text-muted opacity-25 mb-3 d-block"></i>
            <h6 class="text-muted">Tidak ada siswa di kelas ini</h6>
            <p class="text-muted small">Tambahkan siswa ke kelas {{ $practical->kelas?->name }} terlebih dahulu.</p>
        </div>
    </div>

@elseif($kriteriaByCat->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-clipboard-list fa-3x text-muted opacity-25 mb-3 d-block"></i>
            <h6 class="text-muted">Belum ada kriteria penilaian</h6>
            <p class="text-muted small mb-3">
                Admin belum menambahkan kriteria untuk mata praktik
                <strong>{{ $practical->subject?->name ?? '—' }}</strong>.
            </p>
        </div>
    </div>

@else

{{-- ═══ Step 2: Form Penilaian Semua Siswa ═══ --}}
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
    // Flatten semua kriteria untuk JS
    $allKriteria = $kriteriaByCat->flatten()->values();
@endphp

<form action="{{ route('guru.penilaian.nilai-kriteria.store') }}" method="POST" id="penilaianForm">
    @csrf
    <input type="hidden" name="practical_id" value="{{ $selectedPractical }}">

    {{-- Input siswa_ids[] — semua siswa --}}
    @foreach($siswaList as $s)
        <input type="hidden" name="siswa_ids[]" value="{{ $s->id }}">
    @endforeach

    {{-- $globalKi: counter index kriteria GLOBAL (lintas kategori) agar tidak ada
         tabrakan nama field form. Kriteria [0..N-1] konsisten untuk semua siswa. --}}
    @php
        $globalKi = 0;
        // Buat flat list semua kriteria dengan index global yang konsisten
        // sehingga kriteria[0..N] sama untuk setiap siswa
        $flatKriteriaList = $kriteriaByCat->flatten()->values(); // Collection flat, indexed 0..N-1
    @endphp

    {{-- Tab navigasi siswa --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-users me-2 text-primary"></i>
                    Daftar Siswa ({{ $siswaList->count() }} siswa)
                </h6>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <span class="badge bg-secondary" id="globalProgress">0/{{ $siswaList->count() }} selesai</span>
                    <button type="button" class="btn btn-outline-success btn-sm" id="selectAllStudentsBtn">
                        <i class="fas fa-check-double me-1"></i>Centang Semua SOP (Siswa Aktif)
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body py-2 px-3">
            {{-- Tab buttons siswa --}}
            <div class="d-flex flex-wrap gap-2" id="siswaTabs">
                @foreach($siswaList as $idx => $s)
                @php
                    // Cek apakah sudah ada nilai ringkasan (criteria_id = null)
                    $keyNull     = $s->user_id . '_null';
                    $sudahDinilai= isset($existingNilai[$keyNull]) && $existingNilai[$keyNull]->isNotEmpty();
                @endphp
                <button type="button"
                        class="btn btn-outline-secondary btn-sm siswa-tab-btn {{ $idx === 0 ? 'active' : '' }}"
                        data-siswa-id="{{ $s->id }}"
                        data-idx="{{ $idx }}"
                        id="tab-btn-{{ $s->id }}"
                        onclick="switchSiswa({{ $s->id }}, {{ $idx }})">
                    @if($sudahDinilai)
                        <i class="fas fa-check-circle text-success me-1"></i>
                    @endif
                    {{ $s->user?->name ?? "Siswa #$s->id" }}
                    @if($s->nis) <small class="opacity-75">({{ $s->nis }})</small> @endif
                    <span class="badge bg-light text-dark ms-1 score-chip" id="chip-{{ $s->id }}">—</span>
                </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Panel per siswa --}}
    @foreach($siswaList as $idx => $s)
    @php
        $ucId     = $s->user_id;
        $globalKi = 0; // reset per siswa agar index kriteria konsisten untuk semua siswa
    @endphp
    <div class="tab-pane-siswa {{ $idx === 0 ? 'active' : '' }}" id="pane-{{ $s->id }}">

        {{-- Sticky score bar untuk siswa ini --}}
        <div class="sticky-score-bar px-3 py-2 mb-3 rounded-2 shadow-sm">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center
                                justify-content-center flex-shrink-0" style="width:40px;height:40px;">
                        <i class="fas fa-user-graduate text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $s->user?->name ?? "Siswa #$s->id" }}</div>
                        <div class="small text-muted">{{ $s->nis ?? '—' }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-4">
                    <div class="text-center">
                        <div class="small text-muted">Nilai Akhir</div>
                        <div class="fw-bold text-primary fs-5" id="live-score-{{ $s->id }}">0</div>
                    </div>
                    <div class="text-center">
                        <div class="small text-muted">Grade</div>
                        <span class="badge fs-6" id="live-grade-{{ $s->id }}">—</span>
                    </div>
                    <div class="text-center">
                        <div class="small text-muted">Item ✓</div>
                        <div class="fw-semibold" id="live-checked-{{ $s->id }}">0</div>
                    </div>
                </div>
            </div>
            <div class="progress mt-2" style="height:5px;">
                <div class="progress-bar" id="live-bar-{{ $s->id }}" style="width:0%"></div>
            </div>
        </div>

        {{-- Kriteria per kategori --}}
        @foreach($kriteriaByCat as $kategori => $kriteriaList)
        @php
            $katColor = $katColors[$kategori] ?? 'secondary';
            $katLabel = $katLabels[$kategori] ?? ucfirst($kategori);
        @endphp
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-{{ $katColor }} {{ $kategori === 'sikap' ? 'text-dark' : 'text-white' }}
                        d-flex justify-content-between align-items-center py-2">
                <span class="fw-semibold small">
                    <i class="fas fa-check-circle me-2"></i>{{ $katLabel }}
                </span>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-white {{ $kategori === 'sikap' ? 'text-dark' : 'text-' . $katColor }}">
                        Bobot {{ $kriteriaList->sum('weight') }}%
                    </span>
                    <button type="button"
                            class="btn btn-sm py-0 px-2 bg-white {{ $kategori === 'sikap' ? 'text-dark' : 'text-' . $katColor }}"
                            onclick="checkAllKategori({{ $s->id }}, '{{ $kategori }}')">
                        <i class="fas fa-check-double"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                @foreach($kriteriaList as $kriteria)
                @php
                    $ki          = $globalKi; // pakai counter global agar unik lintas kategori
                    $sopList     = is_array($kriteria->sop_checklist) ? $kriteria->sop_checklist : [];
                    $keyExist    = $ucId . '_' . $kriteria->id;
                    $existRec    = $existingNilai[$keyExist] ?? collect();
                    $existFb     = $existRec->isNotEmpty() ? json_decode($existRec->first()->feedback, true) : [];
                    $checkedSop  = $existFb['checked_sop'] ?? [];
                    // Indeks unik untuk SOP dalam form: kombinasi siswa_id + kriteria_indeks
                    $formKi      = $s->id . '_' . $ki;
                    $globalKi++;   // increment SETELAH assign agar semua siswa dapat index yang sama
                @endphp
                <div class="border-bottom p-3 kriteria-block"
                     data-siswa="{{ $s->id }}"
                     data-kategori="{{ $kategori }}"
                     data-kriteria-id="{{ $kriteria->id }}"
                     data-weight="{{ $kriteria->weight }}"
                     data-total-sop="{{ count($sopList) }}">

                    <input type="hidden" name="kriteria[{{ $ki }}][id]" value="{{ $kriteria->id }}">

                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="small fw-semibold">{{ $kriteria->name }}</div>
                        <span class="badge bg-{{ $katColor }} bg-opacity-10 text-{{ $katColor }} flex-shrink-0 ms-2">
                            {{ $kriteria->weight }}%
                        </span>
                    </div>

                    @if(count($sopList))
                    <div class="bg-light rounded-2 p-2">
                        <div class="row g-1">
                            @foreach($sopList as $si => $sopItem)
                            <div class="col-12 col-md-6">
                                <div class="sop-item form-check d-flex align-items-start gap-2 p-2 rounded-2
                                            {{ in_array($si, array_map('intval', $checkedSop)) ? 'checked-item' : '' }}"
                                     id="wrap-{{ $s->id }}-{{ $ki }}-{{ $si }}">
                                    <input type="checkbox"
                                           class="form-check-input flex-shrink-0 mt-1 sop-cb"
                                           name="kriteria[{{ $ki }}][checklist][{{ $s->id }}][]"
                                           value="{{ $si }}"
                                           id="cb-{{ $s->id }}-{{ $ki }}-{{ $si }}"
                                           data-siswa="{{ $s->id }}"
                                           data-ki="{{ $ki }}"
                                           data-wrap="wrap-{{ $s->id }}-{{ $ki }}-{{ $si }}"
                                           {{ in_array($si, array_map('intval', $checkedSop)) ? 'checked' : '' }}
                                           onchange="onCbChange(this)">
                                    <label class="form-check-label small flex-grow-1"
                                           for="cb-{{ $s->id }}-{{ $ki }}-{{ $si }}"
                                           style="cursor:pointer;">
                                        <span class="fw-medium text-muted">{{ $si + 1 }}.</span> {{ $sopItem }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        {{-- Progress checklist per kriteria --}}
                        <div class="d-flex justify-content-between small text-muted mt-1 mb-0">
                            <span id="prog-lbl-{{ $s->id }}-{{ $ki }}">
                                {{ count($checkedSop) }}/{{ count($sopList) }} item
                            </span>
                            <span id="prog-pct-{{ $s->id }}-{{ $ki }}">
                                {{ count($sopList) > 0 ? round(count($checkedSop)/count($sopList)*100) : 0 }}%
                            </span>
                        </div>
                        <div class="progress" style="height:4px;">
                            <div class="progress-bar bg-{{ $katColor }} prog-bar-kriteria"
                                 id="prog-bar-{{ $s->id }}-{{ $ki }}"
                                 style="width:{{ count($sopList) > 0 ? round(count($checkedSop)/count($sopList)*100) : 0 }}%">
                            </div>
                        </div>
                    </div>
                    @else
                        <div class="text-muted small fst-italic">Tidak ada SOP checklist.</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        {{-- Catatan per siswa --}}
        <div class="mb-4">
            <label class="form-label fw-semibold small">
                Catatan untuk {{ $s->user?->name ?? "Siswa #$s->id" }}
            </label>
            <textarea name="feedback[{{ $s->id }}]" rows="2" class="form-control form-control-sm"
                      placeholder="Catatan / umpan balik (opsional)..."></textarea>
        </div>

    </div>
    @endforeach

    {{-- Submit --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div class="text-muted small">
                Simpan penilaian untuk <strong>{{ $siswaList->count() }} siswa</strong>
                sekaligus.
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('guru.penilaian.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
                <button type="submit" class="btn btn-success px-4 fw-semibold" id="submitBtn">
                    <i class="fas fa-save me-2"></i>Simpan Semua Penilaian
                </button>
            </div>
        </div>
    </div>

</form>
@endif {{-- end else (has siswa & kriteria) --}}
@endif {{-- end if selectedPractical --}}

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Switch tab siswa ─────────────────────────────────────────────────
    window.switchSiswa = function (siswaId, idx) {
        document.querySelectorAll('.tab-pane-siswa').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.siswa-tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('pane-' + siswaId)?.classList.add('active');
        document.getElementById('tab-btn-' + siswaId)?.classList.add('active');
    };

    // ── Check all SOP dalam satu kategori untuk siswa aktif ──────────────
    window.checkAllKategori = function (siswaId, kategori) {
        document.querySelectorAll(
            `#pane-${siswaId} .kriteria-block[data-kategori="${kategori}"] .sop-cb`
        ).forEach(cb => { cb.checked = true; onCbChange(cb); });
    };

    // ── Select all SOP untuk siswa aktif (semua kategori) ────────────────
    document.getElementById('selectAllStudentsBtn')?.addEventListener('click', function () {
        const activePane = document.querySelector('.tab-pane-siswa.active');
        if (!activePane) return;
        activePane.querySelectorAll('.sop-cb').forEach(cb => {
            cb.checked = true;
            onCbChange(cb);
        });
    });

    // ── Callback checkbox change ─────────────────────────────────────────
    window.onCbChange = function (cb) {
        const wrap = document.getElementById(cb.dataset.wrap);
        if (wrap) {
            wrap.classList.toggle('checked-item', cb.checked);
        }
        recalcSiswa(cb.dataset.siswa);
    };

    // ── Recalculate score untuk satu siswa ──────────────────────────────
    window.recalcSiswa = function (siswaId) {
        const pane = document.getElementById('pane-' + siswaId);
        if (!pane) return;

        const blocks = pane.querySelectorAll('.kriteria-block');
        let totalWeighted = 0, totalBobot = 0, totalChecked = 0;

        blocks.forEach(block => {
            const ki       = block.querySelector('input[name^="kriteria"]')?.name.match(/kriteria\[(\S+?)\]/)?.[1];
            const weight   = parseFloat(block.dataset.weight) || 0;
            const totalSop = parseInt(block.dataset.totalSop) || 0;
            const cbs      = block.querySelectorAll('.sop-cb');
            let checked    = 0;
            cbs.forEach(c => { if (c.checked) checked++; });
            totalChecked += checked;

            if (totalSop > 0) {
                // Kriteria punya SOP → hitung dari checklist, masukkan ke bobot
                const nilaiKriteria = Math.min(100, (checked / totalSop) * 100);
                totalBobot    += weight;
                totalWeighted += nilaiKriteria * weight;
            }
            // totalSop === 0 → skip dari perhitungan (jangan fallback ke 100)

            // Update progress per kriteria
            if (ki !== undefined) {
                const lbl = document.getElementById(`prog-lbl-${siswaId}-${ki}`);
                const pct = document.getElementById(`prog-pct-${siswaId}-${ki}`);
                const bar = document.getElementById(`prog-bar-${siswaId}-${ki}`);
                if (lbl) lbl.textContent = `${checked}/${totalSop} item`;
                if (pct) pct.textContent = `${totalSop > 0 ? Math.round(checked/totalSop*100) : 0}%`;
                if (bar) bar.style.width = `${totalSop > 0 ? checked/totalSop*100 : 0}%`;
            }
        });

        const divisor    = totalBobot > 0 ? totalBobot : 100;
        const finalScore = Math.min(100, Math.round((totalWeighted / divisor) * 10) / 10);
        const grade      = getGrade(finalScore);
        const color      = gradeColor(grade);

        // Update sticky bar
        const scoreEl = document.getElementById(`live-score-${siswaId}`);
        const gradeEl = document.getElementById(`live-grade-${siswaId}`);
        const barEl   = document.getElementById(`live-bar-${siswaId}`);
        const chkEl   = document.getElementById(`live-checked-${siswaId}`);
        if (scoreEl)  scoreEl.textContent = finalScore.toFixed(1);
        if (gradeEl)  { gradeEl.textContent = grade; gradeEl.className = `badge fs-6 bg-${color}`; }
        if (barEl)    { barEl.style.width = finalScore + '%'; barEl.className = `progress-bar bg-${color}`; }
        if (chkEl)    chkEl.textContent = totalChecked;

        // Update chip di tab button
        const chip = document.getElementById(`chip-${siswaId}`);
        if (chip) {
            chip.textContent = finalScore.toFixed(0);
            chip.className   = `badge ms-1 bg-${color} score-chip`;
        }

        updateGlobalProgress();
    };

    function updateGlobalProgress() {
        const total   = document.querySelectorAll('.tab-pane-siswa').length;
        let   selesai = 0;
        document.querySelectorAll('[id^="live-score-"]').forEach(el => {
            if (parseFloat(el.textContent) > 0) selesai++;
        });
        const badge = document.getElementById('globalProgress');
        if (badge) badge.textContent = `${selesai}/${total} selesai`;
    }

    function getGrade(s) {
        if (s >= 90) return 'A';
        if (s >= 80) return 'B';
        if (s >= 70) return 'C';
        if (s >= 60) return 'D';
        return 'E';
    }
    function gradeColor(g) {
        return { A:'success', B:'primary', C:'info', D:'warning', E:'danger' }[g] ?? 'secondary';
    }

    // ── Init: kalkulasi dari nilai existing ──────────────────────────────
    document.querySelectorAll('.tab-pane-siswa').forEach(pane => {
        const siswaId = pane.id.replace('pane-', '');
        recalcSiswa(siswaId);
    });

    // ── Submit spinner ────────────────────────────────────────────────────
    document.getElementById('penilaianForm')?.addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
        }
    });
    window.addEventListener('pageshow', e => {
        if (!e.persisted) return;
        const btn = document.getElementById('submitBtn');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save me-2"></i>Simpan Semua Penilaian'; }
    });
});
</script>
@endpush

@endsection
