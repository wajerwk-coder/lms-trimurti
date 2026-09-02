<style id="sidebar-shared-styles">
/* ============================================================
   SHARED SIDEBAR STYLES  —  Admin / Guru / Siswa
   v3: tighter Bootstrap-scope, smooth collapsed transition,
       tooltip polish, role-specific accents
   ============================================================ */

/* ── Variables ───────────────────────────────────────────── */
:root {
    --sb-width:       280px;
    --sb-collapsed-w:  68px;
    --sb-collapsed:    68px;
    --sb-bg-from:     #1e1b4b;
    --sb-bg-to:       #312e81;
    --sb-text:        rgba(224,231,255,.88);
    --sb-text-muted:  rgba(199,210,254,.55);
    --sb-border:      rgba(255,255,255,.08);
    --sb-hover-bg:    rgba(255,255,255,.07);
    --sb-transition:  .25s cubic-bezier(.4,0,.2,1);
}

/* ── Base ────────────────────────────────────────────────── */
.sidebar {
    position: fixed;
    top: 0; left: 0;
    width: var(--sb-width);
    height: 100vh;
    background: linear-gradient(175deg, #1e1b4b 0%, #312e81 50%, #4c1d95 100%);
    display: flex;
    flex-direction: column;
    z-index: 1030;
    overflow: hidden;
    box-shadow: 2px 0 16px rgba(0,0,0,.22);
    flex-shrink: 0;
    transition: width var(--sb-transition);
}

/* ── Brand ───────────────────────────────────────────────── */
.sidebar-brand {
    padding: .875rem 1rem;
    border-bottom: 1px solid var(--sb-border);
    flex-shrink: 0;
    min-height: 64px;
    display: flex;
    align-items: center;
}
.brand-link {
    display: flex;
    align-items: center;
    gap: .75rem;
    text-decoration: none !important;
    overflow: hidden;
    width: 100%;
}
.brand-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem; color: #fff;
    flex-shrink: 0;
    transition: box-shadow var(--sb-transition);
}
.brand-text {
    display: flex;
    flex-direction: column;
    line-height: 1.25;
    overflow: hidden;
    max-width: 180px;
    transition: max-width var(--sb-transition), opacity var(--sb-transition);
}
.brand-name {
    font-size: .875rem; font-weight: 700; color: #f1f5f9;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.brand-role {
    font-size: .7rem; color: var(--sb-text-muted); font-weight: 500;
    white-space: nowrap;
}

/* ── User Card ───────────────────────────────────────────── */
.sidebar-user {
    display: flex;
    align-items: center;
    gap: .7rem;
    padding: .7rem 1rem;
    border-bottom: 1px solid var(--sb-border);
    flex-shrink: 0;
    overflow: hidden;
    transition: padding var(--sb-transition);
}
.sidebar-user img {
    width: 36px; height: 36px;
    border-radius: 50%; object-fit: cover;
    border: 2px solid rgba(255,255,255,.15);
    flex-shrink: 0;
    display: block;
}
.sidebar-user-info {
    display: flex;
    flex-direction: column;
    line-height: 1.3;
    overflow: hidden;
    max-width: 170px;
    transition: max-width var(--sb-transition), opacity var(--sb-transition);
}
.sidebar-user-name {
    font-size: .8rem; font-weight: 600; color: #f1f5f9;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sidebar-user-role {
    font-size: .68rem; color: var(--sb-text-muted);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* ── Navigation ──────────────────────────────────────────── */
.sidebar-nav {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: .5rem .625rem .5rem;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,.1) transparent;
}
.sidebar-nav::-webkit-scrollbar { width: 4px; }
.sidebar-nav::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,.12);
    border-radius: 2px;
}

/* Section label */
.sidebar .nav-section { margin-bottom: .125rem; }
.sidebar .nav-section-label {
    display: block;
    font-size: .62rem; font-weight: 700; letter-spacing: .07em;
    text-transform: uppercase; color: var(--sb-text-muted);
    padding: .65rem .5rem .25rem;
    white-space: nowrap;
    overflow: hidden;
    max-width: 100%;
    transition: max-width var(--sb-transition), opacity var(--sb-transition), padding var(--sb-transition);
}

/* Nav item — scope strictly to .sidebar to avoid Bootstrap .nav-item conflict */
.sidebar a.nav-item,
.sidebar button.nav-item {
    display: flex;
    align-items: center;
    gap: .625rem;
    padding: .48rem .7rem;
    border-radius: 8px;
    margin-bottom: 2px;
    color: var(--sb-text);
    text-decoration: none;
    font-size: .825rem;
    font-weight: 500;
    border: none;
    background: transparent;
    width: 100%;
    cursor: pointer;
    text-align: left;
    transition: background var(--sb-transition), color var(--sb-transition), transform .12s;
    white-space: nowrap;
    overflow: hidden;
    position: relative;
    line-height: 1.4;
}
.sidebar a.nav-item i:first-child,
.sidebar button.nav-item i:first-child {
    width: 18px;
    text-align: center;
    font-size: .875rem;
    flex-shrink: 0;
}
.sidebar a.nav-item > span:not(.nav-badge),
.sidebar button.nav-item > span:not(.nav-badge) {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 160px;
    transition: max-width var(--sb-transition), opacity var(--sb-transition);
}

.sidebar a.nav-item:hover,
.sidebar button.nav-item:hover {
    background: var(--sb-hover-bg);
    color: #fff;
    transform: translateX(2px);
    text-decoration: none;
}
.sidebar a.nav-item.active,
.sidebar button.nav-item.active {
    background: linear-gradient(135deg, rgba(99,102,241,.3), rgba(139,92,246,.2));
    color: #c7d2fe;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(99,102,241,.15);
}
.sidebar a.nav-item.active::before,
.sidebar button.nav-item.active::before {
    content: '';
    position: absolute; left: 0; top: 20%; bottom: 20%;
    width: 3px; border-radius: 2px;
    background: linear-gradient(180deg, #818cf8, #a78bfa);
}

/* Badge */
.sidebar .nav-badge {
    margin-left: auto;
    flex-shrink: 0;
    background: #ef4444;
    color: #fff;
    font-size: .6rem; font-weight: 700;
    min-width: 18px; height: 18px;
    border-radius: 9px; padding: 0 4px;
    display: inline-flex; align-items: center; justify-content: center;
    line-height: 1;
    transition: opacity var(--sb-transition);
}

/* ── Accordion / Sub-menu ────────────────────────────────── */
.sidebar .nav-arrow {
    margin-left: auto !important;
    flex-shrink: 0;
    font-size: .62rem !important;
    width: auto !important;
    transition: transform var(--sb-transition), opacity var(--sb-transition);
}
.sidebar .nav-group.open .nav-arrow { transform: rotate(90deg); }

.sidebar .nav-sub {
    max-height: 0;
    overflow: hidden;
    transition: max-height .3s ease;
    padding-left: 1.4rem;
}
.sidebar .nav-group.open .nav-sub { max-height: 320px; }

.sidebar .nav-sub-item {
    display: flex;
    align-items: center;
    gap: .55rem;
    padding: .38rem .55rem;
    border-radius: 7px;
    margin-bottom: 2px;
    color: var(--sb-text);
    text-decoration: none;
    font-size: .8rem;
    transition: background var(--sb-transition), color var(--sb-transition);
    white-space: nowrap;
    overflow: hidden;
    position: relative;
}
.sidebar .nav-sub-item i {
    width: 14px; text-align: center;
    font-size: .78rem; flex-shrink: 0;
}
.sidebar .nav-sub-item > span {
    overflow: hidden; text-overflow: ellipsis;
    max-width: 140px;
    transition: max-width var(--sb-transition), opacity var(--sb-transition);
}
.sidebar .nav-sub-item:hover {
    background: var(--sb-hover-bg); color: #fff; text-decoration: none;
}
.sidebar .nav-sub-item.active {
    background: linear-gradient(135deg, rgba(99,102,241,.25), rgba(139,92,246,.15));
    color: #c7d2fe;
    font-weight: 600;
}
.sidebar .nav-sub-item.active::before {
    content: '';
    position: absolute; left: -4px; top: 25%; bottom: 25%;
    width: 2px; border-radius: 2px;
    background: linear-gradient(180deg, #818cf8, #a78bfa);
}

/* ── Collapse button ─────────────────────────────────────── */
.sidebar-bottom {
    padding: .625rem .75rem;
    border-top: 1px solid var(--sb-border);
    flex-shrink: 0;
}
.sidebar-collapse-btn {
    display: flex;
    align-items: center;
    gap: .55rem;
    width: 100%;
    padding: .45rem .55rem;
    border: 1px solid var(--sb-border);
    border-radius: 8px;
    background: rgba(255,255,255,.05);
    color: var(--sb-text);
    font-size: .8rem;
    cursor: pointer;
    transition: background var(--sb-transition), justify-content var(--sb-transition);
    overflow: hidden;
    white-space: nowrap;
}
.sidebar-collapse-btn:hover { background: var(--sb-hover-bg); }
.sidebar-collapse-btn > i { flex-shrink: 0; transition: transform var(--sb-transition); }
.collapse-label {
    overflow: hidden;
    max-width: 140px;
    transition: max-width var(--sb-transition), opacity var(--sb-transition);
}

/* ── COLLAPSED STATE ─────────────────────────────────────── */
.sidebar.collapsed { width: var(--sb-collapsed-w); }

/* Hide text labels by collapsing max-width (smooth transition) */
.sidebar.collapsed .brand-text,
.sidebar.collapsed .sidebar-user-info {
    max-width: 0;
    opacity: 0;
    pointer-events: none;
}
.sidebar.collapsed .nav-section-label {
    max-width: 0;
    opacity: 0;
    padding-top: 0;
    padding-bottom: 0;
    pointer-events: none;
}
.sidebar.collapsed a.nav-item > span:not(.nav-badge),
.sidebar.collapsed button.nav-item > span:not(.nav-badge) {
    max-width: 0;
    opacity: 0;
    pointer-events: none;
}
.sidebar.collapsed .nav-badge {
    opacity: 0;
    pointer-events: none;
    max-width: 0;
    overflow: hidden;
}
.sidebar.collapsed .nav-arrow {
    opacity: 0;
    pointer-events: none;
}
.sidebar.collapsed .nav-sub-item > span {
    max-width: 0;
    opacity: 0;
}
.sidebar.collapsed .collapse-label {
    max-width: 0;
    opacity: 0;
    pointer-events: none;
}
.sidebar.collapsed .nav-sub { max-height: 0 !important; }

/* Centering in collapsed mode */
.sidebar.collapsed a.nav-item,
.sidebar.collapsed button.nav-item {
    justify-content: center;
    padding: .55rem;
    gap: 0;
}
.sidebar.collapsed a.nav-item::before,
.sidebar.collapsed button.nav-item::before { display: none; }
.sidebar.collapsed a.nav-item.active,
.sidebar.collapsed button.nav-item.active {
    background: rgba(99,102,241,.25);
    box-shadow: none;
}

.sidebar.collapsed .sidebar-user {
    justify-content: center;
    padding: .7rem .5rem;
    gap: 0;
}
.sidebar.collapsed .sidebar-brand {
    padding: .875rem .5rem;
}
.sidebar.collapsed .brand-link {
    justify-content: center;
    gap: 0;
}
.sidebar.collapsed .sidebar-collapse-btn {
    justify-content: center;
    padding: .45rem;
    gap: 0;
}
.sidebar.collapsed #collapseIcon { transform: rotate(180deg); }

/* ── Tooltip in collapsed mode ───────────────────────────── */
.sidebar.collapsed a.nav-item,
.sidebar.collapsed button.nav-item {
    position: relative;
}
.sidebar.collapsed a.nav-item:hover::after,
.sidebar.collapsed button.nav-item:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    left: calc(var(--sb-collapsed-w) + 8px);
    top: 50%; transform: translateY(-50%);
    background: #1e293b;
    color: #f1f5f9;
    padding: .3rem .65rem;
    border-radius: 6px;
    font-size: .75rem;
    font-weight: 500;
    white-space: nowrap;
    box-shadow: 0 4px 14px rgba(0,0,0,.28);
    z-index: 9999;
    pointer-events: none;
    border: 1px solid rgba(255,255,255,.1);
}

/* ── Overlay (mobile) ────────────────────────────────────── */
.sidebar-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.5);
    z-index: 1025;
    opacity: 0;
    visibility: hidden;
    transition: opacity var(--sb-transition), visibility var(--sb-transition);
    pointer-events: none;
}
.sidebar-overlay.active {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}

/* ── Mobile ──────────────────────────────────────────────── */
@media (max-width: 768px) {
    .sidebar {
        position: fixed !important;
        top: 0; left: 0; bottom: 0;
        transform: translateX(-100%);
        width: var(--sb-width) !important;
        transition: transform var(--sb-transition) !important;
        z-index: 1050 !important;
    }
    .sidebar.show { transform: translateX(0); }
    .sidebar.collapsed { width: var(--sb-width) !important; }

    /* Reset collapsed styles on mobile */
    .sidebar.collapsed .brand-text,
    .sidebar.collapsed .sidebar-user-info,
    .sidebar.collapsed .nav-section-label,
    .sidebar.collapsed a.nav-item > span:not(.nav-badge),
    .sidebar.collapsed button.nav-item > span:not(.nav-badge),
    .sidebar.collapsed .nav-badge,
    .sidebar.collapsed .nav-arrow,
    .sidebar.collapsed .collapse-label {
        max-width: unset !important;
        opacity: 1 !important;
        pointer-events: auto !important;
    }
    .sidebar.collapsed .nav-sub { max-height: unset !important; }
    .sidebar.collapsed a.nav-item,
    .sidebar.collapsed button.nav-item {
        justify-content: flex-start;
        padding: .48rem .7rem;
        gap: .625rem;
    }
    .sidebar.collapsed a.nav-item:hover::after,
    .sidebar.collapsed button.nav-item:hover::after { display: none; }
    .sidebar.collapsed .sidebar-brand { padding: .875rem 1rem; }
    .sidebar.collapsed .brand-link { justify-content: flex-start; gap: .75rem; }
    .sidebar.collapsed .sidebar-user { justify-content: flex-start; padding: .7rem 1rem; gap: .7rem; }
    .sidebar.collapsed .sidebar-collapse-btn { justify-content: flex-start; padding: .45rem .55rem; gap: .55rem; }
    .sidebar.collapsed #collapseIcon { transform: none; }
}

/* ═══════════════════════════════════════════════════════════
   ROLE-SPECIFIC ACCENT COLOURS
   ═══════════════════════════════════════════════════════════ */

/* ── Admin — blue-indigo ─────────────────────────────────── */
.sidebar[data-role="admin"] {
    background: linear-gradient(175deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
}
.sidebar[data-role="admin"] a.nav-item.active,
.sidebar[data-role="admin"] button.nav-item.active {
    background: linear-gradient(135deg, rgba(99,102,241,.32), rgba(139,92,246,.22));
    color: #c7d2fe;
}
.sidebar[data-role="admin"] a.nav-item.active::before,
.sidebar[data-role="admin"] button.nav-item.active::before,
.sidebar[data-role="admin"] .nav-sub-item.active::before {
    background: linear-gradient(180deg, #818cf8, #a78bfa);
}
.sidebar[data-role="admin"] .nav-sub-item.active {
    background: linear-gradient(135deg, rgba(99,102,241,.22), rgba(139,92,246,.14));
    color: #c7d2fe;
}
.sidebar[data-role="admin"] a.nav-item:hover,
.sidebar[data-role="admin"] button.nav-item:hover { color: #e0e7ff; }
.sidebar[data-role="admin"] .nav-section-label { color: rgba(199,210,254,.5); }
.sidebar[data-role="admin"] .sidebar-user-role  { color: rgba(199,210,254,.65); }
.sidebar[data-role="admin"] .brand-role          { color: rgba(199,210,254,.6); }

/* ── Guru — teal ─────────────────────────────────────────── */
.sidebar[data-role="guru"] {
    background: linear-gradient(175deg, #042f2e 0%, #134e4a 50%, #0f766e 100%);
}
.sidebar[data-role="guru"] a.nav-item.active,
.sidebar[data-role="guru"] button.nav-item.active {
    background: linear-gradient(135deg, rgba(15,118,110,.38), rgba(8,145,178,.28));
    color: #99f6e4;
}
.sidebar[data-role="guru"] a.nav-item.active::before,
.sidebar[data-role="guru"] button.nav-item.active::before,
.sidebar[data-role="guru"] .nav-sub-item.active::before {
    background: linear-gradient(180deg, #2dd4bf, #22d3ee);
}
.sidebar[data-role="guru"] .nav-sub-item.active {
    background: linear-gradient(135deg, rgba(15,118,110,.28), rgba(8,145,178,.18));
    color: #99f6e4;
}
.sidebar[data-role="guru"] a.nav-item:hover,
.sidebar[data-role="guru"] button.nav-item:hover { color: #ccfbf1; }
.sidebar[data-role="guru"] .nav-section-label { color: rgba(153,246,228,.52); }
.sidebar[data-role="guru"] .sidebar-user-role  { color: rgba(153,246,228,.7); }
.sidebar[data-role="guru"] .brand-role          { color: rgba(153,246,228,.6); }

/* ── Siswa — purple ──────────────────────────────────────── */
.sidebar[data-role="siswa"] {
    background: linear-gradient(175deg, #1e1035 0%, #3b0764 50%, #4c1d95 100%);
}
.sidebar[data-role="siswa"] a.nav-item.active,
.sidebar[data-role="siswa"] button.nav-item.active {
    background: linear-gradient(135deg, rgba(124,58,237,.38), rgba(167,139,250,.28));
    color: #e9d5ff;
}
.sidebar[data-role="siswa"] a.nav-item.active::before,
.sidebar[data-role="siswa"] button.nav-item.active::before,
.sidebar[data-role="siswa"] .nav-sub-item.active::before {
    background: linear-gradient(180deg, #a78bfa, #c084fc);
}
.sidebar[data-role="siswa"] .nav-sub-item.active {
    background: linear-gradient(135deg, rgba(124,58,237,.28), rgba(167,139,250,.18));
    color: #e9d5ff;
}
.sidebar[data-role="siswa"] a.nav-item:hover,
.sidebar[data-role="siswa"] button.nav-item:hover { color: #f3e8ff; }
.sidebar[data-role="siswa"] .nav-section-label { color: rgba(196,181,253,.52); }
.sidebar[data-role="siswa"] .sidebar-user-role  { color: rgba(196,181,253,.7); }
.sidebar[data-role="siswa"] .brand-role          { color: rgba(196,181,253,.6); }
</style>
