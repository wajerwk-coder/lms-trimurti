<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description', 'Portal Siswa - LMS SMK Kesehatan Trimurti Husada')">
    <title>@yield('title', 'Siswa - LMS Trimurti Husada')</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('uploads/logo/favicon.ico') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    @stack('css')
    @stack('styles')

    <style>
    /* ============================================================
       SISWA LAYOUT — Modern Purple-Pink Theme
       ============================================================ */
    :root {
        --sb-width:      280px;
        --sb-collapsed:  68px;
        --hdr-height:    64px;
        --bg-page:       #f0f4f8;
        --transition:    .25s cubic-bezier(.4,0,.2,1);
        --primary-color: #8b5cf6;
        --secondary-color: #64748b;
        --border-color: #e2e8f0;
        --dark-color: #1e293b;
        --light-color: #f8fafc;
    }

    *, *::before, *::after { box-sizing: border-box; }

    html, body {
        margin: 0; padding: 0; height: 100%;
        font-family: 'Inter', sans-serif;
        background: var(--bg-page);
        font-size: 14px; line-height: 1.6;
    }

    .lms-wrapper { display: flex; min-height: 100vh; }

    body .main-content, body #main-content {
        margin-left: 0 !important; width: auto !important;
        min-height: unset !important; padding: 0 !important;
    }

    .lms-main {
        flex: 1; margin-left: var(--sb-width); min-width: 0;
        display: flex; flex-direction: column; min-height: 100vh;
        transition: margin-left var(--transition);
    }
    .lms-main.sb-collapsed { margin-left: var(--sb-collapsed); }

    .lms-header { position: sticky; top: 0; z-index: 100; flex-shrink: 0; }

    .lms-body { flex: 1; padding: 1.5rem; min-width: 0; }

    /* ── Page header — Siswa purple-pink gradient ────────── */
    .lms-page-header {
        display: flex; align-items: flex-start;
        justify-content: space-between; gap: 1rem;
        margin-bottom: 1.5rem; flex-wrap: wrap;
        background: linear-gradient(135deg, #7c3aed 0%, #a21caf 50%, #db2777 100%);
        border-radius: 14px; padding: 1.25rem 1.5rem;
        box-shadow: 0 4px 20px rgba(124,58,237,.2);
        position: relative; overflow: hidden;
    }
    .lms-page-header::before {
        content:''; position:absolute; top:-40px; right:-40px;
        width:160px; height:160px; background:rgba(255,255,255,.06); border-radius:50%;
    }
    .lms-page-title {
        font-size: 1.2rem; font-weight: 700; color: #fff;
        margin: 0; line-height: 1.3; position: relative; z-index: 1;
    }
    .lms-page-subtitle {
        font-size: .78rem; color: rgba(255,255,255,.8);
        margin: .15rem 0 0; position: relative; z-index: 1;
    }
    .lms-page-header .flex-shrink-0 { position: relative; z-index: 1; }
    .lms-page-header .btn {
        font-size: .82rem; border-radius: 8px; font-weight: 500;
    }
    .lms-page-header .btn-primary,
    .lms-page-header .btn-success,
    .lms-page-header .btn-warning,
    .lms-page-header .btn-danger {
        background: rgba(255,255,255,.95) !important;
        color: #7c3aed !important; border: none !important;
        box-shadow: 0 2px 8px rgba(0,0,0,.12);
    }
    .lms-page-header .btn-outline-secondary {
        background: rgba(255,255,255,.15) !important;
        color: #fff !important;
        border: 1.5px solid rgba(255,255,255,.4) !important;
    }

    /* ── Cards ───────────────────────────────────────────── */
    .card { border: 1px solid var(--border-color); border-radius: 12px !important; box-shadow: 0 2px 8px rgba(0,0,0,.06); transition: box-shadow .2s; }
    .card.shadow-sm:hover { box-shadow: 0 6px 20px rgba(124,58,237,.1) !important; }
    .stats-card { background:#fff; border-radius:12px; border:1px solid var(--border-color); height:100%; transition:all .3s; }
    .stats-card:hover { transform:translateY(-4px); box-shadow:0 8px 25px rgba(124,58,237,.12); }

    /* ── Scrollbar purple ─────────────────────────────────── */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #ddd6fe; border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: #7c3aed; }

    .lms-footer { flex-shrink: 0; }

    @media (max-width: 768px) {
        .lms-main { margin-left: 0 !important; }
        .lms-body { padding: 1rem; }
        .lms-page-header { padding: 1rem; }
    }
    </style>
</head>
<body>
<div class="lms-wrapper">

    {{-- SIDEBAR --}}
    @include('partials.sidebar-siswa')

    {{-- MAIN COLUMN --}}
    <div class="lms-main" id="lms-main">

        {{-- STICKY HEADER --}}
        <div class="lms-header">
            @include('partials.header-siswa')
        </div>

        {{-- CONTENT --}}
        <div class="lms-body">

            {{-- Flash alerts --}}
            @foreach(['success'=>'check-circle','error'=>'exclamation-circle','warning'=>'exclamation-triangle','info'=>'info-circle'] as $ftype => $ficon)
                @if(session($ftype))
                    <div class="alert alert-{{ $ftype === 'error' ? 'danger' : $ftype }} alert-dismissible fade show mb-3" role="alert">
                        <i class="fas fa-{{ $ficon }} me-2"></i>{{ session($ftype) }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            @endforeach

            {{-- Breadcrumb --}}
            @if(!request()->routeIs('siswa.dashboard'))
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('siswa.dashboard') }}" class="text-decoration-none">Beranda</a>
                    </li>
                    @yield('siswa-breadcrumb')
                </ol>
            </nav>
            @endif

            {{-- Page header (title + actions) --}}
            @if(View::hasSection('siswa-page-title') || View::hasSection('page-title') || View::hasSection('page-actions'))
            <div class="lms-page-header">
                <div>
                    @hasSection('siswa-page-title')
                        <h1 class="lms-page-title">@yield('siswa-page-title')</h1>
                    @elsehasSection('page-title')
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

        {{-- FOOTER --}}
        <div class="lms-footer">
            @include('partials.footer')
        </div>

    </div>{{-- .lms-main --}}

</div>{{-- .lms-wrapper --}}

{{-- JS --}}
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@stack('js')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Sync sidebar collapse → main column margin ────────────
    var sidebar = document.getElementById('sidebar');
    var lmsMain = document.getElementById('lms-main');
    if (sidebar && lmsMain) {
        var obs = new MutationObserver(function() {
            lmsMain.classList.toggle('sb-collapsed', sidebar.classList.contains('collapsed'));
        });
        obs.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
        lmsMain.classList.toggle('sb-collapsed', sidebar.classList.contains('collapsed'));
    }

    // Auto-hide alerts
    document.querySelectorAll('.alert').forEach(function(a) {
        setTimeout(function() { try { bootstrap.Alert.getOrCreateInstance(a)?.close(); } catch(e) {} }, 5000);
    });

    // DataTables
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('.data-table').DataTable({ responsive: true, language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' } });
    }
});
</script>
</body>
</html>
