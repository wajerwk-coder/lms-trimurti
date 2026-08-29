@extends('layouts.admin')

@section('title', 'Tambah Administrator')
@section('page-title', 'Tambah Administrator')
@section('page-subtitle', 'Buat akun administrator baru untuk mengelola sistem.')

@section('page-actions')
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
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
.preview-banner {
    background: linear-gradient(135deg, #1e3a8a 0%, #4f46e5 50%, #7c3aed 100%);
    border-radius: 14px; padding: 1.5rem; position: relative; overflow: hidden;
}
.preview-banner::before {
    content:''; position:absolute; top:-40px; right:-40px;
    width:140px; height:140px; border-radius:50%; background:rgba(255,255,255,.06);
}
</style>
@endpush

@section('content')

{{-- Error summary --}}
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <div class="d-flex gap-2">
        <i class="fas fa-exclamation-circle mt-1 flex-shrink-0"></i>
        <div>
            <strong>{{ $errors->count() }} kesalahan ditemukan:</strong>
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

<form action="{{ route('admin.users.store.admin') }}" method="POST" id="adminForm" novalidate>
@csrf

<div class="row g-4">

    {{-- ═══ KIRI: Form ═══ --}}
    <div class="col-lg-7">

        {{-- Seksi 1: Informasi Akun --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="rounded-2 p-2 bg-primary bg-opacity-10 lh-1">
                        <i class="fas fa-user-shield text-primary"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 fw-semibold">Informasi Akun</h6>
                        <small class="text-muted">Nama, email, dan username untuk login</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- Nama Lengkap --}}
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
                                   placeholder="Nama lengkap administrator"
                                   autocomplete="off" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">
                            Email <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-envelope text-muted"></i>
                            </span>
                            <input type="email" name="email" id="emailInput"
                                   class="form-control border-start-0 @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="admin@sekolah.sch.id"
                                   autocomplete="off" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Username --}}
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
                                   placeholder="admin_nama"
                                   autocomplete="off" required>
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-text">Huruf, angka, dan garis bawah (_) saja.</div>
                    </div>

                    {{-- Nomor Telepon --}}
                    <div class="col-12">
                        <label class="form-label small fw-semibold">
                            Nomor Telepon <span class="text-muted fw-normal">(opsional)</span>
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

                </div>
            </div>
        </div>

        {{-- Seksi 2: Password --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="rounded-2 p-2 bg-warning bg-opacity-10 lh-1">
                        <i class="fas fa-lock text-warning"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 fw-semibold">Password</h6>
                        <small class="text-muted">Password minimal 8 karakter</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- Password --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">
                            Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" name="password" id="passwordInput"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Min. 8 karakter"
                                   autocomplete="new-password" required>
                            <button type="button" class="btn btn-outline-secondary" id="togglePw" tabindex="-1"
                                    aria-label="Tampilkan password">
                                <i class="fas fa-eye" id="pwIcon"></i>
                            </button>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        {{-- Strength bar --}}
                        <div class="mt-2">
                            <div class="progress mb-1" style="height:5px;border-radius:4px;">
                                <div id="pwStrengthBar" class="progress-bar"
                                     style="width:0%;transition:width .3s;border-radius:4px;"></div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Kekuatan password</small>
                                <small id="pwStrengthText" class="fw-semibold"></small>
                            </div>
                        </div>
                    </div>

                    {{-- Konfirmasi --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">
                            Konfirmasi Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="confirmInput"
                                   class="form-control"
                                   placeholder="Ulangi password"
                                   autocomplete="new-password" required>
                            <button type="button" class="btn btn-outline-secondary" id="toggleConfirm" tabindex="-1"
                                    aria-label="Tampilkan konfirmasi password">
                                <i class="fas fa-eye" id="confirmIcon"></i>
                            </button>
                        </div>
                        <small id="pwMatchText" class="d-block mt-1"></small>
                    </div>

                    {{-- Rules --}}
                    <div class="col-12">
                        <div class="bg-light rounded-2 p-3">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="pw-rule fail" id="rule-length">
                                        <i class="fas fa-circle"></i>Min. 8 karakter
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="pw-rule fail" id="rule-lower">
                                        <i class="fas fa-circle"></i>Huruf kecil (a–z)
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="pw-rule fail" id="rule-upper">
                                        <i class="fas fa-circle"></i>Huruf besar (A–Z)
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="pw-rule fail" id="rule-number">
                                        <i class="fas fa-circle"></i>Angka (0–9)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>{{-- /col-lg-7 --}}

    {{-- ═══ KANAN: Preview & Aksi ═══ --}}
    <div class="col-lg-5">

        {{-- Live Preview Card --}}
        <div class="preview-banner mb-4">
            <div class="position-relative" style="z-index:1;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div id="previewAvatar"
                         class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center
                                justify-content-center fw-bold text-white flex-shrink-0"
                         style="width:54px;height:54px;font-size:1.3rem;min-width:54px;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="overflow-hidden flex-grow-1 min-w-0">
                        <div id="previewName" class="fw-bold text-white fs-6 text-truncate">
                            Nama Administrator
                        </div>
                        <div id="previewEmail" class="text-white opacity-75 small text-truncate">
                            email@contoh.com
                        </div>
                        <div id="previewUsername" class="text-white opacity-60 small"></div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge rounded-pill" style="background:rgba(255,255,255,.2)">
                        <i class="fas fa-shield-alt me-1"></i>Administrator
                    </span>
                    <span class="badge rounded-pill" style="background:rgba(255,255,255,.2)">
                        <i class="fas fa-circle me-1" style="font-size:.55rem;"></i>Aktif
                    </span>
                </div>
            </div>
        </div>

        {{-- Hak Akses --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-shield-alt me-2 text-primary"></i>Hak Akses Administrator
                </h6>
            </div>
            <div class="card-body py-3">
                <ul class="list-unstyled mb-0 small">
                    @foreach([
                        'Kelola semua pengguna (admin, guru, siswa)',
                        'Akses penuh ke semua menu sistem',
                        'Lihat laporan dan statistik keseluruhan',
                        'Kelola kelas, jurusan, dan mata pelajaran',
                        'Atur jadwal ujian dan konfigurasi sistem',
                    ] as $item)
                    <li class="d-flex align-items-start gap-2 mb-2">
                        <i class="fas fa-check-circle text-success mt-1 flex-shrink-0"></i>
                        <span class="text-muted">{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-column gap-2">
                <button type="submit" class="btn btn-primary fw-semibold" id="submitBtn">
                    <i class="fas fa-user-shield me-2"></i>Buat Akun Administrator
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </div>

    </div>{{-- /col-lg-5 --}}

</div>{{-- /row --}}
</form>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Toggle visibility ───────────────────────────── */
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

    /* ── Password strength ───────────────────────────── */
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
        { w: 0,   cls: '',           lbl: '' },
        { w: 20,  cls: 'bg-danger',  lbl: 'Sangat Lemah',  lc: 'text-danger' },
        { w: 40,  cls: 'bg-warning', lbl: 'Lemah',         lc: 'text-warning' },
        { w: 60,  cls: 'bg-info',    lbl: 'Cukup',         lc: 'text-info' },
        { w: 80,  cls: 'bg-primary', lbl: 'Kuat',          lc: 'text-primary' },
        { w: 100, cls: 'bg-success', lbl: 'Sangat Kuat',   lc: 'text-success' },
    ];

    pwInput.addEventListener('input', function () {
        const pw = this.value;
        let score = 0;
        Object.keys(rules).forEach(k => {
            const ok = rules[k].fn(pw);
            if (ok) score++;
            rules[k].el.classList.toggle('pass', ok);
            rules[k].el.classList.toggle('fail', !ok);
            rules[k].el.querySelector('i').className = ok ? 'fas fa-check-circle' : 'fas fa-circle';
        });
        const lvl = pw.length === 0 ? levels[0] : levels[Math.min(score, 5)];
        bar.style.width = lvl.w + '%';
        bar.className   = 'progress-bar ' + (lvl.cls || '');
        stxt.textContent = lvl.lbl || '';
        stxt.className   = 'fw-semibold ' + (lvl.lc || '');
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

    /* ── Live Preview ────────────────────────────────── */
    const nameInput     = document.getElementById('nameInput');
    const emailInput    = document.getElementById('emailInput');
    const usernameInput = document.getElementById('usernameInput');
    const pAvatar       = document.getElementById('previewAvatar');
    const pName         = document.getElementById('previewName');
    const pEmail        = document.getElementById('previewEmail');
    const pUsername     = document.getElementById('previewUsername');

    function updatePreview() {
        const name     = nameInput.value.trim();
        const email    = emailInput.value.trim();
        const username = usernameInput.value.trim();

        pName.textContent    = name     || 'Nama Administrator';
        pEmail.textContent   = email    || 'email@contoh.com';
        pUsername.textContent = username ? '@' + username : '';

        if (name) {
            const ini = name.split(/\s+/).slice(0,2).map(w => w[0].toUpperCase()).join('');
            pAvatar.textContent    = ini;
            pAvatar.style.fontSize = ini.length > 1 ? '1.1rem' : '1.4rem';
        } else {
            pAvatar.innerHTML = '<i class="fas fa-user-shield"></i>';
        }
    }

    nameInput.addEventListener('input', updatePreview);
    emailInput.addEventListener('input', updatePreview);
    usernameInput.addEventListener('input', updatePreview);

    /* ── Auto-generate username dari nama ────────────── */
    nameInput.addEventListener('blur', function () {
        if (!usernameInput.value && this.value.trim()) {
            usernameInput.value = 'admin_' + this.value.trim()
                .toLowerCase().replace(/\s+/g,'_').replace(/[^a-z0-9_]/g,'').slice(0,25);
            updatePreview();
        }
    });

    /* ── Submit guard ────────────────────────────────── */
    document.getElementById('adminForm').addEventListener('submit', function (e) {
        if (pwInput.value !== cfInput.value) {
            e.preventDefault();
            cfInput.focus();
            matchTxt.textContent = '✗ Password tidak cocok!';
            matchTxt.className   = 'd-block mt-1 small fw-semibold text-danger';
            return;
        }
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Membuat akun...';
    });

    /* Restore jika tombol back ditekan */
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        const btn = document.getElementById('submitBtn');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-user-shield me-2"></i>Buat Akun Administrator';
    });

});
</script>
@endpush

@endsection
