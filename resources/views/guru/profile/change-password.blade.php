@extends('layouts.guru')

@section('title', 'Ubah Password')
@section('guru-page-title', 'Ubah Password')
@section('page-subtitle', 'Perbarui password akun Anda')

@section('page-actions')
    <a href="{{ route('guru.profile.edit') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali ke Profil
    </a>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-6">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-key me-2 text-warning"></i>Ubah Password</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('guru.profile.change-password.post') }}" method="POST" id="pwForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Password Saat Ini <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="current_password" id="currentPw"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   placeholder="Masukkan password saat ini" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePw('currentPw','icon1')">
                                <i class="fas fa-eye" id="icon1"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="newPw"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Min. 8 karakter" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePw('newPw','icon2')">
                                <i class="fas fa-eye" id="icon2"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        {{-- Strength indicator --}}
                        <div class="progress mt-2" style="height:4px;" id="strengthBar">
                            <div id="strengthFill" class="progress-bar" style="width:0%"></div>
                        </div>
                        <small id="strengthText" class="text-muted"></small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="confirmPw"
                                   class="form-control"
                                   placeholder="Ulangi password baru" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePw('confirmPw','icon3')">
                                <i class="fas fa-eye" id="icon3"></i>
                            </button>
                        </div>
                        <small id="matchText" class="text-muted"></small>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('guru.profile.edit') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-warning" id="submitBtn">
                            <i class="fas fa-key me-1"></i>Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body small text-muted">
                <i class="fas fa-info-circle me-1 text-info"></i>
                <strong>Tips password yang kuat:</strong>
                <ul class="mt-1 mb-0 ps-3">
                    <li>Minimal 8 karakter</li>
                    <li>Kombinasi huruf besar dan kecil</li>
                    <li>Mengandung angka</li>
                    <li>Mengandung karakter khusus (!@#$%)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
function togglePw(id, iconId) {
    const f = document.getElementById(id);
    const i = document.getElementById(iconId);
    const show = f.type === 'password';
    f.type = show ? 'text' : 'password';
    i.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
}

// Password strength
document.getElementById('newPw').addEventListener('input', function () {
    const pw  = this.value;
    const bar = document.getElementById('strengthFill');
    const txt = document.getElementById('strengthText');
    let score = 0;
    if (pw.length >= 8) score++;
    if (/[a-z]/.test(pw)) score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^a-zA-Z0-9]/.test(pw)) score++;

    const map = [
        [0, 'bg-secondary', ''],
        [20, 'bg-danger', 'Sangat Lemah'],
        [40, 'bg-warning', 'Lemah'],
        [60, 'bg-info', 'Cukup'],
        [80, 'bg-primary', 'Kuat'],
        [100, 'bg-success', 'Sangat Kuat'],
    ];
    const [pct, cls, label] = map[score];
    bar.style.width = pct + '%';
    bar.className = 'progress-bar ' + cls;
    txt.textContent = label;
    txt.className = 'small ' + cls.replace('bg-', 'text-');
    checkMatch();
});

// Match check
document.getElementById('confirmPw').addEventListener('input', checkMatch);
function checkMatch() {
    const a = document.getElementById('newPw').value;
    const b = document.getElementById('confirmPw').value;
    const el = document.getElementById('matchText');
    if (!b) { el.textContent = ''; return; }
    if (a === b) {
        el.textContent = '✓ Password cocok';
        el.className = 'small text-success';
    } else {
        el.textContent = '✗ Password tidak cocok';
        el.className = 'small text-danger';
    }
}

// Loading state
document.getElementById('pwForm').addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';
});
</script>
@endpush
@endsection