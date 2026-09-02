<script>
// Tunggu DOM benar-benar siap — pakai window.onload sebagai fallback
(function() {
    'use strict';

    function initSidebar() {
        var STORAGE_KEY = 'lms_sidebar_collapsed';
        var MOBILE_BP   = 768;

        var sidebar      = document.getElementById('sidebar');
        var overlay      = document.getElementById('sidebarOverlay');
        var lmsMain      = document.getElementById('lms-main');
        var collapseBtn  = document.getElementById('sidebarCollapseBtn');
        var collapseIcon = document.getElementById('collapseIcon');
        var headerToggle = document.getElementById('sidebarToggle');
        var mobileToggle = document.getElementById('mobileSidebarToggle');

        if (!sidebar) return;

    /* ── Collapse helpers ──────────────────────────────────── */
    function setCollapsed(collapsed) {
        sidebar.classList.toggle('collapsed', collapsed);
        /* Sync the main column margin via the sb-collapsed class */
        if (lmsMain) lmsMain.classList.toggle('sb-collapsed', collapsed);
        if (collapseIcon) {
            collapseIcon.style.transform = collapsed ? 'rotate(180deg)' : 'rotate(0deg)';
        }
        try { localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0'); } catch(e) {}
    }

    function toggleCollapse() {
        setCollapsed(!sidebar.classList.contains('collapsed'));
    }

    /* ── Mobile show/hide ──────────────────────────────────── */
    function showMobile() {
        sidebar.classList.add('show');
        if (overlay) {
            overlay.classList.add('active');
        }
        document.body.style.overflow = 'hidden';
    }

    function hideMobile() {
        sidebar.classList.remove('show');
        if (overlay) {
            overlay.classList.remove('active');
        }
        document.body.style.overflow = '';
    }

    /* ── Accordion sub-menu ───────────────────────────────── */
    function initAccordion() {
        document.querySelectorAll('.nav-group-toggle').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
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

    /* ── Add tooltip data-attributes ─────────────────────── */
    function initTooltips() {
        document.querySelectorAll('.nav-item, .nav-sub-item').forEach(function(item) {
            if (!item.getAttribute('data-tooltip')) {
                var spanEl = item.querySelector('span');
                if (spanEl) item.setAttribute('data-tooltip', spanEl.textContent.trim());
            }
        });
    }

    /* ── Bind events ──────────────────────────────────────── */
    if (collapseBtn) {
        collapseBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (window.innerWidth <= MOBILE_BP) return;
            toggleCollapse();
        });
    }

    if (headerToggle) {
        headerToggle.addEventListener('click', function(e) {
            e.preventDefault();
            if (window.innerWidth <= MOBILE_BP) {
                sidebar.classList.contains('show') ? hideMobile() : showMobile();
            } else {
                toggleCollapse();
            }
        });
    }

    if (mobileToggle) {
        mobileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.contains('show') ? hideMobile() : showMobile();
        });
    }

    if (overlay) {
        overlay.addEventListener('click', hideMobile);
    }

    document.addEventListener('click', function(e) {
        if (window.innerWidth <= MOBILE_BP && sidebar.classList.contains('show')) {
            if (!sidebar.contains(e.target) &&
                e.target !== mobileToggle &&
                e.target !== headerToggle &&
                !mobileToggle?.contains(e.target) &&
                !headerToggle?.contains(e.target)) {
                hideMobile();
            }
        }
    });

    /* ── Restore state & init ─────────────────────────────── */
    function init() {
        initAccordion();
        initTooltips();

        if (window.innerWidth > MOBILE_BP) {
            var saved = '';
            try { saved = localStorage.getItem(STORAGE_KEY); } catch(e) {}
            if (saved === '1') setCollapsed(true);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    } // end initSidebar

    // Jalankan saat DOM siap, dengan multiple fallback untuk HP
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebar);
    } else {
        initSidebar();
    }
    // Fallback untuk browser mobile yang lambat
    window.addEventListener('load', function() {
        var sb = document.getElementById('sidebar');
        if (sb && !sb._sidebarInitialized) {
            initSidebar();
        }
    });

})();
</script>
