<header class="lp-navbar navbar navbar-expand-lg">
    <div class="container">

        {{-- Brand --}}
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('welcome') }}">
            <div class="rounded-2 d-flex align-items-center justify-content-center"
                 style="width:34px;height:34px;background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                <i class="fas fa-heartbeat text-white" style="font-size:.85rem;"></i>
            </div>
            <span>LMS Trimurti Husada</span>
        </a>

        {{-- Toggler --}}
        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#landingNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        {{-- Nav Links --}}
        <div class="collapse navbar-collapse" id="landingNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-1">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('welcome') }}#fitur">
                        <i class="fas fa-star me-1 opacity-50"></i>Fitur
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('welcome') }}#faq">
                        <i class="fas fa-question-circle me-1 opacity-50"></i>FAQ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('about') }}">
                        <i class="fas fa-school me-1 opacity-50"></i>Tentang
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('contact') }}">
                        <i class="fas fa-envelope me-1 opacity-50"></i>Kontak
                    </a>
                </li>
            </ul>

            <div class="d-flex gap-2 ms-lg-3 mt-3 mt-lg-0">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-masuk nav-link">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-masuk nav-link">
                        <i class="fas fa-sign-in-alt me-1"></i>Masuk
                    </a>
                @endauth
            </div>
        </div>

    </div>
</header>
