<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'LMS Trimurti') }} - @yield('title')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #4F46E5;
            --secondary-color: #7C3AED;
            --accent-color: #10B981;
            --warning-color: #F59E0B;
            --danger-color: #EF4444;
            --dark-color: #111827;
            --light-color: #F3F4F6;
            --gray-color: #6B7280;
            --sidebar-width: 280px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F9FAFB;
        }

        .sidebar {
            background-color: var(--dark-color);
            color: white;
            min-height: 100vh;
            width: var(--sidebar-width, 280px);
            position: fixed;
            left: 0;
            top: 0;
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar-collapsed {
            width: 70px !important;
        }

        .sidebar .logo {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar .logo img {
            max-height: 40px;
        }

        .sidebar .menu {
            padding: 1rem 0;
        }

        .sidebar .menu-item {
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.2s;
        }

        .sidebar .menu-item:hover,
        .sidebar .menu-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 4px solid var(--primary-color);
        }

        .sidebar .menu-item i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }

        .main-content {
            margin-left: var(--sidebar-width, 280px); /* Gunakan CSS variable agar fleksibel */
            padding: 2rem;
            padding-bottom: 4rem; /* Tambahkan padding bawah agar footer tidak terpotong */
            transition: all 0.3s;
        }

        .main-content-expanded {
            margin-left: 70px;
        }

        .navbar {
            background-color: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-radius: 0.5rem;
        }

        .navbar .toggle-sidebar {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--dark-color);
            cursor: pointer;
        }

        .navbar .user-menu {
            display: flex;
            align-items: center;
        }

        .navbar .user-menu .user-name {
            margin-right: 1rem;
            font-weight: 500;
        }

        .navbar .user-menu .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
        }

        .navbar .user-menu .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #E5E7EB;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-color);
            margin: 0;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #4338CA;
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-secondary:hover {
            background-color: #6D28D9;
        }

        .btn-success {
            background-color: var(--accent-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #059669;
        }

        .btn-warning {
            background-color: var(--warning-color);
            color: white;
        }

        .btn-warning:hover {
            background-color: #D97706;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #DC2626;
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #E5E7EB;
        }

        .table th {
            font-weight: 600;
            color: var(--dark-color);
            background-color: #F9FAFB;
        }

        .table tr:hover {
            background-color: #F9FAFB;
        }

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .bg-success {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .bg-warning text-dark {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .bg-danger {
            background-color: #FEE2E2;
            color: #B91C1C;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark-color);
        }

        .form-control {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #D1D5DB;
            border-radius: 0.375rem;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: #D1FAE5;
            color: #065F46;
            border-left: 4px solid #10B981;
        }

        .alert-warning {
            background-color: #FEF3C7;
            color: #92400E;
            border-left: 4px solid #F59E0B;
        }

        .alert-danger {
            background-color: #FEE2E2;
            color: #B91C1C;
            border-left: 4px solid #EF4444;
        }

        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 1.5rem 0 0;
        }

        .pagination li {
            margin: 0 0.25rem;
        }

        .pagination li a, .pagination li span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 0.375rem;
            background-color: white;
            color: var(--dark-color);
            text-decoration: none;
            transition: all 0.2s;
        }

        .pagination li.active span {
            background-color: var(--primary-color);
            color: white;
        }

        .pagination li a:hover {
            background-color: #F3F4F6;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar-visible {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>

    @stack('styles')

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}">
        </div>
        <div class="menu">
            @include('partials.sidebar')
        </div>
    </div>

    <div class="main-content" id="main-content">
        @if(Auth::check())
            @if(Auth::user()->role === 'guru')
                @include('partials.header-guru')
            @elseif(Auth::user()->role === 'admin')
                @include('partials.header-admin')
            @elseif(Auth::user()->role === 'siswa')
                @include('partials.header-siswa')
            @else
                @include('partials.header')
            @endif
        @else
            @include('partials.header')
        @endif
        <div class="navbar">
            <button class="toggle-sidebar" id="toggle-sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div class="user-menu">
                <span class="user-name">{{ Auth::user()->name }}</span>
                <div class="user-avatar">
                    <img src="{{ Auth::user()->profile_photo_url ?? asset('images/default-avatar.png') }}" alt="{{ Auth::user()->name }}">
                </div>
            </div>
        </div>
        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning">
                    {{ session('warning') }}
                </div>
            @endif

            @yield('content')
        </div>
        @include('partials.footer')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const toggleSidebar = document.getElementById('toggle-sidebar');

            toggleSidebar.addEventListener('click', function() {
                sidebar.classList.toggle('sidebar-collapsed');
                mainContent.classList.toggle('main-content-expanded');
            });

            // Responsive behavior
            function checkWidth() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.add('sidebar-collapsed');
                    mainContent.classList.add('main-content-expanded');
                } else {
                    sidebar.classList.remove('sidebar-collapsed');
                    mainContent.classList.remove('main-content-expanded');
                }
            }

            // Initial check
            checkWidth();

            // Check on resize
            window.addEventListener('resize', checkWidth);
        });
    </script>

    @stack('scripts')
</body>
</html>
