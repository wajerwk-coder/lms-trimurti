<script>
(function() {
    'use strict';

    var STORAGE_KEY = 'lms_sidebar_collapsed';
    var MOBILE_BP   = 768;

    // Flag untuk mencegah double-fire di HP (touch + click)
    var _lastToggle = 0;

    function initSidebar() {
        var sidebar      = document.getElementById('sidebar');
        var overlay      = document.getElementById('sidebarOverlay');
        var lmsMain      = document.getElementById('lms-main');
        var collapseBtn  = document.getElementById('sidebarCollapseBtn');
        var collapseIcon = document.getElementById('collapseIcon');
        var headerToggle = document.getElementById('sidebarToggle');
        var mobileToggle = document.getElementById('mobileSidebarToggle');

        if (!sidebar) return;

        /* ── Collapse (desktop) ─────────────────────────────── */
        function setCollapsed(collapsed) {
            sidebar.classList.toggle('collapsed', collapsed);
            if (lmsMain) lmsMain.classList.toggle('sb-collapsed', collapsed);
            if (collapseIcon) {
                collapseIcon.style.transform = collapsed ? 'rotate(180deg)' : 'rotate(0deg)';
            }
            try { localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0'); } catch(e) {}
        }

        function toggleCollapse() {
            setCollapsed(!sidebar.classList.contains('collapsed'));
        }

        /* ── Mobile show/hide ───────────────────────────────── */
        function showMobile() {
            sidebar.classList.add('show');
            if (overlay) overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function hideMobile() {
            sidebar.classList.remove('show');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        function toggleMobile() {
            // Anti-double-fire: abaikan jika dipanggil <300ms dari sebelumnya
            var now = Date.now();
            if (now - _lastToggle < 300) return;
            _lastToggle = now;

            if (sidebar.classList.contains('show')) {
                hideMobile();
            } else {
                showMobile();
            }
        }

        /* ── Accordion sub-menu ─────────────────────────────── */
        function initAccordion() {
            document.querySelectorAll('.nav-group-toggle').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (sidebar.classList.contains('collapsed') && window.innerWidth > MOBILE_BP) return;
                    var group = btn.closest('.nav-group');
                    if (!group) return;
                    var isOpen = group.classList.contains('open');
                    document.querySelectorAll('.nav-group.open').forEach(function(g) {
                        g.classList.remove('open');
                    });
                    if (!isOpen) group.classList.add('open');
                });
            });
        }

        /* ── Tooltips ───────────────────────────────────────── */
        function initTooltips() {
            document.querySelectorAll('.nav-item, .nav-sub-item').forEach(function(item) {
                if (!item.getAttribute('data-tooltip')) {
                    var spanEl = item.querySelector('span');
                    if (spanEl) item.setAttribute('data-tooltip', spanEl.textContent.trim());
                }
            });
        }

        /* ── Bind tombol toggle ─────────────────────────────── */

        // Desktop collapse button
        if (collapseBtn) {
            collapseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (window.innerWidth <= MOBILE_BP) return;
                toggleCollapse();
            });
        }

        // Desktop header toggle (≥768px → collapse, <768px → mobile slide)
        if (headerToggle) {
            headerToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // ← KUNCI: hentikan bubble ke document
                if (window.innerWidth <= MOBILE_BP) {
                    toggleMobile();
                } else {
                    toggleCollapse();
                }
            });
        }

        // Mobile hamburger button (hanya muncul di <768px)
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // ← KUNCI: hentikan bubble ke document
                toggleMobile();
            });
        }

        // Overlay: klik overlay = tutup sidebar
        if (overlay) {
            overlay.addEventListener('click', function(e) {
                e.stopPropagation();
                hideMobile();
            });
        }

        // Klik di luar sidebar (bukan tombol toggle) = tutup sidebar
        // Gunakan 'touchstart' untuk responsif di HP, 'click' untuk desktop
        function outsideClickHandler(e) {
            if (window.innerWidth > MOBILE_BP) return;
            if (!sidebar.classList.contains('show')) return;

            // Jangan tutup jika klik di dalam sidebar
            if (sidebar.contains(e.target)) return;

            // Jangan tutup jika klik pada tombol toggle
            if (mobileToggle && (mobileToggle === e.target || mobileToggle.contains(e.target))) return;
            if (headerToggle && (headerToggle === e.target || headerToggle.contains(e.target))) return;

            hideMobile();
        }

        document.addEventListener('click', outsideClickHandler);

        /* ── Init ───────────────────────────────────────────── */
        initAccordion();
        initTooltips();

        // Restore collapsed state di desktop
        if (window.innerWidth > MOBILE_BP) {
            var saved = '';
            try { saved = localStorage.getItem(STORAGE_KEY); } catch(e) {}
            if (saved === '1') setCollapsed(true);
        }

        // Mark sebagai initialized
        sidebar._sidebarInitialized = true;
    }

    // Jalankan saat DOM siap
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebar);
    } else {
        initSidebar();
    }

})();
</script>
