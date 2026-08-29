@extends('layouts.admin')

@section('title', 'Tambah Guru')
@section('page-title', 'Tambah Guru')
@section('page-subtitle', 'Daftarkan guru baru beserta profil lengkap.')

@section('page-actions')
    <a href="{{ route('admin.users.guru') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@push('css')
<style>
.pw-rule { display:flex; align-items:center; gap:.45rem; font-size:.78rem; color:#64748b; transition:color .2s; }
.pw-rule i { width:14px; font-size:.7rem; transition:color .2s; }
.pw-rule.pass { color:#16a34a; }
.pw-rule.pass i { color:#16a34a; }
.pw-rule.fail i { color:#cbd5e1; }
/* Checkbox subject list */
.hover-bg:hover { background:#eff6ff; border-radius:6px; }
.subject-item label:hover { color:#1d4ed8; }
#subjectCheckboxList::-webkit-scrollbar { width:4px; }
#subjectCheckboxList::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:2px; }
.preview-banner {
    background: linear-gradient(135deg, #065f46 0%, #0891b2 60%, #1d4ed8 100%);
    border-radius: 14px; padding: 1.5rem; position: relative; overflow: hidden;
}
.preview-banner::before {
    content:''; position:absolute; top:-40px; right:-40px;
    width:140px; height:140px; border-radius:50%; background:rgba(255,255,255,.06);
}
.sec-title {
    display:flex; align-items:center; gap:.75rem; margin-bottom:0;
}
.sec-icon {
    width:32px; height:32px; border-radius:8px;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
</style>
@endpush

@section('content')

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <div class="d-flex gap-2">
        <i class="fas fa-exclamation-circle mt-1 flex-shrink-0"></i>
        <div>
            <strong>{{ $errors->count() }} kesalahan:</strong>
            <ul class="mb-0 mt-1 ps-3 small">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
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

<form action="{{ route('admin.users.store.guru') }}" method="POST" id="guruForm" novalidate>
@csrf

<div class="row g-4">

    {{-- ═══ KIRI: Form ═══ --}}
    <div class="col-lg-8">

        {{-- 1. Akun Login --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="sec-title">
                    <div class="sec-icon bg-success bg-opacity-10">
                        <i class="fas fa-key text-success fa-sm"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-semibold">Akun Login</h6>
                        <small class="text-muted">Kredensial untuk masuk ke sistem</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-12">
                        <label class="form-label small fw-semibold">
                            Nama Lengkap <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-user text-muted"></i>
                            </span>
                            <input type="text" name="name" id="nameInput"
                                   class="form-control border-start-0 @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}"
                                   placeholder="Nama lengkap guru"
                                   autocomplete="off" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">
                            Email Sekolah <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-envelope text-muted"></i>
                            </span>
                            <input type="email" name="email" id="emailInput"
                                   class="form-control border-start-0 @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="guru@sekolah.sch.id"
                                   autocomplete="off" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">
                            Username <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 fw-bold text-muted"
                                  style="font-size:.9rem;">@</span>
                            <input type="text" name="username" id="usernameInput"
                                   class="form-control border-start-0 @error('username') is-invalid @enderror"
                                   value="{{ old('username') }}"
                                   placeholder="guru_nama"
                                   autocomplete="off" required>
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-text">Huruf, angka, dan garis bawah saja.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">
                            Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" name="password" id="passwordInput"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Min. 8 karakter"
                                   autocomplete="new-password" required>
                            <button type="button" class="btn btn-outline-secondary" id="togglePw" tabindex="-1">
                                <i class="fas fa-eye" id="pwIcon"></i>
                            </button>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mt-1">
                            <div class="progress mb-1" style="height:4px;border-radius:4px;">
                                <div id="pwStrengthBar" class="progress-bar" style="width:0%;transition:width .3s;"></div>
                            </div>
                            <small id="pwStrengthText" class="fw-semibold"></small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">
                            Konfirmasi Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="confirmInput"
                                   class="form-control"
                                   placeholder="Ulangi password"
                                   autocomplete="new-password" required>
                            <button type="button" class="btn btn-outline-secondary" id="toggleConfirm" tabindex="-1">
                                <i class="fas fa-eye" id="confirmIcon"></i>
                            </button>
                        </div>
                        <small id="pwMatchText" class="d-block mt-1"></small>
                    </div>

                    <div class="col-12">
                        <div class="bg-light rounded-2 p-3">
                            <div class="row g-2">
                                <div class="col-6"><div class="pw-rule fail" id="rule-length"><i class="fas fa-circle"></i>Min. 8 karakter</div></div>
                                <div class="col-6"><div class="pw-rule fail" id="rule-lower"><i class="fas fa-circle"></i>Huruf kecil (a–z)</div></div>
                                <div class="col-6"><div class="pw-rule fail" id="rule-upper"><i class="fas fa-circle"></i>Huruf besar (A–Z)</div></div>
                                <div class="col-6"><div class="pw-rule fail" id="rule-number"><i class="fas fa-circle"></i>Angka (0–9)</div></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- 2. Profil Profesional --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="sec-title">
                    <div class="sec-icon bg-info bg-opacity-10">
                        <i class="fas fa-briefcase text-info fa-sm"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-semibold">Profil Profesional</h6>
                        <small class="text-muted">NIP, mata pelajaran, dan data kepegawaian</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">
                            NIP <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-id-badge text-muted"></i>
                            </span>
                            <input type="text" name="nip"
                                   class="form-control border-start-0 @error('nip') is-invalid @enderror"
                                   value="{{ old('nip') }}"
                                   placeholder="198001012020123456" required>
                            @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">
                            Nomor Telepon
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-phone text-muted"></i>
                            </span>
                            <input type="tel" name="phone"
                                   class="form-control border-start-0"
                                   value="{{ old('phone') }}"
                                   placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">
                            Mata Pelajaran <span class="text-danger">*</span>
                            <span class="badge bg-info ms-1" style="font-size:.65rem;">Pilih lebih dari satu</span>
                        </label>

                        {{-- Search box --}}
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" id="subjectSearch" class="form-control border-start-0"
                                   placeholder="Cari mata pelajaran..." autocomplete="off">
                        </div>

                        @error('subject_ids')
                            <div class="text-danger small mb-1">{{ $message }}</div>
                        @enderror
                        @error('subject_ids.*')
                            <div class="text-danger small mb-1">{{ $message }}</div>
                        @enderror

                        <div id="subjectCheckboxList"
                             class="border rounded-2 p-2"
                             style="max-height:220px;overflow-y:auto;background:#f8fafc;">
                            @forelse($subjects as $subject)
                            <div class="subject-item form-check py-1 px-2 rounded hover-bg"
                                 data-name="{{ strtolower($subject->name) }}">
                                <input class="form-check-input subject-checkbox"
                                       type="checkbox"
                                       name="subject_ids[]"
                                       value="{{ $subject->id }}"
                                       id="sub_{{ $subject->id }}"
                                       {{ in_array($subject->id, old('subject_ids', [])) ? 'checked' : '' }}>
                                <label class="form-check-label w-100" for="sub_{{ $subject->id }}"
                                       style="cursor:pointer;font-size:.85rem;">
                                    {{ $subject->name }}
                                    @if($subject->code)
                                        <span class="text-muted">({{ $subject->code }})</span>
                                    @endif
                                </label>
                            </div>
                            @empty
                            <div class="text-muted text-center py-3 small">
                                <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                Belum ada mata pelajaran.
                                <a href="{{ route('admin.mata-pelajaran.create') }}">Tambah dulu</a>.
                            </div>
                            @endforelse
                        </div>

                        {{-- Selected count indicator --}}
                        <div class="mt-1 d-flex align-items-center justify-content-between">
                            <small class="text-muted" id="selectedCount">0 mata pelajaran dipilih</small>
                            <button type="button" class="btn btn-link btn-sm p-0 text-danger"
                                    id="clearAllSubjects" style="font-size:.75rem;display:none;">
                                Hapus semua
                            </button>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Pendidikan Terakhir</label>
                        <select name="pendidikan_terakhir" class="form-select">
                            <option value="">— Pilih —</option>
                            @foreach(['D3','S1','S2','S3'] as $p)
                                <option value="{{ $p }}" {{ old('pendidikan_terakhir')==$p ? 'selected':'' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Jurusan Pendidikan</label>
                        <input type="text" name="jurusan_pendidikan" class="form-control"
                               value="{{ old('jurusan_pendidikan') }}"
                               placeholder="Pendidikan Keperawatan">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Tahun Mulai Kerja</label>
                        <input type="number" name="tahun_mulai_kerja" class="form-control"
                               value="{{ old('tahun_mulai_kerja') }}"
                               min="1970" max="{{ date('Y') }}"
                               placeholder="{{ date('Y') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Email Pribadi</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-envelope-open text-muted"></i>
                            </span>
                            <input type="email" name="email_pribadi"
                                   class="form-control border-start-0"
                                   value="{{ old('email_pribadi') }}"
                                   placeholder="personal@gmail.com">
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- 3. Data Pribadi (opsional) --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="sec-title">
                    <div class="sec-icon bg-warning bg-opacity-10">
                        <i class="fas fa-user-circle text-warning fa-sm"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-semibold">
                            Data Pribadi
                            <span class="badge bg-light text-muted fw-normal ms-1" style="font-size:.72rem;">opsional</span>
                        </h6>
                        <small class="text-muted">Tempat/tanggal lahir, jenis kelamin, alamat</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control"
                               value="{{ old('tempat_lahir') }}" placeholder="Kota kelahiran">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control"
                               value="{{ old('tanggal_lahir') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select">
                            <option value="">— Pilih —</option>
                            <option value="L" {{ old('jenis_kelamin')=='L' ? 'selected':'' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin')=='P' ? 'selected':'' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="2"
                                  placeholder="Alamat lengkap">{{ old('alamat') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /col-lg-8 --}}

    {{-- ═══ KANAN: Preview & Aksi ═══ --}}
    <div class="col-lg-4">

        {{-- Live Preview --}}
        <div class="preview-banner mb-4">
            <div class="position-relative" style="z-index:1;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div id="previewAvatar"
                         class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center
                                justify-content-center fw-bold text-white flex-shrink-0"
                         style="width:54px;height:54px;font-size:1.3rem;min-width:54px;">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="overflow-hidden flex-grow-1 min-w-0">
                        <div id="previewName" class="fw-bold text-white fs-6 text-truncate">Nama Guru</div>
                        <div id="previewEmail" class="text-white opacity-75 small text-truncate">email@contoh.com</div>
                        <div id="previewSubject" class="text-white opacity-60 small"></div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge rounded-pill" style="background:rgba(255,255,255,.2)">
                        <i class="fas fa-chalkboard-teacher me-1"></i>Guru
                    </span>
                    <span class="badge rounded-pill" style="background:rgba(255,255,255,.2)">
                        <i class="fas fa-circle me-1" style="font-size:.55rem;"></i>Aktif
                    </span>
                    <span id="previewNip" class="badge rounded-pill d-none" style="background:rgba(255,255,255,.2)">
                        NIP: <span id="previewNipText"></span>
                    </span>
                </div>
            </div>
        </div>

        {{-- Hak Akses --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-shield-alt me-2 text-success"></i>Hak Akses Guru
                </h6>
            </div>
            <div class="card-body py-3">
                <ul class="list-unstyled mb-0 small">
                    @foreach([
                        ['check', 'success', 'Kelola absensi kehadiran siswa'],
                        ['check', 'success', 'Upload materi dan bahan ajar'],
                        ['check', 'success', 'Buat dan nilai tugas siswa'],
                        ['check', 'success', 'Kelola sesi dan nilai praktikum'],
                        ['times', 'warning', 'Tidak dapat mengakses panel admin'],
                    ] as [$icon, $color, $text])
                    <li class="d-flex align-items-start gap-2 mb-2">
                        <i class="fas fa-{{ $icon }}-circle text-{{ $color }} mt-1 flex-shrink-0"></i>
                        <span class="text-muted">{{ $text }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Tombol --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-column gap-2">
                <button type="submit" class="btn btn-success fw-semibold" id="submitBtn">
                    <i class="fas fa-user-plus me-2"></i>Tambah Guru
                </button>
                <button type="reset" class="btn btn-outline-secondary" id="resetBtn">
                    <i class="fas fa-undo me-1"></i>Reset Form
                </button>
                <a href="{{ route('admin.users.guru') }}" class="btn btn-outline-danger">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </div>

    </div>{{-- /col-lg-4 --}}

</div>{{-- /row --}}
</form>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Subject checkbox search & counter ─────────── */
    const searchInput   = document.getElementById('subjectSearch');
    const checkboxList  = document.getElementById('subjectCheckboxList');
    const countEl       = document.getElementById('selectedCount');
    const clearBtn      = document.getElementById('clearAllSubjects');

    function updateCount() {
        const checked = checkboxList ? checkboxList.querySelectorAll('input[type=checkbox]:checked').length : 0;
        if (countEl) countEl.textContent = checked + ' mata pelajaran dipilih';
        if (clearBtn) clearBtn.style.display = checked > 0 ? 'inline' : 'none';
    }

    if (searchInput && checkboxList) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            checkboxList.querySelectorAll('.subject-item').forEach(function (item) {
                const name = item.getAttribute('data-name') || '';
                item.style.display = name.includes(q) ? '' : 'none';
            });
        });
    }

    if (checkboxList) {
        checkboxList.addEventListener('change', updateCount);
        updateCount();
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            checkboxList.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
            updateCount();
        });
    }

    /* ── Toggle visibility ─────────────────────────── */
    function makeToggle(btnId, inputId, iconId) {
        document.getElementById(btnId).addEventListener('click', function () {
            const inp  = document.getElementById(inputId);
            const ic   = document.getElementById(iconId);
            const show = inp.type === 'password';
            inp.type     = show ? 'text' : 'password';
            ic.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
        });
    }
    makeToggle('togglePw',      'passwordInput', 'pwIcon');
    makeToggle('toggleConfirm', 'confirmInput',  'confirmIcon');

    /* ── Password strength & rules ─────────────────── */
    const pwInput  = document.getElementById('passwordInput');
    const cfInput  = document.getElementById('confirmInput');
    const bar      = document.getElementById('pwStrengthBar');
    const stxt     = document.getElementById('pwStrengthText');
    const matchTxt = document.getElementById('pwMatchText');

    const rules = {
        length: { el: document.getElementById('rule-length'), fn: p => p.length >= 8 },
        lower:  { el: document.getElementById('rule-lower'),  fn: p => /[a-z]/.test(p) },
        upper:  { el: document.getElementById('rule-upper'),  fn: p => /[A-Z]/.test(p) },
        number: { el: document.getElementById('rule-number'), fn: p => /\d/.test(p) },
    };
    const levels = [
        { w:0,   cls:'',           lbl:'',            lc:'' },
        { w:20,  cls:'bg-danger',  lbl:'Sangat Lemah',lc:'text-danger' },
        { w:40,  cls:'bg-warning', lbl:'Lemah',       lc:'text-warning' },
        { w:60,  cls:'bg-info',    lbl:'Cukup',       lc:'text-info' },
        { w:80,  cls:'bg-primary', lbl:'Kuat',        lc:'text-primary' },
        { w:100, cls:'bg-success', lbl:'Sangat Kuat', lc:'text-success' },
    ];

    pwInput.addEventListener('input', function () {
        const pw = this.value; let score = 0;
        Object.keys(rules).forEach(k => {
            const ok = rules[k].fn(pw);
            if (ok) score++;
            rules[k].el.classList.toggle('pass', ok);
            rules[k].el.classList.toggle('fail', !ok);
            rules[k].el.querySelector('i').className = ok ? 'fas fa-check-circle' : 'fas fa-circle';
        });
        const lvl = pw.length === 0 ? levels[0] : levels[Math.min(score, 5)];
        bar.style.width  = lvl.w + '%';
        bar.className    = 'progress-bar ' + lvl.cls;
        stxt.textContent = lvl.lbl;
        stxt.className   = 'fw-semibold ' + lvl.lc;
        checkMatch();
    });
    cfInput.addEventListener('input', checkMatch);
    function checkMatch() {
        const pw = pwInput.value, cf = cfInput.value;
        if (!cf) { matchTxt.textContent = ''; matchTxt.className = 'd-block mt-1 small'; return; }
        const ok = pw === cf;
        matchTxt.textContent = ok ? '✓ Password cocok' : '✗ Password tidak cocok';
        matchTxt.className   = 'd-block mt-1 small fw-semibold ' + (ok ? 'text-success' : 'text-danger');
    }

    /* ── Live Preview ──────────────────────────────── */
    const nameEl    = document.getElementById('nameInput');
    const emailEl   = document.getElementById('emailInput');
    const subjectEl = document.getElementById('subjectSelect');
    const nipEl     = document.querySelector('input[name="nip"]');
    const userEl    = document.getElementById('usernameInput');

    const pAvatar  = document.getElementById('previewAvatar');
    const pName    = document.getElementById('previewName');
    const pEmail   = document.getElementById('previewEmail');
    const pSubject = document.getElementById('previewSubject');
    const pNip     = document.getElementById('previewNip');
    const pNipTxt  = document.getElementById('previewNipText');

    function updatePreview() {
        const name  = nameEl.value.trim();
        const email = emailEl.value.trim();
        const subj  = subjectEl.options[subjectEl.selectedIndex]?.text?.trim();
        const nip   = nipEl.value.trim();

        pName.textContent  = name  || 'Nama Guru';
        pEmail.textContent = email || 'email@contoh.com';
        pSubject.textContent = (subj && subj !== '— Pilih Mata Pelajaran —') ? subj : '';

        if (nip) { pNip.classList.remove('d-none'); pNipTxt.textContent = nip; }
        else     { pNip.classList.add('d-none'); }

        if (name) {
            const ini = name.split(/\s+/).slice(0,2).map(w => w[0].toUpperCase()).join('');
            pAvatar.textContent    = ini;
            pAvatar.style.fontSize = ini.length > 1 ? '1.1rem' : '1.4rem';
        } else {
            pAvatar.innerHTML = '<i class="fas fa-chalkboard-teacher"></i>';
        }
    }

    nameEl.addEventListener('input', updatePreview);
    emailEl.addEventListener('input', updatePreview);
    subjectEl.addEventListener('change', updatePreview);
    nipEl.addEventListener('input', updatePreview);

    /* ── Auto-generate username ────────────────────── */
    nameEl.addEventListener('blur', function () {
        if (!userEl.value && this.value.trim()) {
            userEl.value = 'guru_' + this.value.trim()
                .toLowerCase().replace(/\s+/g,'_').replace(/[^a-z0-9_]/g,'').slice(0,25);
            updatePreview();
        }
    });

    /* ── Submit guard ──────────────────────────────── */
    document.getElementById('guruForm').addEventListener('submit', function (e) {
        if (pwInput.value !== cfInput.value) {
            e.preventDefault();
            cfInput.focus();
            matchTxt.textContent = '✗ Password tidak cocok!';
            matchTxt.className   = 'd-block mt-1 small fw-semibold text-danger';
            return;
        }
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    });

    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        const btn = document.getElementById('submitBtn');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-user-plus me-2"></i>Tambah Guru';
    });

    /* Reset juga perbarui preview */
    document.getElementById('resetBtn').addEventListener('click', () => setTimeout(updatePreview, 10));

});
</script>
@endpush

@endsection
