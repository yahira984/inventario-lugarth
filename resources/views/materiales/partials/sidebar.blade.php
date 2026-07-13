<style>
    :root {
        --sidebar-width: 278px;
        --sidebar-collapsed-width: 88px;

        --sidebar-bg-start: #0b1728;
        --sidebar-bg-middle: #10253d;
        --sidebar-bg-end: #123252;

        --sidebar-surface: rgba(255, 255, 255, 0.075);
        --sidebar-surface-hover: rgba(255, 255, 255, 0.12);
        --sidebar-border: rgba(255, 255, 255, 0.11);

        --sidebar-text: #f4f8fc;
        --sidebar-muted: #9fb2c9;

        --sidebar-blue: #3b82f6;
        --sidebar-blue-light: #60a5fa;
        --sidebar-cyan: #22d3ee;
        --sidebar-green: #34d399;
        --sidebar-red: #fb7185;

        --sidebar-shadow: 14px 0 40px rgba(4, 12, 24, 0.18);
    }

    * {
        box-sizing: border-box;
    }

    .app-shell {
        min-height: 100vh;
        display: grid;
        grid-template-columns: var(--sidebar-width) minmax(0, 1fr);
        transition: grid-template-columns 0.28s ease;
    }

    .app-shell.sidebar-collapsed {
        grid-template-columns: var(--sidebar-collapsed-width) minmax(0, 1fr);
    }

    /*
    |--------------------------------------------------------------------------
    | Barra lateral
    |--------------------------------------------------------------------------
    */

    .sidebar {
        position: sticky;
        top: 0;
        height: 100vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        padding: 16px 14px;
        color: var(--sidebar-text);
        background:
            radial-gradient(
                circle at 20% 4%,
                rgba(59, 130, 246, 0.25),
                transparent 26%
            ),
            radial-gradient(
                circle at 110% 50%,
                rgba(34, 211, 238, 0.12),
                transparent 32%
            ),
            linear-gradient(
                165deg,
                var(--sidebar-bg-start) 0%,
                var(--sidebar-bg-middle) 52%,
                var(--sidebar-bg-end) 100%
            );
        border-right: 1px solid rgba(255, 255, 255, 0.06);
        box-shadow: var(--sidebar-shadow);
        z-index: 1200;
        transition:
            width 0.28s ease,
            padding 0.28s ease,
            transform 0.28s ease;
    }

    .sidebar::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        opacity: 0.25;
        background-image:
            linear-gradient(
                rgba(255, 255, 255, 0.025) 1px,
                transparent 1px
            ),
            linear-gradient(
                90deg,
                rgba(255, 255, 255, 0.025) 1px,
                transparent 1px
            );
        background-size: 24px 24px;
    }

    .sidebar::after {
        content: "";
        position: absolute;
        top: 0;
        right: 0;
        width: 1px;
        height: 100%;
        background: linear-gradient(
            transparent,
            rgba(96, 165, 250, 0.5),
            transparent
        );
    }

    .sidebar > * {
        position: relative;
        z-index: 2;
    }

    /*
    |--------------------------------------------------------------------------
    | Encabezado y marca
    |--------------------------------------------------------------------------
    */

    .sidebar-top {
        min-height: 72px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 4px 3px 17px;
        border-bottom: 1px solid var(--sidebar-border);
    }

    .sidebar-brand-wrapper {
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .sidebar-logo {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        border-radius: 13px;
        color: #ffffff;
        background:
            linear-gradient(
                145deg,
                var(--sidebar-blue-light),
                #2563eb 55%,
                #1d4ed8
            );
        border: 1px solid rgba(255, 255, 255, 0.25);
        box-shadow:
            0 10px 24px rgba(37, 99, 235, 0.32),
            inset 0 1px 0 rgba(255, 255, 255, 0.3);
    }

    .sidebar-logo::before {
        content: "";
        position: absolute;
        width: 30px;
        height: 30px;
        top: -13px;
        right: -10px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.22);
    }

    .sidebar-logo svg {
        width: 24px;
        height: 24px;
        position: relative;
        z-index: 1;
    }

    .sidebar-brand {
        min-width: 0;
        overflow: hidden;
    }

    .sidebar-brand strong,
    .sidebar-brand span,
    .sidebar-user-info,
    .nav-text,
    .sidebar-section-title {
        transition:
            opacity 0.18s ease,
            width 0.22s ease,
            transform 0.22s ease;
    }

    .sidebar-brand strong {
        display: block;
        overflow: hidden;
        color: #ffffff;
        font-size: 18px;
        font-weight: 900;
        letter-spacing: -0.02em;
        line-height: 1.2;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sidebar-brand span {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 5px;
        color: var(--sidebar-muted);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .sidebar-brand span::before {
        content: "";
        width: 7px;
        height: 7px;
        flex: 0 0 7px;
        border-radius: 50%;
        background: var(--sidebar-green);
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.12);
    }

    /*
    |--------------------------------------------------------------------------
    | Botón contraer
    |--------------------------------------------------------------------------
    */

    .sidebar-toggle {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        border: 1px solid var(--sidebar-border);
        border-radius: 11px;
        color: #eaf2fb;
        background: var(--sidebar-surface);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
        font-family: inherit;
        cursor: pointer;
        transition:
            background 0.2s ease,
            border-color 0.2s ease,
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }

    .sidebar-toggle:hover {
        background: var(--sidebar-surface-hover);
        border-color: rgba(96, 165, 250, 0.45);
        transform: translateY(-1px);
        box-shadow: 0 7px 18px rgba(0, 0, 0, 0.18);
    }

    .sidebar-toggle:active {
        transform: scale(0.96);
    }

    .sidebar-toggle svg {
        width: 19px;
        height: 19px;
        transition: transform 0.28s ease;
    }

    .app-shell.sidebar-collapsed .sidebar-toggle svg {
        transform: rotate(180deg);
    }

    /*
    |--------------------------------------------------------------------------
    | Navegación
    |--------------------------------------------------------------------------
    */

    .sidebar-navigation {
        min-height: 0;
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 18px 0 12px;
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.18) transparent;
    }

    .sidebar-navigation::-webkit-scrollbar {
        width: 5px;
    }

    .sidebar-navigation::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar-navigation::-webkit-scrollbar-thumb {
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.16);
    }

    .sidebar-section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 0 10px 10px;
        color: #7188a5;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .sidebar-section-title::after {
        content: "";
        width: 100%;
        height: 1px;
        background: linear-gradient(
            90deg,
            rgba(255, 255, 255, 0.13),
            transparent
        );
    }

    .sidebar-nav {
        display: grid;
        gap: 7px;
    }

    .sidebar-link,
    .sidebar-logout {
        width: 100%;
        min-height: 48px;
        display: flex;
        align-items: center;
        position: relative;
        gap: 11px;
        padding: 8px 11px;
        overflow: hidden;
        border: 1px solid transparent;
        border-radius: 12px;
        color: #dce8f5;
        background: transparent;
        font-family: inherit;
        font-size: 13px;
        font-weight: 800;
        text-align: left;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
        transition:
            color 0.2s ease,
            background 0.2s ease,
            border-color 0.2s ease,
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }

    .sidebar-link::before {
        content: "";
        width: 3px;
        height: 24px;
        position: absolute;
        left: 0;
        top: 50%;
        border-radius: 0 4px 4px 0;
        background: linear-gradient(
            var(--sidebar-cyan),
            var(--sidebar-blue-light)
        );
        opacity: 0;
        transform: translateY(-50%) scaleY(0.2);
        transition:
            opacity 0.2s ease,
            transform 0.2s ease;
    }

    .sidebar-link::after {
        content: "";
        width: 60px;
        height: 60px;
        position: absolute;
        right: -34px;
        top: -37px;
        border-radius: 50%;
        pointer-events: none;
        opacity: 0;
        background: rgba(96, 165, 250, 0.18);
        transition: opacity 0.2s ease;
    }

    .sidebar-link:hover {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.075);
        border-color: rgba(255, 255, 255, 0.08);
        transform: translateX(3px);
    }

    .sidebar-link:hover::after {
        opacity: 1;
    }

    .sidebar-link.active {
        color: #ffffff;
        background:
            linear-gradient(
                90deg,
                rgba(37, 99, 235, 0.35),
                rgba(59, 130, 246, 0.15)
            );
        border-color: rgba(96, 165, 250, 0.28);
        box-shadow:
            0 9px 22px rgba(2, 10, 24, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.08);
    }

    .sidebar-link.active::before {
        opacity: 1;
        transform: translateY(-50%) scaleY(1);
    }

    .nav-mark {
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 1;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 9px;
        color: #dce9f8;
        background: rgba(255, 255, 255, 0.08);
        transition:
            color 0.2s ease,
            background 0.2s ease,
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }

    .nav-mark svg {
        width: 17px;
        height: 17px;
        stroke-width: 2;
    }

    .sidebar-link:hover .nav-mark,
    .sidebar-link.active .nav-mark {
        color: #ffffff;
        background: linear-gradient(
            145deg,
            rgba(96, 165, 250, 0.9),
            rgba(37, 99, 235, 0.9)
        );
        border-color: rgba(255, 255, 255, 0.23);
        box-shadow: 0 7px 16px rgba(37, 99, 235, 0.25);
        transform: scale(1.04);
    }

    .nav-text {
        min-width: 0;
        position: relative;
        z-index: 1;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .nav-arrow {
        width: 16px;
        height: 16px;
        margin-left: auto;
        color: #7890ac;
        opacity: 0;
        transform: translateX(-5px);
        transition:
            opacity 0.2s ease,
            transform 0.2s ease,
            color 0.2s ease;
    }

    .sidebar-link:hover .nav-arrow,
    .sidebar-link.active .nav-arrow {
        color: #dceaff;
        opacity: 1;
        transform: translateX(0);
    }

    /*
    |--------------------------------------------------------------------------
    | Pie y usuario
    |--------------------------------------------------------------------------
    */

    .sidebar-footer {
        margin-top: auto;
        padding-top: 15px;
        border-top: 1px solid var(--sidebar-border);
    }

    .sidebar-user-card {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        padding: 11px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 13px;
        background: rgba(255, 255, 255, 0.055);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
    }

    .sidebar-avatar {
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        border-radius: 11px;
        color: #ffffff;
        background: linear-gradient(
            145deg,
            #0891b2,
            #2563eb
        );
        font-size: 14px;
        font-weight: 900;
        box-shadow: 0 6px 16px rgba(8, 145, 178, 0.22);
    }

    .sidebar-avatar::after {
        content: "";
        width: 9px;
        height: 9px;
        position: absolute;
        right: -2px;
        bottom: -2px;
        border: 2px solid #112841;
        border-radius: 50%;
        background: var(--sidebar-green);
    }

    .sidebar-user-info {
        min-width: 0;
        overflow: hidden;
    }

    .sidebar-user-name {
        overflow: hidden;
        color: #f5f9fe;
        font-size: 12px;
        font-weight: 900;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sidebar-user-email {
        margin-top: 3px;
        overflow: hidden;
        color: var(--sidebar-muted);
        font-size: 10px;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sidebar-logout {
        color: #ffd4d9;
    }

    .sidebar-logout:hover {
        color: #ffffff;
        background: rgba(244, 63, 94, 0.17);
        border-color: rgba(251, 113, 133, 0.24);
        transform: translateX(3px);
    }

    .sidebar-logout .nav-mark {
        color: #ffc1ca;
        background: rgba(244, 63, 94, 0.12);
        border-color: rgba(251, 113, 133, 0.13);
    }

    .sidebar-logout:hover .nav-mark {
        color: #ffffff;
        background: linear-gradient(
            145deg,
            #fb7185,
            #e11d48
        );
        box-shadow: 0 7px 16px rgba(225, 29, 72, 0.22);
    }

    /*
    |--------------------------------------------------------------------------
    | Contenido principal
    |--------------------------------------------------------------------------
    */

    .app-content {
        min-width: 0;
        padding: 28px 18px;
        transition: padding 0.25s ease;
    }

    /*
    |--------------------------------------------------------------------------
    | Estado contraído
    |--------------------------------------------------------------------------
    */

    .app-shell.sidebar-collapsed .sidebar {
        padding-left: 11px;
        padding-right: 11px;
    }

    .app-shell.sidebar-collapsed .sidebar-top {
        flex-direction: column;
        justify-content: center;
        gap: 10px;
        padding-bottom: 14px;
    }

    .app-shell.sidebar-collapsed .sidebar-brand-wrapper {
        justify-content: center;
    }

    .app-shell.sidebar-collapsed .sidebar-brand,
    .app-shell.sidebar-collapsed .sidebar-user-info,
    .app-shell.sidebar-collapsed .nav-text,
    .app-shell.sidebar-collapsed .nav-arrow,
    .app-shell.sidebar-collapsed .sidebar-section-title {
        width: 0;
        max-width: 0;
        margin: 0;
        overflow: hidden;
        opacity: 0;
        transform: translateX(-7px);
        pointer-events: none;
    }

    .app-shell.sidebar-collapsed .sidebar-navigation {
        overflow: visible;
    }

    .app-shell.sidebar-collapsed .sidebar-nav {
        gap: 9px;
    }

    .app-shell.sidebar-collapsed .sidebar-link,
    .app-shell.sidebar-collapsed .sidebar-logout {
        justify-content: center;
        padding-left: 8px;
        padding-right: 8px;
        overflow: visible;
    }

    .app-shell.sidebar-collapsed .sidebar-link:hover,
    .app-shell.sidebar-collapsed .sidebar-logout:hover {
        transform: translateY(-1px);
    }

    .app-shell.sidebar-collapsed .sidebar-link::before {
        left: -11px;
    }

    .app-shell.sidebar-collapsed .sidebar-user-card {
        justify-content: center;
        padding-left: 8px;
        padding-right: 8px;
    }

    /*
    |--------------------------------------------------------------------------
    | Tooltip al contraer
    |--------------------------------------------------------------------------
    */

    .app-shell.sidebar-collapsed .sidebar-link::after,
    .app-shell.sidebar-collapsed .sidebar-logout::after {
        content: attr(data-label);
        width: auto;
        height: auto;
        min-width: max-content;
        position: absolute;
        top: 50%;
        right: auto;
        left: calc(100% + 14px);
        z-index: 1500;
        padding: 8px 11px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        color: #ffffff;
        background: #102238;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.26);
        font-size: 11px;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
        opacity: 0;
        transform: translate(-7px, -50%);
        pointer-events: none;
        transition:
            opacity 0.18s ease,
            transform 0.18s ease;
    }

    .app-shell.sidebar-collapsed .sidebar-link:hover::after,
    .app-shell.sidebar-collapsed .sidebar-logout:hover::after {
        opacity: 1;
        transform: translate(0, -50%);
    }

    /*
    |--------------------------------------------------------------------------
    | Fondo para menú móvil
    |--------------------------------------------------------------------------
    */

    .sidebar-mobile-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1100;
        background: rgba(5, 12, 22, 0.62);
        backdrop-filter: blur(3px);
    }

    /*
    |--------------------------------------------------------------------------
    | Diseño adaptable
    |--------------------------------------------------------------------------
    */

    @media (max-width: 860px) {
        .app-shell,
        .app-shell.sidebar-collapsed {
            display: block;
        }

        .sidebar {
            width: min(310px, 88vw);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding: 16px 14px;
            transform: translateX(-105%);
            box-shadow: 18px 0 45px rgba(3, 10, 20, 0.38);
        }

        .app-shell.mobile-menu-open .sidebar {
            transform: translateX(0);
        }

        .app-shell.mobile-menu-open .sidebar-mobile-overlay {
            display: block;
        }

        .sidebar-top {
            flex-direction: row;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 17px;
            border-bottom: 1px solid var(--sidebar-border);
        }

        .sidebar-navigation {
            display: block;
        }

        .sidebar-nav {
            display: grid;
            grid-template-columns: 1fr;
        }

        .sidebar-footer {
            display: block;
        }

        .app-shell.sidebar-collapsed .sidebar-brand,
        .app-shell.sidebar-collapsed .sidebar-user-info,
        .app-shell.sidebar-collapsed .nav-text,
        .app-shell.sidebar-collapsed .nav-arrow,
        .app-shell.sidebar-collapsed .sidebar-section-title {
            width: auto;
            max-width: none;
            margin: initial;
            overflow: visible;
            opacity: 1;
            transform: none;
            pointer-events: auto;
        }

        .app-shell.sidebar-collapsed .sidebar-top {
            flex-direction: row;
            justify-content: space-between;
        }

        .app-shell.sidebar-collapsed .sidebar-link,
        .app-shell.sidebar-collapsed .sidebar-logout {
            justify-content: flex-start;
            padding-left: 11px;
            padding-right: 11px;
        }

        .app-shell.sidebar-collapsed .sidebar-user-card {
            justify-content: flex-start;
            padding-left: 11px;
            padding-right: 11px;
        }

        .app-shell.sidebar-collapsed .sidebar-link::after,
        .app-shell.sidebar-collapsed .sidebar-logout::after {
            display: none;
        }

        .app-shell.sidebar-collapsed .sidebar-toggle svg {
            transform: none;
        }

        .app-content {
            width: 100%;
            padding: 18px 12px;
        }
    }

    @media (max-width: 560px) {
        .sidebar {
            width: min(300px, 90vw);
        }

        .app-content {
            padding: 12px 8px;
        }

        .sidebar-logo {
            width: 41px;
            height: 41px;
            flex-basis: 41px;
        }

        .sidebar-brand strong {
            font-size: 17px;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Accesibilidad
    |--------------------------------------------------------------------------
    */

    .sidebar-link:focus-visible,
    .sidebar-logout:focus-visible,
    .sidebar-toggle:focus-visible {
        outline: 3px solid rgba(96, 165, 250, 0.55);
        outline-offset: 2px;
    }

    @media (prefers-reduced-motion: reduce) {
        .sidebar,
        .sidebar-link,
        .sidebar-logout,
        .sidebar-toggle,
        .sidebar-toggle svg,
        .nav-mark,
        .nav-text,
        .sidebar-brand,
        .sidebar-user-info {
            transition: none !important;
        }
    }
</style>

<aside class="sidebar" aria-label="Menú principal">
    <div class="sidebar-top">
        <div class="sidebar-brand-wrapper">
            <div class="sidebar-logo" aria-hidden="true">
                <svg
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3 10.5 12 4l9 6.5M5 9.5V20h14V9.5M8 20v-6h8v6M8 10h.01M12 10h.01M16 10h.01"
                    />
                </svg>
            </div>

            <div class="sidebar-brand">
                <strong>Inventario Lugarth</strong>
                <span>Almacén y entradas</span>
            </div>
        </div>

        <button
            type="button"
            class="sidebar-toggle"
            id="sidebarToggle"
            aria-label="Alternar menú lateral"
            aria-expanded="true"
            title="Contraer menú"
        >
            <svg
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m15 18-6-6 6-6"
                />
            </svg>
        </button>
    </div>

    <div class="sidebar-navigation">
        <div class="sidebar-section-title">
            Navegación
        </div>

        <nav class="sidebar-nav" id="sidebarNav">
            <a
                href="{{ route('materiales.index') }}"
                title="Inventario"
                data-label="Inventario"
                class="sidebar-link {{ request()->routeIs('materiales.index') && !request('sin_codigo') ? 'active' : '' }}"
            >
                <span class="nav-mark" aria-hidden="true">
                    <svg
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4 6h16M4 12h16M4 18h16M7 6v12"
                        />
                    </svg>
                </span>

                <span class="nav-text">Inventario</span>

                <svg
                    class="nav-arrow"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m9 18 6-6-6-6"
                    />
                </svg>
            </a>

            <a
                href="{{ route('materiales.create') }}"
                title="Registrar entrada"
                data-label="Registrar entrada"
                class="sidebar-link {{ request()->routeIs('materiales.create') ? 'active' : '' }}"
            >
                <span class="nav-mark" aria-hidden="true">
                    <svg
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 5v14M5 12h14"
                        />
                    </svg>
                </span>

                <span class="nav-text">Registrar entrada</span>

                <svg
                    class="nav-arrow"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m9 18 6-6-6-6"
                    />
                </svg>
            </a>

            <a
                href="{{ route('materiales.salidas.create') }}"
                title="Registrar salida"
                data-label="Registrar salida"
                class="sidebar-link {{ request()->routeIs('materiales.salidas.*') ? 'active' : '' }}"
            >
                <span class="nav-mark" aria-hidden="true">
                    <svg
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 12h14M13 6l6 6-6 6"
                        />
                    </svg>
                </span>

                <span class="nav-text">Registrar salida</span>

                <svg
                    class="nav-arrow"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m9 18 6-6-6-6"
                    />
                </svg>
            </a>

            <a
                href="{{ route('materiales.xml.create') }}"
                title="Importar XML"
                data-label="Importar XML"
                class="sidebar-link {{ request()->routeIs('materiales.xml.*') ? 'active' : '' }}"
            >
                <span class="nav-mark" aria-hidden="true">
                    <svg
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M14 2v6h6M8 13l-2 2 2 2M16 13l2 2-2 2M13 12l-2 6"
                        />
                    </svg>
                </span>

                <span class="nav-text">Importar XML</span>

                <svg
                    class="nav-arrow"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m9 18 6-6-6-6"
                    />
                </svg>
            </a>

            <a
                href="{{ route('materiales.visual.create') }}"
                title="Identificador visual"
                data-label="Identificador visual"
                class="sidebar-link {{ request()->routeIs('materiales.visual.*') ? 'active' : '' }}"
            >
                <span class="nav-mark" aria-hidden="true">
                    <svg
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                        />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </span>

                <span class="nav-text">Identificador visual</span>

                <svg
                    class="nav-arrow"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m9 18 6-6-6-6"
                    />
                </svg>
            </a>

            <a
                href="{{ route('materiales.index', ['sin_codigo' => 1]) }}"
                title="Agregar códigos"
                data-label="Agregar códigos"
                class="sidebar-link {{ request('sin_codigo') ? 'active' : '' }}"
            >
                <span class="nav-mark" aria-hidden="true">
                    <svg
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 5v14M6 5v14M10 5v14M14 5v14M18 5v14M21 5v14"
                        />
                    </svg>
                </span>

                <span class="nav-text">Agregar códigos</span>

                <svg
                    class="nav-arrow"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m9 18 6-6-6-6"
                    />
                </svg>
            </a>
        </nav>
    </div>

    <div class="sidebar-footer">
        @auth
            <div class="sidebar-user-card">
                <div class="sidebar-avatar">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>

                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">
                        {{ Auth::user()->name }}
                    </div>

                    <div class="sidebar-user-email">
                        {{ Auth::user()->email }}
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button
                    type="submit"
                    class="sidebar-logout"
                    title="Cerrar sesión"
                    data-label="Cerrar sesión"
                >
                    <span class="nav-mark" aria-hidden="true">
                        <svg
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M10 17l5-5-5-5M15 12H3M14 3h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5"
                            />
                        </svg>
                    </span>

                    <span class="nav-text">Cerrar sesión</span>
                </button>
            </form>
        @endauth
    </div>
</aside>

<div
    class="sidebar-mobile-overlay"
    id="sidebarMobileOverlay"
    aria-hidden="true"
></div>

<script>
    (() => {
        const shell = document.querySelector('.app-shell');
        const toggle = document.getElementById('sidebarToggle');
        const overlay = document.getElementById('sidebarMobileOverlay');
        const sidebarLinks = document.querySelectorAll('.sidebar-link');
        const compactQuery = window.matchMedia('(max-width: 860px)');

        if (!shell || !toggle) {
            return;
        }

        function actualizarAccesibilidad() {
            const esMovil = compactQuery.matches;
            const menuMovilAbierto = shell.classList.contains('mobile-menu-open');
            const sidebarContraida = shell.classList.contains('sidebar-collapsed');

            if (esMovil) {
                toggle.setAttribute(
                    'aria-expanded',
                    menuMovilAbierto ? 'true' : 'false'
                );

                toggle.setAttribute(
                    'aria-label',
                    menuMovilAbierto
                        ? 'Cerrar menú lateral'
                        : 'Abrir menú lateral'
                );

                toggle.title = menuMovilAbierto
                    ? 'Cerrar menú'
                    : 'Abrir menú';

                if (overlay) {
                    overlay.setAttribute(
                        'aria-hidden',
                        menuMovilAbierto ? 'false' : 'true'
                    );
                }

                return;
            }

            toggle.setAttribute(
                'aria-expanded',
                sidebarContraida ? 'false' : 'true'
            );

            toggle.setAttribute(
                'aria-label',
                sidebarContraida
                    ? 'Expandir menú lateral'
                    : 'Contraer menú lateral'
            );

            toggle.title = sidebarContraida
                ? 'Expandir menú'
                : 'Contraer menú';

            if (overlay) {
                overlay.setAttribute('aria-hidden', 'true');
            }
        }

        function aplicarEstadoGuardado() {
            if (compactQuery.matches) {
                shell.classList.remove('sidebar-collapsed');
                shell.classList.remove('mobile-menu-open');
                actualizarAccesibilidad();
                return;
            }

            shell.classList.remove('mobile-menu-open');

            const estadoGuardado = localStorage.getItem(
                'inventarioSidebarCollapsed'
            );

            shell.classList.toggle(
                'sidebar-collapsed',
                estadoGuardado === '1'
            );

            actualizarAccesibilidad();
        }

        function cerrarMenuMovil() {
            shell.classList.remove('mobile-menu-open');
            actualizarAccesibilidad();
        }

        toggle.addEventListener('click', () => {
            if (compactQuery.matches) {
                shell.classList.toggle('mobile-menu-open');
                actualizarAccesibilidad();
                return;
            }

            shell.classList.toggle('sidebar-collapsed');

            localStorage.setItem(
                'inventarioSidebarCollapsed',
                shell.classList.contains('sidebar-collapsed')
                    ? '1'
                    : '0'
            );

            actualizarAccesibilidad();
        });

        if (overlay) {
            overlay.addEventListener('click', cerrarMenuMovil);
        }

        sidebarLinks.forEach((link) => {
            link.addEventListener('click', () => {
                if (compactQuery.matches) {
                    cerrarMenuMovil();
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (
                event.key === 'Escape' &&
                shell.classList.contains('mobile-menu-open')
            ) {
                cerrarMenuMovil();
            }
        });

        if (typeof compactQuery.addEventListener === 'function') {
            compactQuery.addEventListener(
                'change',
                aplicarEstadoGuardado
            );
        } else {
            compactQuery.addListener(aplicarEstadoGuardado);
        }

        aplicarEstadoGuardado();
    })();
</script>
