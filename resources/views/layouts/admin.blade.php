<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - LMS Trimurti Husada')</title>
    <meta name="description" content="@yield('description', 'LMS SMK Kesehatan Trimurti Husada')">

    <link rel="icon" type="image/x-icon" href="{{ asset('uploads/logo/favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

    @stack('css')
    @stack('styles')

    <style>
    /* ============================================================
       ADMIN LAYOUT — Modern Blue-Violet Theme (like login page)
       ============================================================ */
    :root {
        --sb-width:      280px;
        --sb-collapsed:  68px;
        --hdr-height:    64px;
        --bg-page:       #f0f4f8;
        --transition:    .25s cubic-bezier(.4,0,.2,1);
        /* Brand colors from login */
        --brand-from:    #3b82f6;
        --brand-to:      #6d28d9;
        --brand-mid:     #5b21b6;
    }

    *, *::before, *::after { box-sizing: border-box; }

    html, body {
        margin: 0; padding: 0; height: 100%;
        font-family: 'Inter', sans-serif;
        background: var(--bg-page);
        font-size: 14px; line-height: 1.6;
    }

    /* ── Wrapper ──────────────────────────────────────────── */
    .lms-wrapper { display: flex; min-height: 100vh; }

    /* ── Reset universal.css conflicts ───────────────────── */
    body.admin-layout .main-content,
    body.admin-layout #main-content {
        margin-left: 0 !important; width: auto !important;
        min-height: unset !important; padding: 0 !important; border: none !important;
    }
    body.admin-layout .sidebar-wrapper { display: none !important; }

    /* ── Main column ──────────────────────────────────────── */
    .lms-main {
        flex: 1; margin-left: var(--sb-width); min-width: 0;
        display: flex; flex-direction: column; min-height: 100vh;
        transition: margin-left var(--transition);
    }
    .lms-main.sb-collapsed { margin-left: var(--sb-collapsed); }

    /* ── Sticky header ────────────────────────────────────── */
    .lms-header { position: sticky; top: 0; z-index: 100; flex-shrink: 0; }

    /* ── Content area ─────────────────────────────────────── */
    .lms-body { flex: 1; padding: 1.5rem; min-width: 0; }

    /* ── Page header — gradient brand strip ────────────────── */
    .lms-page-header {
        display: flex; align-items: flex-start;
        justify-content: space-between; gap: 1rem;
        margin-bottom: 1.5rem; flex-wrap: wrap;
        background: linear-gradient(135deg, var(--brand-from) 0%, var(--brand-to) 100%);
        border-radius: 14px; padding: 1.25rem 1.5rem;
        box-shadow: 0 4px 20px rgba(59,130,246,.25);
        position: relative; overflow: hidden;
    }
    .lms-page-header::before {
        content: ''; position: absolute; top: -40px; right: -40px;
        width: 160px; height: 160px;
        background: rgba(255,255,255,.07); border-radius: 50%;
    }
    .lms-page-header-left { position: relative; z-index: 1; }
    .lms-page-header .flex-shrink-0 { position: relative; z-index: 1; }

    .lms-page-title {
        font-size: 1.2rem; font-weight: 700;
        color: #fff; margin: 0; line-height: 1.3;
    }
    .lms-page-subtitle { font-size: .78rem; color: rgba(255,255,255,.8); margin: .15rem 0 0; }

    /* Page actions buttons — override to white-outline style */
    .lms-page-header .btn {
        border-radius: 8px; font-size: .82rem; font-weight: 500;
    }
    .lms-page-header .btn-sm { padding: .35rem .85rem; }
    .lms-page-header .btn-primary,
    .lms-page-header .btn-danger,
    .lms-page-header .btn-success,
    .lms-page-header .btn-warning {
        background: rgba(255,255,255,.95) !important;
        color: var(--brand-from) !important;
        border: none !important;
        box-shadow: 0 2px 8px rgba(0,0,0,.12);
    }
    .lms-page-header .btn-primary:hover,
    .lms-page-header .btn-danger:hover,
    .lms-page-header .btn-success:hover,
    .lms-page-header .btn-warning:hover {
        background: #fff !important;
        box-shadow: 0 4px 12px rgba(0,0,0,.18);
        transform: translateY(-1px);
    }
    .lms-page-header .btn-outline-secondary {
        background: rgba(255,255,255,.15) !important;
        color: #fff !important;
        border: 1.5px solid rgba(255,255,255,.4) !important;
    }
    .lms-page-header .btn-outline-secondary:hover {
        background: rgba(255,255,255,.25) !important;
    }

    /* ── Alert bar ────────────────────────────────────────── */
    .lms-alerts { margin-bottom: 1rem; }

    /* ── Cards — subtle brand accent ─────────────────────── */
    .card {
        border-radius: 12px !important;
        transition: box-shadow .2s;
    }
    .card.shadow-sm:hover { box-shadow: 0 6px 20px rgba(59,130,246,.1) !important; }

    /* Stat cards top accent bar */
    .stat-card-accent::before {
        content: ''; display: block; height: 3px;
        background: linear-gradient(90deg, var(--brand-from), var(--brand-to));
        border-radius: 12px 12px 0 0;
    }

    /* ── Scrollbar ─────────────────────────────────────────── */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #c7d2fe; border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: var(--brand-from); }

    /* ── Footer ───────────────────────────────────────────── */
    .lms-footer { flex-shrink: 0; }

    /* ── Mobile ───────────────────────────────────────────── */
    @media (max-width: 768px) {
        .lms-main { margin-left: 0 !important; }
        .lms-body { padding: 1rem; }
        .lms-page-header { padding: 1rem; }
        .sidebar { z-index: 1050 !important; }
        .sidebar-overlay.active { z-index: 1040 !important; }
        .lms-header { z-index: 1030 !important; position: sticky; top: 0; }

        /* Tabel scroll horizontal */
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table td, .table th { white-space: nowrap; font-size: .78rem; padding: .45rem .6rem; }

        /* Cegah zoom otomatis iOS */
        .form-control, .form-select, .form-check-input { font-size: 16px !important; }

        /* Card padding lebih kecil */
        .card-body { padding: 1rem !important; }
        .card-header, .card-footer { padding: .75rem 1rem !important; }

        /* Tombol lebih mudah ditekan */
        .btn { min-height: 38px; }
        .btn-sm { min-height: 32px; }

        /* Dropdown tidak keluar layar */
        .dropdown-menu { max-width: calc(100vw - 2rem); }

        /* Gambar tidak overflow */
        img { max-width: 100%; height: auto; }

        /* Page title */
        .lms-page-title { font-size: 1.1rem !important; }
        .lms-page-subtitle { font-size: .78rem !important; }
    }

    @media (max-width: 480px) {
        .lms-body { padding: .75rem; }
        .card-body { padding: .75rem !important; }
        h4, .h4 { font-size: 1.1rem; }
        h5, .h5 { font-size: 1rem; }
    }
    </style>
</head>
<body>
<div class="lms-wrapper">

    {{-- SIDEBAR --}}
    @include('partials.sidebar-admin')

    {{-- MAIN COLUMN --}}
    <div class="lms-main" id="lms-main">

        {{-- STICKY HEADER --}}
        <div class="lms-header">
            @include('partials.header-admin')
        </div>

        {{-- CONTENT --}}
        <div class="lms-body">

            {{-- Alerts --}}
            <div class="lms-alerts">
                @foreach(['success'=>'check-circle','error'=>'exclamation-circle','warning'=>'exclamation-triangle','info'=>'info-circle'] as $type => $icon)
                    @if(session($type))
                        <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show" role="alert">
                            <i class="fas fa-{{ $icon }} me-2"></i>{{ session($type) }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Page header (title + actions) --}}
            @if(View::hasSection('page-title') || View::hasSection('page-actions'))
            <div class="lms-page-header">
                <div class="lms-page-header-left">
                    @hasSection('page-title')
                        <h1 class="lms-page-title">@yield('page-title')</h1>
                    @endif
                    @hasSection('page-subtitle')
                        <p class="lms-page-subtitle">@yield('page-subtitle')</p>
                    @endif
                </div>
                @hasSection('page-actions')
                    <div class="flex-shrink-0">@yield('page-actions')</div>
                @endif
            </div>
            @endif

            {{-- Main page content --}}
            @yield('content')
        </div>

        {{-- FOOTER inside main column so it doesn't overlap sidebar --}}
        <div class="lms-footer">
            @include('partials.footer')
        </div>

    </div>{{-- .lms-main --}}

</div>{{-- .lms-wrapper --}}

{{-- JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/notifications.js') }}" defer></script>

@stack('js')
@stack('scripts')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Sync sidebar collapse → main column margin ────────────
    var sidebar  = document.getElementById('sidebar');
    var lmsMain  = document.getElementById('lms-main');

    if (sidebar && lmsMain) {
        var observer = new MutationObserver(function() {
            lmsMain.classList.toggle('sb-collapsed', sidebar.classList.contains('collapsed'));
        });
        observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
        // Initial state
        lmsMain.classList.toggle('sb-collapsed', sidebar.classList.contains('collapsed'));
    }

    // jQuery CSRF setup
    if (typeof $ !== 'undefined') {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' } });
    }

    // Auto-hide alerts
    document.querySelectorAll('.alert').forEach(function(a) {
        setTimeout(function() { bootstrap.Alert.getOrCreateInstance(a)?.close(); }, 5000);
    });

    // DataTables
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('.data-table').DataTable({ responsive: true, language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' } });
    }
});
</script>
</body>
</html>
