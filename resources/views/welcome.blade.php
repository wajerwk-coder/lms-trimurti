@extends('layouts.app')

@section('title', 'Beranda - LMS Trimurti Husada')
@section('description', 'Sistem Manajemen Pembelajaran SMK Kesehatan Trimurti Husada Ambon.')

@push('css')
<link href="{{ asset('css/landing.css') }}" rel="stylesheet">
<style>
/* Override main padding dari layouts/app.blade.php */
main.py-4 { padding: 0 !important; }
</style>
@endpush

@section('header')
    @include('partials.landing-header')
@endsection

@section('content')

{{-- ── Hero ────────────────────────────────────────────────────────── --}}
<section class="lp-hero position-relative overflow-hidden">
    <div class="lp-hero-bg"></div>
    <div class="container py-5">
        <div class="row align-items-center py-4 py-lg-5 g-5">
            <div class="col-lg-6">
                <div class="lp-pill d-inline-flex align-items-center gap-2 rounded-pill px-3 py-2 mb-4">
                    <span class="badge bg-white text-primary fw-bold">LMS</span>
                    <span class="small text-white opacity-90">SMK Kesehatan Trimurti Husada Ambon</span>
                </div>
                <h1 class="display-5 fw-bold text-white mb-3 lh-sm">
                    Belajar Lebih Terarah,<br>
                    <span class="lp-underline">Praktik Lebih Terukur</span>
                </h1>
                <p class="lead text-white opacity-90 mb-4">
                    Akses materi, tugas, praktikum, nilai, dan notifikasi ujian dalam satu platform.
                    Lebih cepat, rapi, dan transparan untuk siswa dan guru.
                </p>
                <div class="d-flex gap-3 flex-wrap mb-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-light btn-lg px-4 fw-semibold">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-light btn-lg px-4 fw-semibold">
                            <i class="fas fa-sign-in-alt me-2"></i>Masuk Sekarang
                        </a>
                    @endauth
                    <a href="#fitur" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-star me-2"></i>Lihat Fitur
                    </a>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('about') }}" class="btn btn-sm btn-outline-light rounded-pill">
                        <i class="fas fa-school me-1"></i>Tentang Sekolah
                    </a>
                    <a href="{{ route('contact') }}" class="btn btn-sm btn-outline-light rounded-pill">
                        <i class="fas fa-envelope me-1"></i>Kontak
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div class="lp-hero-card mx-auto" style="max-width:380px;">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="lp-mini-card p-3">
                                <div class="lp-mini-icon bg-primary-subtle text-primary mb-2">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <div class="small text-muted">Materi</div>
                                <div class="fw-semibold">Digital</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="lp-mini-card p-3">
                                <div class="lp-mini-icon bg-success-subtle text-success mb-2">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="small text-muted">Nilai</div>
                                <div class="fw-semibold">Real-time</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="lp-mini-card p-3">
                                <div class="lp-mini-icon bg-warning-subtle text-warning mb-2">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div class="small text-muted">Notifikasi</div>
                                <div class="fw-semibold">Ujian & Tugas</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="lp-mini-card p-3">
                                <div class="lp-mini-icon bg-info-subtle text-info mb-2">
                                    <i class="fas fa-flask"></i>
                                </div>
                                <div class="small text-muted">Praktikum</div>
                                <div class="fw-semibold">Terstruktur</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Stats ───────────────────────────────────────────────────────── --}}
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-6 col-md-3">
                <div class="h2 fw-bold text-primary mb-1 lp-counter" data-bs-target="{{ $stats['siswa'] ?? 0 }}">0</div>
                <small class="text-muted">Siswa Aktif</small>
            </div>
            <div class="col-6 col-md-3">
                <div class="h2 fw-bold text-success mb-1 lp-counter" data-bs-target="{{ $stats['guru'] ?? 0 }}">0</div>
                <small class="text-muted">Guru Profesional</small>
            </div>
            <div class="col-6 col-md-3">
                <div class="h2 fw-bold text-warning mb-1">3</div>
                <small class="text-muted">Program Keahlian</small>
            </div>
            <div class="col-6 col-md-3">
                <div class="h2 fw-bold text-info mb-1">100+</div>
                <small class="text-muted">Materi Digital</small>
            </div>
        </div>
    </div>
</section>

{{-- ── Fitur ───────────────────────────────────────────────────────── --}}
<section id="fitur" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <p class="text-primary fw-semibold text-uppercase small mb-2 letter-spacing">Fitur Unggulan</p>
            <h2 class="fw-bold mb-3">Semua yang Kamu Butuhkan</h2>
            <p class="text-muted mx-auto" style="max-width:520px;">
                Platform pembelajaran digital lengkap untuk mendukung kegiatan belajar mengajar di SMK Kesehatan.
            </p>
        </div>
        <div class="row g-4">
            @foreach([
                ['bg-primary','fa-book-open','Materi Pembelajaran','Akses materi interaktif — dokumen, video, presentasi — kapan saja dan di mana saja.'],
                ['bg-success','fa-tasks','Tugas & Pengumpulan','Kumpulkan tugas online, pantau status, dan terima feedback langsung dari guru.'],
                ['bg-info','fa-flask','Praktikum Digital','Penilaian praktik berbasis SOP checklist dengan nilai otomatis dan transparan.'],
                ['bg-warning','fa-chart-line','Monitoring Progres','Lihat nilai, absensi, dan riwayat belajar dalam laporan yang mudah dipahami.'],
                ['bg-danger','fa-calendar-check','Absensi Online','Rekap kehadiran per mata pelajaran, tersinkron dengan laporan guru dan admin.'],
                ['bg-secondary','fa-bell','Notifikasi Ujian','Pengingat otomatis untuk jadwal ujian, deadline tugas, dan materi baru.'],
            ] as [$color, $icon, $title, $desc])
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm lp-feature-card">
                    <div class="card-body p-4 text-center">
                        <div class="lp-feature-icon {{ $color }} bg-gradient text-white mx-auto mb-4">
                            <i class="fas {{ $icon }} fa-xl"></i>
                        </div>
                        <h5 class="fw-bold mb-2">{{ $title }}</h5>
                        <p class="text-muted mb-0 small">{{ $desc }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Cara Kerja ──────────────────────────────────────────────────── --}}
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <p class="text-primary fw-semibold text-uppercase small mb-2 letter-spacing">Cara Pakai</p>
            <h2 class="fw-bold mb-3">Mudah dalam 4 Langkah</h2>
        </div>
        <div class="row g-4">
            @foreach([
                ['1','fas fa-sign-in-alt','Masuk Akun','Login sesuai peran: admin, guru, atau siswa dengan akun yang diberikan sekolah.'],
                ['2','fas fa-book','Akses Materi & Tugas','Materi dan tugas sudah tersusun sesuai kelas dan mata pelajaran.'],
                ['3','fas fa-flask','Praktik & Penilaian','Penilaian praktik berbasis kriteria dan SOP dengan perhitungan nilai otomatis.'],
                ['4','fas fa-chart-bar','Pantau Progres','Nilai, absensi, dan notifikasi ujian tersedia real-time di dashboard.'],
            ] as [$num, $icon, $title, $desc])
            <div class="col-lg-3 col-md-6">
                <div class="lp-step-card p-4 h-100">
                    <div class="lp-step-num mb-3">{{ $num }}</div>
                    <div class="lp-step-icon mb-3"><i class="fas {{ $icon }}"></i></div>
                    <h6 class="fw-bold mb-2">{{ $title }}</h6>
                    <p class="text-muted small mb-0">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Program Keahlian ────────────────────────────────────────────── --}}
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <p class="text-primary fw-semibold text-uppercase small mb-2 letter-spacing">Jurusan</p>
            <h2 class="fw-bold mb-3">Program Keahlian</h2>
        </div>
        <div class="row g-4 justify-content-center">
            @foreach([
                ['bg-primary','fa-user-nurse','Keperawatan','Kompetensi dasar & lanjutan, materi terstruktur, dan penilaian praktik transparan.'],
                ['bg-success','fa-pills','Farmasi','Peracikan dan K3 — penilaian berbasis SOP checklist dengan feedback otomatis.'],
                ['bg-info','fa-microscope','Analis Kesehatan','Lab dan pemeriksaan dasar — dokumentasi nilai dan absensi terintegrasi.'],
            ] as [$color, $icon, $title, $desc])
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm lp-feature-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="lp-prog-icon {{ $color }} bg-gradient text-white">
                                <i class="fas {{ $icon }}"></i>
                            </div>
                            <div>
                                <div class="fw-bold">{{ $title }}</div>
                                <div class="small text-muted">Program Keahlian</div>
                            </div>
                        </div>
                        <p class="text-muted small mb-0">{{ $desc }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── FAQ ─────────────────────────────────────────────────────────── --}}
<section id="faq" class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <p class="text-primary fw-semibold text-uppercase small mb-2 letter-spacing">FAQ</p>
            <h2 class="fw-bold mb-3">Pertanyaan Umum</h2>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    @foreach([
                        ['Bagaimana siswa melihat nilai praktik?','Setelah guru memfinalkan penilaian, nilai total, grade, dan feedback otomatis tersimpan dan dapat dilihat di halaman nilai pada akun siswa.',true],
                        ['Apakah tugas bisa dikumpulkan lewat HP?','Bisa. Tampilan dirancang responsif sehingga siswa dapat login dan mengumpulkan tugas melalui perangkat mobile.',false],
                        ['Bagaimana sistem menghitung nilai praktik?','Skor setiap poin SOP dijumlah lalu dibagi total poin, dikonversi ke skala 0–100. Nilai otomatis tersimpan setelah guru menekan simpan.',false],
                        ['Bagaimana jika lupa password?','Hubungi guru atau admin sekolah untuk reset password akun Anda.',false],
                    ] as $i => [$q, $a, $open])
                    <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-semibold {{ $open ? '' : 'collapsed' }}"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#faq{{ $i }}"
                                    aria-expanded="{{ $open ? 'true' : 'false' }}">
                                {{ $q }}
                            </button>
                        </h2>
                        <div id="faq{{ $i }}" class="accordion-collapse collapse {{ $open ? 'show' : '' }}" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">{{ $a }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── CTA ──────────────────────────────────────────────────────────── --}}
<section class="py-5 lp-cta text-white text-center">
    <div class="container py-3">
        <h2 class="fw-bold mb-3">Siap Memulai Pembelajaran Digital?</h2>
        <p class="lead opacity-90 mb-4 mx-auto" style="max-width:520px;">
            Bergabunglah dengan seluruh siswa dan guru SMK Kesehatan Trimurti Husada dalam satu platform.
        </p>
        @guest
            <a href="{{ route('login') }}" class="btn btn-light btn-lg px-5 fw-semibold">
                <i class="fas fa-sign-in-alt me-2"></i>Masuk ke Akun
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="btn btn-light btn-lg px-5 fw-semibold me-2">
                <i class="fas fa-tachometer-alt me-2"></i>Ke Dashboard
            </a>
            <a href="{{ route('contact') }}" class="btn btn-outline-light btn-lg px-4">
                <i class="fas fa-headset me-2"></i>Bantuan
            </a>
        @endguest
    </div>
</section>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Counter animation ───────────────────────── */
    function animateCounter(el) {
        var target = parseInt(el.dataset.bsTarget || el.dataset.target || 0);
        if (!target) return;
        var start = 0;
        var duration = 1500;
        var step = Math.ceil(target / (duration / 16));
        var timer = setInterval(function () {
            start += step;
            if (start >= target) { start = target; clearInterval(timer); }
            el.textContent = start.toLocaleString('id');
        }, 16);
    }

    /* IntersectionObserver untuk counter */
    var counters = document.querySelectorAll('.lp-counter');
    if ('IntersectionObserver' in window) {
        var obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) {
                    animateCounter(e.target);
                    obs.unobserve(e.target);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(function (el) { obs.observe(el); });
    } else {
        counters.forEach(animateCounter);
    }

    /* ── Smooth scroll untuk anchor links ─────────── */
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

});
</script>
@endpush

@endsection