@extends('layouts.app')

@section('title', 'Tentang Sekolah - LMS Trimurti Husada')
@section('description', 'Tentang SMK Kesehatan Trimurti Husada Ambon - Sistem Manajemen Pembelajaran.')

@push('css')
<link href="{{ asset('css/landing.css') }}" rel="stylesheet">
<style>
main.py-4 { padding: 0 !important; }
.about-hero {
    background: linear-gradient(135deg, #1e3a8a 0%, #4f46e5 60%, #7c3aed 100%);
    padding: 5rem 0 4rem;
    position: relative;
    overflow: hidden;
}
.about-hero::before {
    content: '';
    position: absolute; top: -80px; right: -80px;
    width: 300px; height: 300px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.info-card {
    border: 1px solid #e8edf2;
    border-radius: 16px;
    transition: transform .18s, box-shadow .18s;
    overflow: hidden;
}
.info-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0,0,0,.10) !important;
}
.info-icon {
    width: 56px; height: 56px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; color: #fff; flex-shrink: 0;
}
.jurusan-badge {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .6rem 1.1rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: .9rem;
    margin: .3rem;
}
</style>
@endpush

@section('header')
    @include('partials.landing-header')
@endsection

@section('content')

{{-- ── Hero ─────────────────────────────────────────────── --}}
<section class="about-hero text-white">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <div class="mb-3">
                    <span class="badge bg-white text-primary fw-semibold px-3 py-2 rounded-pill">
                        <i class="fas fa-school me-1"></i>Tentang Kami
                    </span>
                </div>
                <h1 class="display-5 fw-bold mb-3">
                    SMK Kesehatan<br>Trimurti Husada Ambon
                </h1>
                <p class="lead opacity-90 mb-4">
                    Sekolah Menengah Kejuruan bidang kesehatan yang berkomitmen menghasilkan
                    tenaga kesehatan profesional dan berkualitas di Kota Ambon, Maluku.
                </p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="{{ route('welcome') }}" class="btn btn-outline-light btn-sm rounded-pill">
                        <i class="fas fa-arrow-left me-1"></i>Kembali ke Beranda
                    </a>
                    <a href="{{ route('contact') }}" class="btn btn-light btn-sm rounded-pill">
                        <i class="fas fa-envelope me-1"></i>Hubungi Kami
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Info Utama ───────────────────────────────────────── --}}
<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-4">

            @foreach([
                ['from'=>'#3b82f6','to'=>'#1d4ed8','icon'=>'fa-map-marker-alt','title'=>'Alamat',
                 'content'=> $address ?? 'Jl. Tabea Jou No.8 Waihoka, Sirimau, Kota Ambon, Maluku'],
                ['from'=>'#16a34a','to'=>'#15803d','icon'=>'fa-phone','title'=>'Telepon',
                 'content'=> $phone ?? '(0910) 123456'],
                ['from'=>'#d97706','to'=>'#b45309','icon'=>'fa-envelope','title'=>'Email',
                 'content'=> $email ?? 'info@smktrimurti.sch.id'],
                ['from'=>'#7c3aed','to'=>'#6d28d9','icon'=>'fa-clock','title'=>'Jam Operasional',
                 'content'=> 'Senin – Jumat: 07.00 – 16.00 WIB'],
            ] as $info)
            <div class="col-md-6 col-lg-3">
                <div class="card info-card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="info-icon mb-3"
                             style="background:linear-gradient(135deg,{{ $info['from'] }},{{ $info['to'] }});">
                            <i class="fas {{ $info['icon'] }}"></i>
                        </div>
                        <h6 class="fw-bold mb-2">{{ $info['title'] }}</h6>
                        <p class="text-muted mb-0" style="font-size:.88rem;">{{ $info['content'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach

        </div>
    </div>
</section>

{{-- ── Visi & Misi ──────────────────────────────────────── --}}
<section class="py-5" style="background:#f8fafc;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Visi & Misi</h2>
            <p class="text-muted">Landasan dalam membentuk generasi tenaga kesehatan profesional</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card info-card border-0 shadow-sm h-100"
                     style="border-top: 4px solid #4f46e5 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="info-icon"
                                 style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                                <i class="fas fa-eye"></i>
                            </div>
                            <h5 class="fw-bold mb-0">Visi</h5>
                        </div>
                        <p class="text-muted lh-lg">
                            Menjadi sekolah kejuruan kesehatan yang unggul, berkarakter, dan menghasilkan
                            lulusan yang kompeten, berakhlak mulia, serta siap bersaing di tingkat
                            nasional maupun internasional.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card info-card border-0 shadow-sm h-100"
                     style="border-top: 4px solid #16a34a !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="info-icon"
                                 style="background:linear-gradient(135deg,#16a34a,#15803d);">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <h5 class="fw-bold mb-0">Misi</h5>
                        </div>
                        <ul class="text-muted lh-lg ps-3 mb-0">
                            <li class="mb-2">Menyelenggarakan pendidikan kejuruan kesehatan berkualitas tinggi</li>
                            <li class="mb-2">Mengembangkan kompetensi siswa sesuai standar industri kesehatan</li>
                            <li class="mb-2">Membentuk karakter profesional dan beretika tinggi</li>
                            <li class="mb-2">Menjalin kemitraan dengan institusi kesehatan terkemuka</li>
                            <li>Memanfaatkan teknologi dalam proses pembelajaran</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Program Keahlian ─────────────────────────────────── --}}
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Program Keahlian</h2>
            <p class="text-muted">Jurusan unggulan bidang kesehatan</p>
        </div>
        <div class="row g-4 justify-content-center">
            @foreach([
                ['from'=>'#0891b2','to'=>'#0e7490','icon'=>'fa-heartbeat',
                 'title'=>'Keperawatan',
                 'desc'=>'Program keahlian Asisten Keperawatan yang mempersiapkan tenaga perawat profesional dan terampil dalam pelayanan kesehatan.'],
                ['from'=>'#7c3aed','to'=>'#6d28d9','icon'=>'fa-pills',
                 'title'=>'Farmasi Klinis & Komunitas',
                 'desc'=>'Program keahlian Farmasi yang mencetak tenaga farmasi handal dalam pengelolaan obat dan pelayanan farmasi klinik.'],
                ['from'=>'#16a34a','to'=>'#15803d','icon'=>'fa-microscope',
                 'title'=>'Teknologi Laboratorium Medik',
                 'desc'=>'Program keahlian Analis Kesehatan yang menghasilkan tenaga laboratorium medik terampil dalam pemeriksaan diagnostik.'],
            ] as $jurusan)
            <div class="col-md-6 col-lg-4">
                <div class="card info-card border-0 shadow-sm h-100">
                    <div style="height:5px;background:linear-gradient(90deg,{{ $jurusan['from'] }},{{ $jurusan['to'] }});"></div>
                    <div class="card-body p-4">
                        <div class="info-icon mb-3"
                             style="background:linear-gradient(135deg,{{ $jurusan['from'] }},{{ $jurusan['to'] }});">
                            <i class="fas {{ $jurusan['icon'] }}"></i>
                        </div>
                        <h5 class="fw-bold mb-2">{{ $jurusan['title'] }}</h5>
                        <p class="text-muted mb-0" style="font-size:.875rem;line-height:1.7;">
                            {{ $jurusan['desc'] }}
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── CTA ──────────────────────────────────────────────── --}}
<section class="py-5" style="background:linear-gradient(135deg,#1e3a8a,#4f46e5,#7c3aed);">
    <div class="container text-center text-white">
        <h2 class="fw-bold mb-3">Bergabung dengan LMS Trimurti Husada</h2>
        <p class="lead opacity-90 mb-4">
            Akses materi, tugas, dan jadwal ujian kapan saja dan di mana saja.
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-light btn-lg px-4 fw-semibold">
                    <i class="fas fa-tachometer-alt me-2"></i>Buka Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="btn btn-light btn-lg px-4 fw-semibold">
                    <i class="fas fa-sign-in-alt me-2"></i>Masuk Sekarang
                </a>
            @endauth
            <a href="{{ route('contact') }}" class="btn btn-outline-light btn-lg px-4">
                <i class="fas fa-envelope me-2"></i>Hubungi Kami
            </a>
        </div>
    </div>
</section>

{{-- ── Footer minimal ──────────────────────────────────── --}}
<footer class="bg-dark text-white py-3 text-center" style="font-size:.82rem;">
    <div class="container">
        <span class="opacity-75">
            &copy; {{ date('Y') }} SMK Kesehatan Trimurti Husada Ambon.
            Hak cipta dilindungi.
        </span>
    </div>
</footer>

@endsection
