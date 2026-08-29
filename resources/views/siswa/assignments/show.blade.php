@extends('layouts.siswa')

@section('title', $assignment->title)
@section('page-title', $assignment->title)
@section('page-subtitle', ($assignment->subject?->name ?? '—') . ($assignment->kelas ? ' · ' . $assignment->kelas->name : ''))

@section('page-actions')
    <a href="{{ route('siswa.assignments.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@push('css')
<style>
.info-row {
    display:flex;align-items:center;gap:.65rem;
    padding:.5rem 0;border-bottom:1px solid #f1f5f9;font-size:.84rem;
}
.info-row:last-child { border-bottom:none; }
.info-icon {
    width:28px;height:28px;border-radius:8px;
    display:flex;align-items:center;justify-content:center;
    flex-shrink:0;font-size:.65rem;
}
.progress-sm { height:8px;border-radius:4px; }
.score-circle {
    width:80px;height:80px;border-radius:50%;
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    border:4px solid;margin:0 auto;
}
</style>
@endpush

@section('content')

@php
    $deadline    = $assignment->due_date;
    $isPast      = $deadline && $deadline->isPast();
    $isSubmitted = !is_null($submission);
    $isGraded    = $isSubmitted && $submission->score !== null;
    $canSubmit   = !$isSubmitted && $assignment->is_published
                   && (!$isPast || $assignment->allow_late);
    $maxScore    = $assignment->max_score ?? 100;
    $pct         = $isGraded ? min(100, ($submission->score / $maxScore) * 100) : 0;
    $scoreClr    = $pct >= 80 ? '#16a34a' : ($pct >= 60 ? '#d97706' : '#dc2626');

    // Status badge
    [$sClr, $sBg, $sLabel] = match(true) {
        $isGraded            => ['#16a34a','rgba(22,163,74,.09)',   'Sudah Dinilai'],
        $isSubmitted         => ['#0891b2','rgba(8,145,178,.09)',   'Menunggu Nilai'],
        $isPast && !$assignment->allow_late
                             => ['#dc2626','rgba(220,38,38,.09)',   'Terlambat'],
        $isPast && $assignment->allow_late
                             => ['#d97706','rgba(217,119,6,.09)',   'Boleh Terlambat'],
        $deadline && $deadline->diffInHours(now()) <= 24
                             => ['#d97706','rgba(217,119,6,.09)',   'Segera Berakhir'],
        default              => ['#16a34a','rgba(22,163,74,.09)',   'Aktif'],
    };
@endphp

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:12px;">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:12px;">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:12px;">
    <i class="fas fa-exclamation-circle me-2"></i>
    <strong>{{ $errors->count() }} kesalahan:</strong>
    <ul class="mb-0 mt-1 ps-3 small">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    {{-- ═══ KIRI ═══════════════════════════════════════════════ --}}
    <div class="col-lg-8">

        {{-- Hero card --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;overflow:hidden;">
            <div style="height:5px;background:{{ $sClr }};"></div>
            <div class="card-body p-4">

                {{-- Header --}}
                <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                    <div>
                        <h4 class="fw-bold mb-2">{{ $assignment->title }}</h4>
                        <span class="badge fw-semibold"
                              style="background:{{ $sBg }};color:{{ $sClr }};border-radius:20px;padding:.25rem .8rem;">
                            {{ $sLabel }}
                        </span>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="fw-black" style="font-size:1.8rem;color:{{ $sClr }};line-height:1;">
                            {{ $maxScore }}
                        </div>
                        <div class="text-muted" style="font-size:.7rem;">poin maks</div>
                    </div>
                </div>

                {{-- Deskripsi --}}
                @if($assignment->description)
                <div class="mb-4">
                    <div class="text-muted mb-1"
                         style="font-size:.7rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;">
                        Deskripsi
                    </div>
                    <div class="p-3 rounded-3 lh-lg"
                         style="background:#f8fafc;border:1px solid #e8edf2;font-size:.85rem;color:#475569;">
                        {!! nl2br(e(strip_tags($assignment->description))) !!}
                    </div>
                </div>
                @endif

                {{-- Instruksi --}}
                @if($assignment->instructions)
                <div class="mb-4">
                    <div class="text-muted mb-1"
                         style="font-size:.7rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;">
                        Instruksi
                    </div>
                    <div class="p-3 rounded-3 lh-lg"
                         style="background:#f8fafc;border:1px solid #e8edf2;font-size:.85rem;color:#475569;">
                        {!! nl2br(e(strip_tags($assignment->instructions))) !!}
                    </div>
                </div>
                @endif

                {{-- Info grid --}}
                <div class="row g-2">
                    @foreach([
                        ['fa-book',         'rgba(124,58,237,.09)', '#7c3aed', 'Mata Pelajaran', $assignment->subject?->name ?? '—'],
                        ['fa-door-open',     'rgba(22,163,74,.09)', '#16a34a', 'Kelas',          $assignment->kelas?->name ?? '—'],
                        ['fa-clock',         $isPast ? 'rgba(220,38,38,.09)' : 'rgba(8,145,178,.09)',
                                             $isPast ? '#dc2626' : '#0891b2', 'Deadline',
                                             $deadline?->format('d M Y, H:i') ?? 'Tidak ada'],
                        ['fa-chalkboard-teacher','rgba(100,116,139,.09)','#64748b','Guru', $assignment->guru?->name ?? '—'],
                        ['fa-clock-rotate-left', $assignment->allow_late ? 'rgba(22,163,74,.09)' : 'rgba(100,116,139,.09)',
                                                  $assignment->allow_late ? '#16a34a' : '#64748b',
                                                  'Terlambat', $assignment->allow_late ? 'Diizinkan' : 'Tidak diizinkan'],
                    ] as [$ic, $ibg, $iclr, $label, $val])
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-icon" style="background:{{ $ibg }};">
                                <i class="fas {{ $ic }}" style="color:{{ $iclr }};"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:.67rem;text-transform:uppercase;letter-spacing:.04em;">{{ $label }}</div>
                                <div class="fw-semibold" style="font-size:.84rem;">{{ $val }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- File lampiran guru --}}
                @php $attachFile = $assignment->file_url ?? $assignment->file ?? null; @endphp
                @if($attachFile)
                <div class="mt-3 pt-3 border-top">
                    <div class="text-muted mb-2"
                         style="font-size:.7rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;">
                        File Lampiran Tugas
                    </div>
                    <a href="{{ asset('storage/assignments/' . $attachFile) }}"
                       class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 text-decoration-none"
                       style="background:rgba(59,130,246,.09);color:#3b82f6;font-size:.82rem;"
                       download>
                        <i class="fas fa-file-download"></i>
                        <span>{{ $attachFile }}</span>
                    </a>
                </div>
                @endif

            </div>
        </div>

        {{-- Status Pengumpulan --}}
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-header bg-white border-bottom py-3 px-4"
                 style="border-radius:14px 14px 0 0;">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-clipboard-check me-2" style="color:#16a34a;"></i>Status Pengumpulan
                </h6>
            </div>
            <div class="card-body p-4">
                @if($isSubmitted)
                    {{-- Sudah dikumpulkan --}}
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3 mb-3"
                         style="background:rgba(22,163,74,.07);border:1px solid rgba(22,163,74,.2);">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:36px;height:36px;background:rgba(22,163,74,.15);">
                            <i class="fas fa-check" style="color:#16a34a;font-size:.8rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold" style="color:#16a34a;">Tugas telah dikumpulkan</div>
                            <div class="text-muted small mt-1">
                                {{ $submission->submitted_at?->format('d M Y, H:i') ?? '—' }}
                                @if($submission->is_late ?? false)
                                    <span class="badge ms-1 fw-semibold"
                                          style="background:rgba(217,119,6,.1);color:#d97706;border-radius:20px;">
                                        Terlambat
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- File yang dikumpulkan --}}
                    @if($submission->file_path)
                    <div class="mb-3">
                        <div class="text-muted mb-2"
                             style="font-size:.72rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;">
                            File Kamu
                        </div>
                        <a href="{{ route('siswa.assignments.download', [$assignment->id, $submission->id]) }}"
                           class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 text-decoration-none"
                           style="background:rgba(22,163,74,.09);color:#16a34a;font-size:.82rem;">
                            <i class="fas fa-file-download"></i>
                            <span>Unduh File</span>
                        </a>
                    </div>
                    @endif

                    {{-- Teks submission --}}
                    @if($submission->submission_text)
                    <div class="mb-3">
                        <div class="text-muted mb-2"
                             style="font-size:.72rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;">
                            Catatan Kamu
                        </div>
                        <div class="p-3 rounded-3 small lh-lg"
                             style="background:#f8fafc;border:1px solid #e8edf2;color:#475569;">
                            {!! nl2br(e($submission->submission_text)) !!}
                        </div>
                    </div>
                    @endif

                    {{-- Nilai --}}
                    @if($isGraded)
                    <div class="p-3 rounded-3"
                         style="background:rgba(22,163,74,.06);border:1px solid rgba(22,163,74,.15);">
                        <div class="d-flex align-items-center gap-4">
                            <div class="score-circle flex-shrink-0"
                                 style="border-color:{{ $scoreClr }};">
                                <div class="fw-black lh-1"
                                     style="font-size:1.7rem;color:{{ $scoreClr }};">
                                    {{ number_format($submission->score, 0) }}
                                </div>
                                <div class="text-muted" style="font-size:.62rem;">/ {{ $maxScore }}</div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between mb-1"
                                     style="font-size:.78rem;">
                                    <span class="text-muted">Nilai</span>
                                    <span class="fw-semibold" style="color:{{ $scoreClr }};">
                                        {{ number_format($pct, 1) }}%
                                    </span>
                                </div>
                                <div class="progress progress-sm">
                                    <div class="progress-bar"
                                         style="width:{{ $pct }}%;background:{{ $scoreClr }};"></div>
                                </div>
                                <div class="text-muted mt-1" style="font-size:.72rem;">
                                    {{ $pct >= 80 ? 'Sangat Baik' : ($pct >= 70 ? 'Baik' : ($pct >= 60 ? 'Cukup' : 'Perlu Perbaikan')) }}
                                </div>
                            </div>
                        </div>
                        @if($submission->feedback)
                        <div class="mt-3 pt-3 border-top">
                            <div class="text-muted mb-1"
                                 style="font-size:.7rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;">
                                Feedback Guru
                            </div>
                            <div class="small lh-lg" style="color:#475569;">
                                {!! nl2br(e($submission->feedback)) !!}
                            </div>
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="d-flex align-items-center gap-2 p-3 rounded-3"
                         style="background:rgba(217,119,6,.07);border:1px solid rgba(217,119,6,.2);">
                        <i class="fas fa-hourglass-half" style="color:#d97706;"></i>
                        <span class="small fw-semibold" style="color:#d97706;">Menunggu penilaian dari guru</span>
                    </div>
                    @endif

                @elseif($isPast && !$assignment->allow_late)
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3"
                         style="background:rgba(220,38,38,.07);border:1px solid rgba(220,38,38,.2);">
                        <i class="fas fa-times-circle mt-1 flex-shrink-0" style="color:#dc2626;"></i>
                        <div>
                            <div class="fw-semibold" style="color:#dc2626;">Batas waktu telah berlalu</div>
                            <div class="small text-muted mt-1">
                                Pengumpulan terlambat tidak diizinkan. Hubungi guru jika ada pertanyaan.
                            </div>
                        </div>
                    </div>
                @else
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3"
                         style="background:rgba(217,119,6,.07);border:1px solid rgba(217,119,6,.2);">
                        <i class="fas fa-exclamation-triangle mt-1 flex-shrink-0" style="color:#d97706;"></i>
                        <div>
                            <div class="fw-semibold" style="color:#d97706;">Belum dikumpulkan</div>
                            @if($deadline)
                            <div class="small text-muted mt-1">
                                Kumpulkan sebelum <strong>{{ $deadline->format('d M Y, H:i') }}</strong>
                                @if($deadline->isFuture())
                                    ({{ $deadline->diffForHumans() }})
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ═══ KANAN ════════════════════════════════════════════════ --}}
    <div class="col-lg-4">

        {{-- Form Kumpulkan --}}
        @if($canSubmit)
        <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;overflow:hidden;">
            <div style="height:4px;background:#16a34a;"></div>
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#16a34a;">
                    <i class="fas fa-paper-plane me-2"></i>Kumpulkan Tugas
                </h6>
            </div>
            <div class="card-body px-4 py-3">
                @if($isPast && $assignment->allow_late)
                <div class="d-flex align-items-start gap-2 p-2 rounded-3 mb-3"
                     style="background:rgba(217,119,6,.09);border:1px solid rgba(217,119,6,.2);font-size:.78rem;">
                    <i class="fas fa-exclamation-triangle flex-shrink-0 mt-1" style="color:#d97706;"></i>
                    <span style="color:#d97706;">Pengumpulan terlambat diizinkan, namun mungkin mempengaruhi penilaian.</span>
                </div>
                @endif

                <form action="{{ route('siswa.assignments.submit', $assignment->id) }}"
                      method="POST" enctype="multipart/form-data"
                      id="submissionForm" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            File Tugas <span class="text-danger">*</span>
                        </label>
                        <input type="file"
                               class="form-control @error('file') is-invalid @enderror"
                               name="file" id="submission_file"
                               accept=".pdf,.doc,.docx,.txt,.zip,.rar,.jpg,.jpeg,.png"
                               style="border-radius:8px;">
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="filePreview" class="mt-2"></div>
                        <div class="form-text" style="font-size:.72rem;">
                            PDF, DOC, DOCX, TXT, ZIP, RAR, JPG — maks 5 MB
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Catatan untuk Guru</label>
                        <textarea class="form-control @error('submission_text') is-invalid @enderror"
                                  name="submission_text" rows="3"
                                  placeholder="Keterangan tambahan (opsional)…"
                                  style="border-radius:8px;resize:none;">{{ old('submission_text') }}</textarea>
                        @error('submission_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn w-100 fw-semibold" id="submitBtn"
                            style="border-radius:10px;background:#16a34a;color:#fff;border:none;">
                        <i class="fas fa-paper-plane me-2"></i>Kumpulkan Tugas
                    </button>
                </form>
            </div>
        </div>

        @elseif($isGraded)
        {{-- Sudah dinilai --}}
        <div class="card border-0 shadow-sm mb-4 text-center" style="border-radius:14px;">
            <div style="height:4px;background:{{ $scoreClr }};"></div>
            <div class="card-body py-4">
                <div class="score-circle mx-auto mb-3" style="border-color:{{ $scoreClr }};">
                    <div class="fw-black lh-1"
                         style="font-size:1.8rem;color:{{ $scoreClr }};">
                        {{ number_format($submission->score, 0) }}
                    </div>
                    <div class="text-muted" style="font-size:.62rem;">/ {{ $maxScore }}</div>
                </div>
                <div class="fw-bold mb-1" style="color:{{ $scoreClr }};">
                    {{ $pct >= 80 ? 'Sangat Baik' : ($pct >= 70 ? 'Baik' : ($pct >= 60 ? 'Cukup' : 'Perlu Perbaikan')) }}
                </div>
                <div class="text-muted small">{{ number_format($pct, 1) }}% dari nilai maksimum</div>
            </div>
        </div>

        @elseif($isSubmitted)
        {{-- Sudah dikumpulkan, belum dinilai --}}
        <div class="card border-0 shadow-sm mb-4 text-center" style="border-radius:14px;">
            <div style="height:4px;background:#0891b2;"></div>
            <div class="card-body py-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                     style="width:64px;height:64px;background:rgba(8,145,178,.1);">
                    <i class="fas fa-check-circle fa-2x" style="color:#0891b2;"></i>
                </div>
                <div class="fw-semibold text-dark">Tugas Terkumpul</div>
                <div class="text-muted small mt-1">Menunggu penilaian guru.</div>
            </div>
        </div>
        @endif

        {{-- Tips --}}
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-header bg-white border-bottom py-3 px-4"
                 style="border-radius:14px 14px 0 0;">
                <h6 class="mb-0 fw-semibold" style="font-size:.85rem;">
                    <i class="fas fa-lightbulb me-2" style="color:#d97706;"></i>Tips Pengumpulan
                </h6>
            </div>
            <div class="card-body px-4 py-3">
                <ul class="list-unstyled mb-0">
                    @foreach([
                        'Pastikan format file sesuai yang diminta',
                        'Periksa kembali sebelum mengumpulkan',
                        'Tugas terlambat dapat mempengaruhi nilai',
                        'Simpan bukti pengumpulan',
                    ] as $tip)
                    <li class="d-flex gap-2 mb-2" style="font-size:.8rem;">
                        <i class="fas fa-check-circle flex-shrink-0 mt-1" style="color:#16a34a;font-size:.7rem;"></i>
                        <span class="text-muted">{{ $tip }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('submission_file');
    const preview   = document.getElementById('filePreview');
    const submitBtn = document.getElementById('submitBtn');
    const form      = document.getElementById('submissionForm');

    if (fileInput && preview) {
        fileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) { preview.innerHTML = ''; return; }

            const maxMB   = 5;
            const allowed = ['pdf','doc','docx','txt','zip','rar','jpg','jpeg','png'];
            const ext     = file.name.split('.').pop().toLowerCase();
            const sizeMB  = (file.size / 1024 / 1024).toFixed(2);

            if (file.size > maxMB * 1024 * 1024) {
                preview.innerHTML = `<div class="d-flex align-items-center gap-2 p-2 rounded-3 mt-1"
                    style="background:rgba(220,38,38,.08);border:1px solid rgba(220,38,38,.2);font-size:.78rem;">
                    <i class="fas fa-times-circle" style="color:#dc2626;"></i>
                    <span style="color:#dc2626;">File terlalu besar (${sizeMB} MB). Maks 5 MB.</span></div>`;
                this.value = '';
                return;
            }
            if (!allowed.includes(ext)) {
                preview.innerHTML = `<div class="d-flex align-items-center gap-2 p-2 rounded-3 mt-1"
                    style="background:rgba(220,38,38,.08);border:1px solid rgba(220,38,38,.2);font-size:.78rem;">
                    <i class="fas fa-times-circle" style="color:#dc2626;"></i>
                    <span style="color:#dc2626;">Format .${ext.toUpperCase()} tidak diizinkan.</span></div>`;
                this.value = '';
                return;
            }
            preview.innerHTML = `<div class="d-flex align-items-center gap-2 p-2 rounded-3 mt-1"
                style="background:rgba(22,163,74,.08);border:1px solid rgba(22,163,74,.2);font-size:.78rem;">
                <i class="fas fa-file-check" style="color:#16a34a;"></i>
                <div>
                    <div class="fw-semibold text-dark">${file.name}</div>
                    <div class="text-muted">${sizeMB} MB · ${ext.toUpperCase()}</div>
                </div></div>`;
        });
    }

    if (form && submitBtn) {
        form.addEventListener('submit', function (e) {
            const hasFile = (fileInput?.files?.length ?? 0) > 0;
            const hasText = document.querySelector('textarea[name=submission_text]')?.value?.trim() ?? '';
            if (!hasFile && !hasText) {
                e.preventDefault();
                if (preview) preview.innerHTML = `<div class="d-flex align-items-center gap-2 p-2 rounded-3 mt-1"
                    style="background:rgba(220,38,38,.08);border:1px solid rgba(220,38,38,.2);font-size:.78rem;">
                    <i class="fas fa-exclamation-circle" style="color:#dc2626;"></i>
                    <span style="color:#dc2626;">Wajib melampirkan file atau mengisi catatan.</span></div>`;
                return;
            }
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengunggah…';
        });
    }

    window.addEventListener('pageshow', function (e) {
        if (e.persisted && submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Kumpulkan Tugas';
        }
    });
});
</script>
@endpush

@endsection
