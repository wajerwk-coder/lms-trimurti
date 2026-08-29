@extends('layouts.admin')

@section('title', 'Tambah Siswa')
@section('page-title', 'Tambah Siswa')
@section('page-subtitle', 'Daftarkan siswa baru beserta profil lengkap.')

@section('page-actions')
    <a href="{{ route('admin.users.siswa') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@push('css')
<style>
.pw-rule { display:flex; align-items:center; gap:.45rem; font-size:.78rem; color:#64748b; }
.pw-rule i { width:14px; font-size:.7rem; transition:color .2s; }
.pw-rule.pass { color:#16a34a; }
.pw-rule.pass i { color:#16a34a; }
.pw-rule.fail i { color:#cbd5e1; }
.preview-banner {
    background: linear-gradient(135deg, #7c3aed 0%, #a21caf 50%, #db2777 100%);
    border-radius: 14px; padding: 1.5rem; position: relative; overflow: hidden;
}
.preview-banner::before {
    content:''; position:absolute; top:-40px; right:-40px;
    width:140px; height:140px; border-radius:50%; background:rgba(255,255,255,.06);
}
.sec-title { display:flex; align-items:center; gap:.75rem; }
.sec-icon { width:32px; height:32px; border-radius:8px;
    display:flex; align-items:center; justify-content:center; flex-shrink:0; }
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

<form action="{{ route('admin.users.store.siswa') }}" method="POST" id="siswaForm" novalidate>
@csrf
<div class="row g-4">

    {{-- ═══ KIRI ═══ --}}
    <div class="col-lg-8">

        {{-- 1. Akun Login --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="sec-title">
                    <div class="sec-icon bg-purple bg-opacity-10" style="background:rgba(124,58,237,.1)">
                        <i class="fas fa-key text-purple" style="color:#7c3aed;font-size:.8rem;"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-semibold">Akun Login</h6>
                        <small class="text-muted">Nama, email, username, NIS, NISN, dan password</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" name="name" id="nameInput"
                                   class="form-control border-start-0 @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Nama lengkap siswa" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="email" id="emailInput"
                                   class="form-control border-start-0 @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="siswa@sekolah.sch.id" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Username <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 fw-bold text-muted" style="font-size:.9rem;">@</span>
                            <input type="text" name="username" id="usernameInput"
                                   class="form-control border-start-0 @error('username') is-invalid @enderror"
                                   value="{{ old('username') }}" placeholder="siswa_nama" required>
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-text">Huruf, angka, dan garis bawah saja.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">NIS <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-id-card text-muted"></i></span>
                            <input type="text" name="nis" id="nisInput"
                                   class="form-control border-start-0 @error('nis') is-invalid @enderror"
                                   value="{{ old('nis') }}" placeholder="2024001" required>
                            @error('nis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">NISN <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-id-badge text-muted"></i></span>
                            <input type="text" name="nisn"
                                   class="form-control border-start-0 @error('nisn') is-invalid @enderror"
                                   value="{{ old('nisn') }}" placeholder="0087654321" required>
                            @error('nisn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Nomor Telepon</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone text-muted"></i></span>
                            <input type="tel" name="phone"
                                   class="form-control border-start-0"
                                   value="{{ old('phone') }}" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="passwordInput"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Min. 8 karakter" autocomplete="new-password" required>
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
                        <label class="form-label small fw-semibold">Konfirmasi Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="confirmInput"
                                   class="form-control" placeholder="Ulangi password"
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

        {{-- 2. Akademik --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="sec-title">
                    <div class="sec-icon bg-success bg-opacity-10">
                        <i class="fas fa-graduation-cap text-success fa-sm"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-semibold">Informasi Akademik</h6>
                        <small class="text-muted">Kelas, jurusan, dan tahun ajaran</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Kelas <span class="text-danger">*</span></label>
                        <select name="kelas_id" id="kelasSelect"
                                class="form-select @error('kelas_id') is-invalid @enderror" required>
                            <option value="">— Pilih Kelas —</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}"
                                        data-jurusan="{{ $k->jurusan?->name ?? $k->major ?? '' }}"
                                        {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($kelas->isEmpty())
                            <div class="form-text text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Belum ada kelas. <a href="{{ route('admin.kelas.create') }}">Tambah dulu</a>.
                            </div>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Jurusan <span class="text-danger">*</span></label>
                        <select name="major" id="majorSelect"
                                class="form-select @error('major') is-invalid @enderror" required>
                            <option value="">— Pilih Jurusan —</option>
                            @foreach($jurusans as $j)
                                <option value="{{ $j->name }}" {{ old('major') == $j->name ? 'selected':'' }}>
                                    {{ $j->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('major')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                        <input type="text" name="tahun_ajaran"
                               class="form-control @error('tahun_ajaran') is-invalid @enderror"
                               value="{{ old('tahun_ajaran', date('Y').'/'.(date('Y')+1)) }}"
                               placeholder="2024/2025" required>
                        @error('tahun_ajaran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Data Pribadi (opsional) --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="sec-title">
                    <div class="sec-icon bg-primary bg-opacity-10">
                        <i class="fas fa-user-circle text-primary fa-sm"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-semibold">
                            Data Pribadi
                            <span class="badge bg-light text-muted fw-normal ms-1" style="font-size:.72rem;">opsional</span>
                        </h6>
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

        {{-- 4. Orang Tua (opsional) --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="sec-title">
                    <div class="sec-icon bg-secondary bg-opacity-10">
                        <i class="fas fa-users text-secondary fa-sm"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-semibold">
                            Data Orang Tua / Wali
                            <span class="badge bg-light text-muted fw-normal ms-1" style="font-size:.72rem;">opsional</span>
                        </h6>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nama Orang Tua / Wali</label>
                        <input type="text" name="nama_ortu" class="form-control"
                               value="{{ old('nama_ortu') }}" placeholder="Nama orang tua">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Telepon Orang Tua</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone text-muted"></i></span>
                            <input type="tel" name="no_telepon_ortu" class="form-control border-start-0"
                                   value="{{ old('no_telepon_ortu') }}" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. Kesehatan (opsional) --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="sec-title">
                    <div class="sec-icon bg-danger bg-opacity-10">
                        <i class="fas fa-heartbeat text-danger fa-sm"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-semibold">
                            Info Kesehatan
                            <span class="badge bg-light text-muted fw-normal ms-1" style="font-size:.72rem;">opsional</span>
                        </h6>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Golongan Darah</label>
                        <select name="golongan_darah" class="form-select">
                            <option value="">— Pilih —</option>
                            @foreach(['A','B','AB','O'] as $gol)
                                <option value="{{ $gol }}" {{ old('golongan_darah')==$gol ? 'selected':'' }}>{{ $gol }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Riwayat Penyakit</label>
                        <input type="text" name="riwayat_penyakit" class="form-control"
                               value="{{ old('riwayat_penyakit') }}" placeholder="Jika ada">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Alergi</label>
                        <input type="text" name="alergi" class="form-control"
                               value="{{ old('alergi') }}" placeholder="Obat / makanan">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Info Kesehatan Tambahan</label>
                        <textarea name="info_kesehatan" class="form-control" rows="2"
                                  placeholder="Kondisi kesehatan khusus, catatan medis, dll.">{{ old('info_kesehatan') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /col-lg-8 --}}

    {{-- ═══ KANAN ═══ --}}
    <div class="col-lg-4">

        {{-- Live Preview --}}
        <div class="preview-banner mb-4">
            <div class="position-relative" style="z-index:1;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div id="previewAvatar"
                         class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center
                                justify-content-center fw-bold text-white flex-shrink-0"
                         style="width:54px;height:54px;font-size:1.3rem;min-width:54px;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="overflow-hidden flex-grow-1 min-w-0">
                        <div id="previewName" class="fw-bold text-white fs-6 text-truncate">Nama Siswa</div>
                        <div id="previewEmail" class="text-white opacity-75 small text-truncate">email@contoh.com</div>
                        <div id="previewKelas" class="text-white opacity-60 small"></div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge rounded-pill" style="background:rgba(255,255,255,.2)">
                        <i class="fas fa-user-graduate me-1"></i>Siswa
                    </span>
                    <span class="badge rounded-pill" style="background:rgba(255,255,255,.2)">
                        <i class="fas fa-circle me-1" style="font-size:.55rem;"></i>Aktif
                    </span>
                    <span id="previewNis" class="badge rounded-pill d-none" style="background:rgba(255,255,255,.2)">
                        NIS: <span id="previewNisText"></span>
                    </span>
                </div>
            </div>
        </div>

        {{-- Hak Akses --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-shield-alt me-2" style="color:#7c3aed;"></i>Hak Akses Siswa
                </h6>
            </div>
            <div class="card-body py-3">
                <ul class="list-unstyled mb-0 small">
                    @foreach([
                        ['check', 'success', 'Lihat dan unduh materi pelajaran'],
                        ['check', 'success', 'Kumpulkan tugas dari guru'],
                        ['check', 'success', 'Ikuti sesi praktikum'],
                        ['check', 'success', 'Lihat nilai dan rekap absensi'],
                        ['check', 'success', 'Lihat jadwal ujian'],
                        ['times', 'warning', 'Tidak dapat akses panel admin/guru'],
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
                <button type="submit" class="btn fw-semibold text-white" id="submitBtn"
                        style="background:linear-gradient(135deg,#7c3aed,#db2777);">
                    <i class="fas fa-user-graduate me-2"></i>Tambah Siswa
                </button>
                <button type="reset" class="btn btn-outline-secondary" id="resetBtn">
                    <i class="fas fa-undo me-1"></i>Reset Form
                </button>
                <a href="{{ route('admin.users.siswa') }}" class="btn btn-outline-danger">
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

    /* ── Password strength ─────────────────────────── */
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
    const nameEl     = document.getElementById('nameInput');
    const emailEl    = document.getElementById('emailInput');
    const kelasEl    = document.getElementById('kelasSelect');
    const nisEl      = document.getElementById('nisInput');
    const usernameEl = document.getElementById('usernameInput');
    const majorEl    = document.getElementById('majorSelect');

    const pAvatar  = document.getElementById('previewAvatar');
    const pName    = document.getElementById('previewName');
    const pEmail   = document.getElementById('previewEmail');
    const pKelas   = document.getElementById('previewKelas');
    const pNis     = document.getElementById('previewNis');
    const pNisTxt  = document.getElementById('previewNisText');

    function updatePreview() {
        const name  = nameEl.value.trim();
        const email = emailEl.value.trim();
        const kelas = kelasEl.options[kelasEl.selectedIndex]?.text?.trim();
        const nis   = nisEl.value.trim();

        pName.textContent  = name  || 'Nama Siswa';
        pEmail.textContent = email || 'email@contoh.com';
        pKelas.textContent = (kelas && kelas !== '— Pilih Kelas —') ? kelas : '';

        if (nis) { pNis.classList.remove('d-none'); pNisTxt.textContent = nis; }
        else     { pNis.classList.add('d-none'); }

        if (name) {
            const ini = name.split(/\s+/).slice(0,2).map(w => w[0].toUpperCase()).join('');
            pAvatar.textContent    = ini;
            pAvatar.style.fontSize = ini.length > 1 ? '1.1rem' : '1.4rem';
        } else {
            pAvatar.innerHTML = '<i class="fas fa-user-graduate"></i>';
        }
    }

    nameEl.addEventListener('input', updatePreview);
    emailEl.addEventListener('input', updatePreview);
    kelasEl.addEventListener('change', updatePreview);
    nisEl.addEventListener('input', updatePreview);

    /* ── Auto-sync jurusan dari kelas ──────────────── */
    kelasEl.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const jurusan = opt?.dataset?.jurusan ?? '';
        if (jurusan && majorEl) {
            for (let i = 0; i < majorEl.options.length; i++) {
                if (majorEl.options[i].value === jurusan) {
                    majorEl.selectedIndex = i;
                    break;
                }
            }
        }
    });

    /* ── Auto-generate username dari NIS ────────────── */
    nisEl.addEventListener('blur', function () {
        if (!usernameEl.value && this.value.trim()) {
            usernameEl.value = 'siswa_' + this.value.trim().replace(/[^a-z0-9]/gi, '').slice(0, 20);
            updatePreview();
        }
    });

    /* ── Submit guard ──────────────────────────────── */
    document.getElementById('siswaForm').addEventListener('submit', function (e) {
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
        btn.innerHTML = '<i class="fas fa-user-graduate me-2"></i>Tambah Siswa';
    });

    /* Reset perbarui preview */
    document.getElementById('resetBtn').addEventListener('click', () => setTimeout(updatePreview, 10));

});
</script>
@endpush

@endsection
