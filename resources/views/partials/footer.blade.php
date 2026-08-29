@php
    $role = Auth::check() ? Auth::user()->role : null;
    $year = date('Y');

    // Warna accent per role
    $accent = match($role) {
        'guru'  => ['from' => '#0f766e', 'to' => '#0891b2', 'hover_bg' => '#f0fdfa', 'hover_text' => '#0f766e', 'hover_border' => '#99f6e4'],
        'siswa' => ['from' => '#7c3aed', 'to' => '#db2777', 'hover_bg' => '#fdf4ff', 'hover_text' => '#7c3aed', 'hover_border' => '#e9d5ff'],
        default => ['from' => '#3b82f6', 'to' => '#6d28d9', 'hover_bg' => '#eef2ff', 'hover_text' => '#4f46e5', 'hover_border' => '#c7d2fe'],
    };
@endphp

<footer class="app-footer" data-role="{{ $role ?? 'guest' }}">
    <div class="footer-inner">

        {{-- MAIN ROW --}}
        <div class="footer-main">

            {{-- Brand --}}
            <div class="footer-brand">
                <div class="footer-brand-icon"
                     style="background:linear-gradient(135deg,{{ $accent['from'] }},{{ $accent['to'] }});">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="footer-brand-text">
                    <div class="footer-brand-name">SMK Kesehatan Trimurti Husada</div>
                    <div class="footer-brand-sub">Learning Management System</div>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="footer-links">
                @if($role === 'siswa')
                    <a href="{{ route('siswa.materials.index') }}" class="footer-link">
                        <i class="fas fa-book"></i>Materi
                    </a>
                    <a href="{{ route('siswa.assignments.index') }}" class="footer-link">
                        <i class="fas fa-tasks"></i>Tugas
                    </a>
                    <a href="{{ route('siswa.praktikum.index') }}" class="footer-link">
                        <i class="fas fa-flask"></i>Praktikum
                    </a>
                    <a href="{{ route('siswa.nilai.index') }}" class="footer-link">
                        <i class="fas fa-chart-bar"></i>Nilai
                    </a>
                    <a href="{{ route('siswa.absensi.index') }}" class="footer-link">
                        <i class="fas fa-calendar-check"></i>Absensi
                    </a>
                @elseif($role === 'guru')
                    <a href="{{ route('guru.materials.index') }}" class="footer-link">
                        <i class="fas fa-book"></i>Materi
                    </a>
                    <a href="{{ route('guru.assignments.index') }}" class="footer-link">
                        <i class="fas fa-tasks"></i>Tugas
                    </a>
                    <a href="{{ route('guru.praktikum.index') }}" class="footer-link">
                        <i class="fas fa-flask"></i>Praktikum
                    </a>
                    <a href="{{ route('guru.penilaian.index') }}" class="footer-link">
                        <i class="fas fa-star"></i>Penilaian
                    </a>
                    <a href="{{ route('guru.absensi.index') }}" class="footer-link">
                        <i class="fas fa-user-check"></i>Absensi
                    </a>
                    <a href="{{ route('guru.reports.index') }}" class="footer-link">
                        <i class="fas fa-chart-line"></i>Laporan
                    </a>
                @elseif($role === 'admin')
                    <a href="{{ route('admin.users.index') }}" class="footer-link">
                        <i class="fas fa-users"></i>Pengguna
                    </a>
                    <a href="{{ route('admin.materials.index') }}" class="footer-link">
                        <i class="fas fa-book"></i>Materi
                    </a>
                    <a href="{{ route('admin.assignments.index') }}" class="footer-link">
                        <i class="fas fa-tasks"></i>Tugas
                    </a>
                    <a href="{{ route('admin.attendance.index') }}" class="footer-link">
                        <i class="fas fa-calendar-check"></i>Absensi
                    </a>
                    <a href="{{ route('admin.exam-schedules.index') }}" class="footer-link">
                        <i class="fas fa-calendar-alt"></i>Jadwal Ujian
                    </a>
                @endif
            </div>

            {{-- Status --}}
            <div class="footer-status">
                <div class="footer-status-row">
                    <span class="footer-status-dot"></span>
                    <span class="footer-status-label">Sistem Online</span>
                </div>
                <div class="footer-version">
                    <i class="fas fa-code-branch"></i> v2.0.0
                </div>
                <button class="btn-back-top" id="backToTop" title="Kembali ke atas">
                    <i class="fas fa-chevron-up"></i>
                </button>
            </div>
        </div>

        {{-- BOTTOM ROW — Copyright --}}
        <div class="footer-bottom">
            <span class="footer-copyright">
                &copy; {{ $year }}
                <strong>SMK Kesehatan Trimurti Husada Ambon</strong>.
                Hak cipta dilindungi.
            </span>
            <span class="footer-made">
                Dibuat dengan <i class="fas fa-heart footer-heart"></i> untuk pendidikan yang lebih baik
            </span>
        </div>

    </div>{{-- .footer-inner --}}
</footer>

{{-- Back to top overlay button (fixed, only visible on scroll) --}}
<button class="btn-back-top-fixed" id="backToTopFixed" title="Kembali ke atas"
        style="display:none;"
        aria-label="Kembali ke atas">
    <i class="fas fa-chevron-up"></i>
</button>

<style>
/* ═══════════════════════════════════════════════
   APP FOOTER — clean, role-adaptive
   ═══════════════════════════════════════════════ */
.app-footer {
    background: #fff;
    border-top: 1.5px solid #e8edf2;
    width: 100%;
    margin-top: auto;
    font-family: 'Inter', sans-serif;
}

.footer-inner {
    padding: 0 1.5rem;
}

/* ── Main row ────────────────────────────────── */
.footer-main {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.25rem;
    padding: .875rem 0;
    border-bottom: 1px solid #f1f5f9;
    flex-wrap: wrap;
}

/* ── Brand ───────────────────────────────────── */
.footer-brand {
    display: flex;
    align-items: center;
    gap: .6rem;
    flex-shrink: 0;
}
.footer-brand-icon {
    width: 34px; height: 34px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    color: #fff;
    font-size: .85rem;
}
.footer-brand-name {
    font-size: .82rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
    white-space: nowrap;
}
.footer-brand-sub {
    font-size: .7rem;
    color: #94a3b8;
    line-height: 1.2;
}

/* ── Links ───────────────────────────────────── */
.footer-links {
    display: flex;
    align-items: center;
    gap: .4rem;
    flex-wrap: wrap;
    justify-content: center;
    flex: 1;
}

.footer-link {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .28rem .6rem;
    font-size: .75rem;
    font-weight: 500;
    color: #64748b;
    text-decoration: none !important;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #f8fafc;
    transition: background .15s, color .15s, border-color .15s, transform .1s;
    white-space: nowrap;
    line-height: 1.4;
}
.footer-link i { font-size: .68rem; }
.footer-link:hover {
    transform: translateY(-1px);
    text-decoration: none !important;
}

/* Role-specific link hover colours via data-role */
[data-role="admin"]  .footer-link:hover { background: #eef2ff; color: #4f46e5; border-color: #c7d2fe; }
[data-role="guru"]   .footer-link:hover { background: #f0fdfa; color: #0f766e; border-color: #99f6e4; }
[data-role="siswa"]  .footer-link:hover { background: #fdf4ff; color: #7c3aed; border-color: #e9d5ff; }

/* ── Status block ────────────────────────────── */
.footer-status {
    display: flex;
    align-items: center;
    gap: .75rem;
    flex-shrink: 0;
}
.footer-status-row {
    display: flex;
    align-items: center;
    gap: .3rem;
}
.footer-status-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #22c55e;
    display: inline-block;
    animation: footer-pulse 2s ease-in-out infinite;
    flex-shrink: 0;
}
.footer-status-label {
    font-size: .72rem;
    color: #64748b;
    white-space: nowrap;
}
.footer-version {
    font-size: .72rem;
    color: #94a3b8;
    white-space: nowrap;
}
.footer-version i { font-size: .65rem; }

/* Inline back-to-top (hidden, replaced by fixed button) */
.btn-back-top { display: none; }

/* ── Bottom row ──────────────────────────────── */
.footer-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
    padding: .55rem 0;
    flex-wrap: wrap;
}
.footer-copyright {
    font-size: .73rem;
    color: #64748b;
}
.footer-copyright strong { color: #334155; }
.footer-made {
    font-size: .73rem;
    color: #94a3b8;
}
.footer-heart {
    color: #f43f5e;
    font-size: .65rem;
    margin: 0 .15rem;
    animation: footer-heartbeat 1.4s ease-in-out infinite;
}

/* ── Fixed back-to-top button ─────────────────── */
.btn-back-top-fixed {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 999;
    width: 38px; height: 38px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    font-size: .8rem;
    color: #fff;
    background: linear-gradient(135deg, #3b82f6, #6d28d9);
    box-shadow: 0 4px 14px rgba(59,130,246,.35);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: opacity .2s, transform .2s, box-shadow .2s;
    opacity: 0;
    pointer-events: none;
}
.btn-back-top-fixed.visible {
    opacity: 1;
    pointer-events: auto;
}
.btn-back-top-fixed:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(59,130,246,.45);
}

/* Role-specific back-to-top colour */
[data-role="guru"]  ~ .btn-back-top-fixed,
footer[data-role="guru"]  .btn-back-top-fixed {
    background: linear-gradient(135deg, #0f766e, #0891b2);
    box-shadow: 0 4px 14px rgba(8,145,178,.35);
}
[data-role="siswa"] ~ .btn-back-top-fixed,
footer[data-role="siswa"] .btn-back-top-fixed {
    background: linear-gradient(135deg, #7c3aed, #db2777);
    box-shadow: 0 4px 14px rgba(124,58,237,.35);
}

/* ── Animations ─────────────────────────────── */
@keyframes footer-pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: .55; transform: scale(.85); }
}
@keyframes footer-heartbeat {
    0%, 100% { transform: scale(1); }
    14%       { transform: scale(1.3); }
    28%       { transform: scale(1); }
    42%       { transform: scale(1.2); }
    70%       { transform: scale(1); }
}

/* ── Responsive ─────────────────────────────── */
@media (max-width: 992px) {
    .footer-main {
        flex-direction: column;
        align-items: flex-start;
        gap: .75rem;
        padding: .75rem 0;
    }
    .footer-links {
        justify-content: flex-start;
    }
    .footer-status {
        width: 100%;
        justify-content: space-between;
    }
    .footer-bottom {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: .25rem;
        padding: .5rem 0;
    }
}
@media (max-width: 576px) {
    .footer-inner { padding: 0 1rem; }
    .footer-brand-name { font-size: .78rem; }
    .footer-link { font-size: .7rem; padding: .24rem .5rem; }
    .btn-back-top-fixed { bottom: 1rem; right: 1rem; width: 34px; height: 34px; }
}
</style>

<script>
(function() {
    'use strict';
    var btn = document.getElementById('backToTopFixed');
    if (!btn) return;

    function syncRole() {
        var footer = document.querySelector('.app-footer');
        var role   = footer ? footer.getAttribute('data-role') : null;
        if (role === 'guru') {
            btn.style.background = 'linear-gradient(135deg,#0f766e,#0891b2)';
            btn.style.boxShadow  = '0 4px 14px rgba(8,145,178,.35)';
        } else if (role === 'siswa') {
            btn.style.background = 'linear-gradient(135deg,#7c3aed,#db2777)';
            btn.style.boxShadow  = '0 4px 14px rgba(124,58,237,.35)';
        }
    }

    function onScroll() {
        var scrolled = (window.pageYOffset || document.documentElement.scrollTop) > 300;
        btn.classList.toggle('visible', scrolled);
        btn.style.display = 'inline-flex';
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    btn.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    syncRole();
    onScroll();
})();
</script>
