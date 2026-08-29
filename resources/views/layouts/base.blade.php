<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description', 'LMS SMK Kesehatan Trimurti Husada')">
    <title>@yield('title', 'LMS Trimurti Husada')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('uploads/logo/favicon.ico') }}">

    <!-- Font Awesome 6 (Consistent version) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts (Consistent font) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS 5.3 (Consistent version) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/components/universal.css') }}" rel="stylesheet">
    <link href="{{ asset('css/base-layout.css') }}" rel="stylesheet">
    @stack('css')
    <style>
    :root {
        --sb-width: 280px;
        --sb-collapsed: 68px;
    }
    .lms-main.sb-collapsed {
        margin-left: var(--sb-collapsed) !important;
    }
    @media (max-width: 768px) {
        .lms-main { margin-left: 0 !important; }
    }
    </style>
</head>
<body class="@yield('body-class', 'lms-layout')">
    <div class="lms-wrapper" style="display:flex;min-height:100vh;">
        <!-- Sidebar -->
        @yield('sidebar')

        <!-- Main Content Wrapper -->
        <div class="lms-main" id="lms-main"
             style="flex:1;margin-left:var(--sb-width,280px);min-width:0;display:flex;flex-direction:column;min-height:100vh;transition:margin-left .25s;">
            <!-- Header -->
            <div style="position:sticky;top:0;z-index:100;flex-shrink:0;">
                @yield('header')
            </div>

            <!-- Content Area -->
            <div class="lms-body" style="flex:1;padding:1.5rem;min-width:0;">
                <!-- Page Header -->
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
                    <div>
                        <h1 style="font-size:1.25rem;font-weight:700;color:#1e293b;margin:0;line-height:1.3;">
                            @yield('page-title', '')
                        </h1>
                        <p style="font-size:.8rem;color:#64748b;margin:.15rem 0 0;">@yield('page-subtitle', '')</p>
                    </div>
                    <div>@yield('page-actions')</div>
                </div>

                <!-- Flash Messages -->
                @include('partials.flash-messages')

                <!-- Main Content -->
                @yield('content')
            </div>

            <!-- Footer -->
            <div style="flex-shrink:0;">
                @include('partials.footer')
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Base JavaScript -->
    <script src="{{ asset('js/base-layout.js') }}"></script>
    <script src="{{ asset('js/notifications.js') }}" defer></script>

    @stack('js')

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // ── Sync sidebar → main column ─────────────────────────
        var sidebar = document.getElementById('sidebar');
        var lmsMain = document.getElementById('lms-main') || document.getElementById('main-content');
        if (sidebar && lmsMain) {
            var obs = new MutationObserver(function() {
                lmsMain.classList.toggle('sb-collapsed', sidebar.classList.contains('collapsed'));
            });
            obs.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
            lmsMain.classList.toggle('sb-collapsed', sidebar.classList.contains('collapsed'));
        }

        // Auto-hide alerts
        document.querySelectorAll('.alert').forEach(function(a) {
            setTimeout(function() {
                try { bootstrap.Alert.getOrCreateInstance(a)?.close(); } catch(e) {}
            }, 5000);
        });

        // DataTables
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            $('.data-table').DataTable({ responsive: true, language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' } });
        }
    });
    </script>
</body>
</html>
