{{-- Tema verde del panel (misma identidad que el catálogo y el inicio de sesión). --}}
<style>
    /* Base */
    .fi-body { background: #fffaf0; }
    .dark .fi-body { background: #14100c; }
    h1, .fi-header-heading, .fi-section-header-heading { font-family: Fredoka, Nunito, sans-serif; }
    .fi-header-heading { color: #2f6628; }
    .dark .fi-header-heading { color: #bde0b1; }
    .fi-section, .fi-ta-ctn, .fi-wi-stats-overview-stat { border-radius: 1.25rem; }
    .fi-btn { border-radius: .9rem; }

    /* Botones primarios: verde de marca sólido con texto blanco (por defecto Filament usa un tono pálido) */
    html:not(.dark) .fi-btn.fi-color-primary:not(.fi-outlined) { background-color: #3a7f30; color: #fff; }
    html:not(.dark) .fi-btn.fi-color-primary:not(.fi-outlined):hover { background-color: #2f6628; color: #fff; }
    html:not(.dark) .fi-btn.fi-color-primary:not(.fi-outlined) .fi-btn-label,
    html:not(.dark) .fi-btn.fi-color-primary:not(.fi-outlined) svg { color: #fff; }
    .dark .fi-btn.fi-color-primary:not(.fi-outlined) { background-color: #4c9a3f; color: #fff; }

    /* Barra lateral verde */
    .fi-sidebar {
        background: linear-gradient(170deg, #4c9a3f 0%, #3a7f30 45%, #2f6628 100%);
        border: 0;
        box-shadow: 4px 0 24px -12px rgb(47 102 40 / .55);
    }
    .dark .fi-sidebar { background: linear-gradient(170deg, #2d5b25 0%, #204519 55%, #142a0f 100%); }
    .fi-sidebar-header { background: transparent; box-shadow: none; border: 0; }
    .fi-sidebar-nav { scrollbar-color: rgba(255,255,255,.3) transparent; }

    .fi-sidebar .fi-logo { color: #fff; }
    .fi-sidebar .fi-logo img { border-radius: 9999px; background: #fffaf0; padding: 3px; box-shadow: 0 6px 16px -6px rgba(0,0,0,.45); }

    .fi-sidebar-group-label, .fi-sidebar-group-btn { color: rgba(255,255,255,.66); text-transform: uppercase; letter-spacing: .07em; font-size: .72rem; font-weight: 700; }
    .fi-sidebar-group-btn svg { color: rgba(255,255,255,.66); }
    .fi-sidebar-item-btn { border-radius: .9rem; transition: background-color .15s ease, transform .15s ease; }
    .fi-sidebar-item-label { color: rgba(255,255,255,.94); font-weight: 600; }
    .fi-sidebar-item-btn svg { color: rgba(255,255,255,.86); }
    .fi-sidebar-item-btn:hover { background: rgba(255,255,255,.15); transform: translateX(2px); }
    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn { background: #fffaf0; box-shadow: 0 8px 18px -10px rgba(0,0,0,.5); }
    .fi-sidebar-item.fi-active .fi-sidebar-item-label, .fi-sidebar-item.fi-active > .fi-sidebar-item-btn svg { color: #2f6628; }
    .dark .fi-sidebar-item.fi-active > .fi-sidebar-item-btn { background: #e8f4e3; }
    .fi-sidebar-footer, .jm-sidebar-version { color: rgba(255,255,255,.62); }

    /* Barra superior */
    .fi-topbar { background: rgba(255, 250, 240, .92); backdrop-filter: blur(8px); border-bottom: 1px solid #f0e2c4; }
    .dark .fi-topbar { background: rgba(20, 16, 12, .9); border-bottom-color: #2b241c; }
    .fi-topbar .fi-icon-btn svg, .fi-topbar-open-sidebar-btn svg, .fi-topbar-close-sidebar-btn svg { color: #3a7f30; }
    .dark .fi-topbar .fi-icon-btn svg { color: #96cb86; }

    /* Tablas y acentos */
    .fi-ta-header-cell { color: #2f6628; }
    .dark .fi-ta-header-cell { color: #bde0b1; }
    .fi-ta-row:hover { background: #f1f8ee; }
    .dark .fi-ta-row:hover { background: rgb(76 154 63 / .12); }
    ::selection { background: #bde0b1; color: #1b3a16; }
</style>
