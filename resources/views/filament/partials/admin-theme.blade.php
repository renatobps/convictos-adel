<style>
    :root {
        --convictos-navy: #121F41;
        --convictos-red: #CF3136;
        --convictos-white: #FFFFFF;
        --convictos-surface: #F4F5F8;
    }

    /* Área principal clara */
    html:not(.dark) .fi-body {
        background-color: var(--convictos-surface) !important;
        color: var(--convictos-navy);
    }

    html:not(.dark) .fi-topbar {
        background-color: var(--convictos-white) !important;
        border-bottom: 1px solid rgba(18, 31, 65, 0.08);
    }

    html:not(.dark) .fi-main-ctn {
        background-color: var(--convictos-surface);
    }

    /* Sidebar navy */
    html:not(.dark) .fi-sidebar.fi-main-sidebar {
        background-color: var(--convictos-navy) !important;
        border-inline-end: 1px solid rgba(255, 255, 255, 0.06);
    }

    html:not(.dark) .fi-sidebar-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    html:not(.dark) .fi-sidebar-group-label {
        color: rgba(255, 255, 255, 0.5) !important;
        font-size: 0.6875rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    html:not(.dark) .fi-sidebar-group-btn .fi-icon,
    html:not(.dark) .fi-sidebar-item-btn > .fi-icon {
        color: rgba(255, 255, 255, 0.6) !important;
    }

    html:not(.dark) .fi-sidebar-item-label {
        color: rgba(255, 255, 255, 0.92) !important;
    }

    html:not(.dark) .fi-sidebar-item.fi-sidebar-item-has-url > .fi-sidebar-item-btn:hover,
    html:not(.dark) .fi-sidebar-item.fi-sidebar-item-has-url > .fi-sidebar-item-btn:focus-visible,
    html:not(.dark) .fi-sidebar-group.fi-collapsible > .fi-sidebar-group-btn:hover {
        background-color: rgba(255, 255, 255, 0.07) !important;
    }

    html:not(.dark) .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
        background-color: rgba(255, 255, 255, 0.1) !important;
        box-shadow: inset 3px 0 0 var(--convictos-red);
    }

    html:not(.dark) .fi-sidebar-item.fi-active > .fi-sidebar-item-btn > .fi-icon,
    html:not(.dark) .fi-sidebar-item.fi-active > .fi-sidebar-item-btn > .fi-sidebar-item-label {
        color: var(--convictos-white) !important;
    }

    html:not(.dark) .fi-sidebar-item.fi-active > .fi-sidebar-item-btn > .fi-sidebar-item-grouped-border > .fi-sidebar-item-grouped-border-part {
        background-color: var(--convictos-red) !important;
    }

    html:not(.dark) .fi-sidebar .fi-icon-btn .fi-icon {
        color: rgba(255, 255, 255, 0.65);
    }

    html:not(.dark) .fi-sidebar .fi-icon-btn:hover {
        background-color: rgba(255, 255, 255, 0.08);
    }

    html:not(.dark) .fi-sidebar-group.fi-active .fi-sidebar-group-dropdown-trigger-btn .fi-icon {
        color: var(--convictos-red) !important;
    }

    /* Login */
    html:not(.dark) .fi-simple-layout {
        background: linear-gradient(160deg, var(--convictos-navy) 0%, #1a2d5a 45%, var(--convictos-navy) 100%);
    }

    html:not(.dark) .fi-simple-main {
        background-color: var(--convictos-white);
        border-radius: 1rem;
        box-shadow: 0 24px 48px rgba(18, 31, 65, 0.25);
        padding: 2rem;
    }

    html:not(.dark) .fi-simple-header-heading {
        color: var(--convictos-navy) !important;
    }

    html:not(.dark) .fi-simple-header-subheading {
        color: rgba(18, 31, 65, 0.6) !important;
    }
</style>
