<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — LMS Trimurti Husada</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('uploads/logo/favicon.ico') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: stretch;
        }

        /* ── Layout split ── */
        .login-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Left panel */
        .login-left {
            flex: 1;
            background: linear-gradient(145deg, #3b82f6 0%, #6d28d9 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
            top: -120px; right: -120px;
        }

        .login-left::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(0,0,0,.06);
            bottom: -80px; left: -80px;
        }

        .login-left-content { position: relative; z-index: 1; max-width: 400px; }

        .login-brand {
            display: flex; align-items: center; gap: .75rem;
            margin-bottom: 3rem;
            text-decoration: none;
        }

        .login-brand-icon {
            width: 48px; height: 48px; border-radius: 14px;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            backdrop-filter: blur(8px);
        }

        .login-brand-text { color: #fff; }
        .login-brand-text strong { display: block; font-size: 1.05rem; }
        .login-brand-text small { opacity: .75; font-size: .8rem; }

        .login-headline {
            color: #fff;
            font-size: 1.9rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 1rem;
        }

        .login-subtext { color: rgba(255,255,255,.8); line-height: 1.65; margin-bottom: 2rem; }

        .login-feature {
            display: flex; align-items: center; gap: .75rem;
            color: rgba(255,255,255,.9);
            margin-bottom: .75rem; font-size: .9rem;
        }

        .login-feature-icon {
            width: 32px; height: 32px; border-radius: 8px;
            background: rgba(255,255,255,.15);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: .8rem;
        }

        /* Right panel */
        .login-right {
            width: 480px;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2.5rem 3rem;
            box-shadow: -8px 0 40px rgba(0,0,0,.08);
        }

        .login-back {
            display: inline-flex; align-items: center; gap: .4rem;
            color: #64748b; font-size: .85rem; text-decoration: none;
            margin-bottom: 2.5rem;
            transition: color .2s;
        }

        .login-back:hover { color: #3b82f6; }

        .login-title {
            font-size: 1.6rem; font-weight: 700; color: #1e293b;
            margin-bottom: .3rem;
        }

        .login-subtitle { color: #64748b; font-size: .9rem; margin-bottom: 2rem; }

        /* Form */
        .form-label { font-size: .85rem; font-weight: 600; color: #374151; margin-bottom: .4rem; }

        .input-wrap { position: relative; }

        .input-wrap .input-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #9ca3af; pointer-events: none; font-size: .9rem;
        }

        .input-wrap .form-control {
            padding-left: 2.6rem;
            height: 48px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: .92rem;
            transition: border-color .2s, box-shadow .2s;
        }

        .input-wrap .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,.15);
            outline: none;
        }

        .input-wrap .form-control.is-invalid {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239,68,68,.12);
        }

        .input-wrap .toggle-pw {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: #9ca3af;
            padding: 0; font-size: .9rem; transition: color .2s;
        }

        .input-wrap .toggle-pw:hover { color: #3b82f6; }

        /* Remember + forgot */
        .form-check-input:checked { background-color: #3b82f6; border-color: #3b82f6; }
        .form-check-label { font-size: .85rem; color: #374151; }
        .forgot-link { font-size: .85rem; color: #3b82f6; text-decoration: none; }
        .forgot-link:hover { text-decoration: underline; }

        /* Submit button */
        .btn-login {
            height: 50px;
            border-radius: 10px;
            font-weight: 600;
            font-size: .95rem;
            background: linear-gradient(135deg, #3b82f6 0%, #6d28d9 100%);
            border: none;
            color: #fff;
            transition: opacity .2s, transform .15s;
            width: 100%;
        }

        .btn-login:hover { opacity: .92; transform: translateY(-1px); }
        .btn-login:active { transform: translateY(0); }
        .btn-login:disabled { opacity: .65; transform: none; }

        /* Error */
        .login-error {
            background: #fef2f2; border: 1px solid #fecaca;
            border-radius: 10px; padding: .85rem 1rem;
            color: #b91c1c; font-size: .85rem; margin-bottom: 1.25rem;
        }

        /* Divider */
        .login-divider {
            display: flex; align-items: center; gap: .75rem;
            color: #cbd5e1; font-size: .8rem; margin: 1.5rem 0;
        }
        .login-divider::before,
        .login-divider::after {
            content: ''; flex: 1; height: 1px; background: #e2e8f0;
        }

        /* Footer text */
        .login-footer { margin-top: 1.5rem; text-align: center; font-size: .82rem; color: #94a3b8; }

        /* Responsive */
        @media (max-width: 900px) {
            .login-left { display: none; }
            .login-right { width: 100%; padding: 2rem 1.5rem; box-shadow: none; }
        }

        @media (max-width: 480px) {
            .login-right { padding: 1.5rem 1.25rem; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">

    {{-- ── Left Panel ── --}}
    <div class="login-left d-none d-lg-flex">
        <div class="login-left-content">
            <a href="{{ route('welcome') }}" class="login-brand">
                <div class="login-brand-icon">
                    <i class="fas fa-graduation-cap text-white fa-lg"></i>
                </div>
                <div class="login-brand-text">
                    <strong>LMS Trimurti Husada</strong>
                    <small>SMK Kesehatan Trimurti Husada Ambon</small>
                </div>
            </a>

            <h2 class="login-headline">Platform Pembelajaran<br>Digital untuk Semua</h2>
            <p class="login-subtext">
                Akses materi, tugas, praktikum, dan nilai kapan saja.
                Dirancang untuk siswa, guru, dan admin SMK Kesehatan.
            </p>

            <div class="login-feature">
                <div class="login-feature-icon"><i class="fas fa-book-open"></i></div>
                <span>Materi dan tugas terorganisir per kelas</span>
            </div>
            <div class="login-feature">
                <div class="login-feature-icon"><i class="fas fa-flask"></i></div>
                <span>Penilaian praktik berbasis SOP otomatis</span>
            </div>
            <div class="login-feature">
                <div class="login-feature-icon"><i class="fas fa-chart-line"></i></div>
                <span>Laporan nilai dan absensi real-time</span>
            </div>
            <div class="login-feature">
                <div class="login-feature-icon"><i class="fas fa-bell"></i></div>
                <span>Notifikasi jadwal ujian & deadline tugas</span>
            </div>
        </div>
    </div>

    {{-- ── Right Panel (Form) ── --}}
    <div class="login-right">
        <a href="{{ route('welcome') }}" class="login-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
        </a>

        <h1 class="login-title">Selamat Datang</h1>
        <p class="login-subtitle">Masuk ke akun Anda untuk melanjutkan</p>

        {{-- Error --}}
        @if ($errors->any())
            <div class="login-error">
                <i class="fas fa-exclamation-circle me-2"></i>
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif

        @if (session('error'))
            <div class="login-error">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
            @csrf

            {{-- Email --}}
            <div class="mb-4">
                <label for="email" class="form-label">Alamat Email</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email"
                           id="email"
                           name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}"
                           placeholder="nama@email.com"
                           autocomplete="email"
                           autofocus
                           required>
                </div>
            </div>

            {{-- Password --}}
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Masukkan password"
                           autocomplete="current-password"
                           required>
                    <button type="button" class="toggle-pw" id="togglePw" aria-label="Tampilkan/sembunyikan password">
                        <i class="fas fa-eye" id="togglePwIcon"></i>
                    </button>
                </div>
            </div>

            {{-- Remember + Forgot --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check mb-0">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember"
                           {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-link">Lupa password?</a>
                @endif
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-login" id="loginBtn">
                <span id="btnText"><i class="fas fa-sign-in-alt me-2"></i>Masuk</span>
                <span id="btnLoading" class="d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>Memproses...
                </span>
            </button>
        </form>

        <div class="login-footer">
            &copy; {{ date('Y') }} SMK Kesehatan Trimurti Husada Ambon
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle password visibility
document.getElementById('togglePw').addEventListener('click', function () {
    const pw   = document.getElementById('password');
    const icon = document.getElementById('togglePwIcon');
    const show = pw.type === 'password';
    pw.type    = show ? 'text' : 'password';
    icon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
});

// Loading state on submit
document.getElementById('loginForm').addEventListener('submit', function () {
    const btn     = document.getElementById('loginBtn');
    const text    = document.getElementById('btnText');
    const loading = document.getElementById('btnLoading');
    btn.disabled  = true;
    text.classList.add('d-none');
    loading.classList.remove('d-none');
});
</script>
</body>
</html>
