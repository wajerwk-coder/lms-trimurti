<script>
(function() {
    'use strict';

    var STORAGE_KEY = 'lms_sidebar_collapsed';
    var MOBILE_BP   = 768;
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

        /* ── Desktop collapse ───────────────────────────────── */
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
            var now = Date.now();
            if (now - _lastToggle < 350) return;
            _lastToggle = now;
            sidebar.classList.contains('show') ? hideMobile() : showMobile();
        }

        /* ── Accordion ──────────────────────────────────────── */
        function initAccordion() {
            document.querySelectorAll('.nav-group-toggle').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (sidebar.classList.contains('collapsed') && window.innerWidth > MOBILE_BP) return;
                    var group = btn.closest('.nav-group');
                    if (!group) return;
                    var isOpen = group.classList.contains('open');
                    document.querySelectorAll('.nav-group.open').forEach(function(g) { g.classList.remove('open'); });
                    if (!isOpen) group.classList.add('open');
                });
            });
        }

        /* ── Tooltips ───────────────────────────────────────── */
        function initTooltips() {
            document.querySelectorAll('.nav-item, .nav-sub-item').forEach(function(item) {
                if (!item.getAttribute('data-tooltip')) {
                    var s = item.querySelector('span');
                    if (s) item.setAttribute('data-tooltip', s.textContent.trim());
                }
            });
        }

        /* ── Event Bindings ─────────────────────────────────── */

        // Desktop collapse btn
        if (collapseBtn) {
            collapseBtn.addEventListener('click', function(e) {
                e.preventDefault(); e.stopPropagation();
                if (window.innerWidth <= MOBILE_BP) return;
                toggleCollapse();
            });
        }

        // Header toggle (desktop: collapse | mobile: slide)
        if (headerToggle) {
            headerToggle.addEventListener('click', function(e) {
                e.preventDefault(); e.stopPropagation();
                window.innerWidth <= MOBILE_BP ? toggleMobile() : toggleCollapse();
            });
        }

        // Mobile hamburger button
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function(e) {
                e.preventDefault(); e.stopPropagation();
                toggleMobile();
            });
        }

        // Overlay klik = tutup
        if (overlay) {
            overlay.addEventListener('click', function(e) {
                e.stopPropagation();
                hideMobile();
            });
        }

        // Klik di luar sidebar = tutup (hanya mobile)
        document.addEventListener('click', function(e) {
            if (window.innerWidth > MOBILE_BP || !sidebar.classList.contains('show')) return;
            if (sidebar.contains(e.target)) return;
            if (mobileToggle && mobileToggle.contains(e.target)) return;
            if (headerToggle && headerToggle.contains(e.target)) return;
            hideMobile();
        });

        /* ── Init ───────────────────────────────────────────── */
        initAccordion();
        initTooltips();

        if (window.innerWidth > MOBILE_BP) {
            var saved = '';
            try { saved = localStorage.getItem(STORAGE_KEY); } catch(e) {}
            if (saved === '1') setCollapsed(true);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebar);
    } else {
        initSidebar();
    }

})();
</script>
