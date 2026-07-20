<style>
    :root {
        --sidebar-width: 288px;
        --sidebar-collapsed-width: 86px;

        --ui-bg: #f8fafc;
        --ui-surface: #ffffff;
        --ui-ink: #0f172a;
        --ui-muted: #64748b;
        --ui-soft: #f1f5f9;
        --ui-line: rgba(226, 232, 240, 0.9);

        --brand-blue: #2563eb;
        --brand-blue-2: #3b82f6;
        --brand-blue-soft: rgba(239, 246, 255, 0.85);
        --brand-blue-line: rgba(191, 219, 254, 0.9);

        --danger: #e11d48;
        --danger-dark: #be123c;
        --danger-soft: #fff1f2;

        --shadow-soft: 4px 0 24px rgba(15, 23, 42, 0.045);
        --shadow-card: 0 18px 42px rgba(15, 23, 42, 0.08);
    }

    * {
        box-sizing: border-box;
    }

    html,
    body {
        margin: 0;
        min-height: 100%;
        background: var(--ui-bg);
        color: var(--ui-ink);
        font-family:
            "DM Sans",
            "Segoe UI",
            system-ui,
            -apple-system,
            BlinkMacSystemFont,
            sans-serif;
    }

    body {
        background:
            radial-gradient(
                circle at 95% 0%,
                rgba(59, 130, 246, 0.12),
                transparent 28%
            ),
            linear-gradient(
                180deg,
                #ffffff 0%,
                #f8fafc 48%,
                #eef4ff 100%
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Estructura principal
    |--------------------------------------------------------------------------
    */

    .app-shell {
        min-height: 100vh;
        display: grid;
        grid-template-columns: var(--sidebar-width) minmax(0, 1fr);
        background: var(--ui-bg);
        color: var(--ui-ink);
        transition: grid-template-columns 0.28s ease;
    }

    .app-shell.sidebar-collapsed {
        grid-template-columns:
            var(--sidebar-collapsed-width)
            minmax(0, 1fr);
    }

    .app-content {
        min-width: 0;
        padding: 28px 18px;
        background: var(--ui-bg);
        transition: padding 0.25s ease;
    }

    /*
    |--------------------------------------------------------------------------
    | Sidebar estilo premium blanco / azul
    |--------------------------------------------------------------------------
    */

    .sidebar {
        position: sticky;
        top: 0;
        z-index: 1200;

        height: 100vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;

        padding: 0;

        color: var(--ui-ink);
        background: rgba(255, 255, 255, 0.98);
        border-right: 1px solid rgba(226, 232, 240, 0.72);
        box-shadow: var(--shadow-soft);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);

        transition:
            width 0.28s ease,
            padding 0.28s ease,
            transform 0.28s ease,
            box-shadow 0.28s ease;
    }

    .sidebar::before,
    .sidebar::after {
        display: none;
    }

    .sidebar > * {
        position: relative;
        z-index: 2;
    }

    /*
    |--------------------------------------------------------------------------
    | Encabezado / logo
    |--------------------------------------------------------------------------
    */

    .sidebar-top {
        min-height: 80px;

        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;

        padding: 0 20px;

        border-bottom: 1px solid rgba(241, 245, 249, 0.95);
        background: rgba(255, 255, 255, 0.96);
    }

    .sidebar-brand-wrapper {
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sidebar-logo {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        position: relative;
        overflow: hidden;

        padding: 7px;

        color: var(--brand-blue);

        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 18px;

        box-shadow:
            0 10px 24px rgba(37, 99, 235, 0.1),
            0 1px 0 rgba(255, 255, 255, 0.95);
    }

    .sidebar-logo::before,
    .sidebar-logo::after {
        display: none;
    }

    .sidebar-logo svg {
        width: 26px;
        height: 26px;
        position: relative;
        z-index: 2;
        stroke-width: 2;
    }

    .sidebar-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
        position: relative;
        z-index: 2;
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

        color: #0f172a;

        font-family:
            "Sora",
            "DM Sans",
            "Segoe UI",
            system-ui,
            sans-serif;

        font-size: 15px;
        font-weight: 900;
        letter-spacing: 0.02em;
        line-height: 1.15;

        text-overflow: ellipsis;
        white-space: nowrap;

        text-shadow: none;
    }

    .sidebar-brand span {
        display: flex;
        align-items: center;
        gap: 7px;

        margin-top: 5px;

        color: var(--brand-blue);

        font-size: 10px;
        font-weight: 950;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .sidebar-brand span::before {
        content: "";

        width: 7px;
        height: 7px;
        flex: 0 0 7px;

        border-radius: 50%;

        background: #10b981;

        box-shadow:
            0 0 0 4px rgba(16, 185, 129, 0.12),
            0 0 12px rgba(16, 185, 129, 0.55);
    }

    /*
    |--------------------------------------------------------------------------
    | Botón contraer
    |--------------------------------------------------------------------------
    */

    .sidebar-toggle {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 14px;

        color: #64748b;

        background: #ffffff;

        box-shadow: none;

        font-family: inherit;
        cursor: pointer;

        transition:
            background 0.2s ease,
            color 0.2s ease,
            border-color 0.2s ease,
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }

    .sidebar-toggle:hover {
        color: var(--brand-blue);
        background: #f8fafc;
        border-color: rgba(191, 219, 254, 0.98);

        transform: translateY(-1px);

        box-shadow: 0 10px 22px rgba(37, 99, 235, 0.1);
    }

    .sidebar-toggle:active {
        transform: scale(0.96);
    }

    .sidebar-toggle svg {
        width: 20px;
        height: 20px;
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

        padding: 16px;

        scrollbar-width: thin;
        scrollbar-color:
            #cbd5e1
            transparent;
    }

    .sidebar-navigation::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .sidebar-navigation::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar-navigation::-webkit-scrollbar-thumb {
        border-radius: 10px;
        background: #cbd5e1;
    }

    .sidebar-navigation::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .sidebar-section-title {
        display: flex;
        align-items: center;
        gap: 9px;

        margin: 22px 12px 9px;

        color: #94a3b8;

        font-size: 10px;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .sidebar-section-title:first-child {
        margin-top: 4px;
    }

    .sidebar-section-title::after {
        content: "";

        width: 100%;
        height: 1px;

        background:
            linear-gradient(
                90deg,
                rgba(226, 232, 240, 0.95),
                transparent
            );
    }

    .sidebar-nav {
        display: grid;
        gap: 6px;
    }

    .sidebar-link,
    .sidebar-logout {
        width: 100%;
        min-height: 50px;

        display: flex;
        align-items: center;
        position: relative;

        gap: 12px;

        padding: 9px 12px;

        overflow: hidden;

        border: 1px solid transparent;
        border-radius: 18px;

        color: #64748b;
        background: transparent;

        font-family: inherit;
        font-size: 14px;
        font-weight: 800;

        text-align: left;
        text-decoration: none;
        white-space: nowrap;

        cursor: pointer;

        transition:
            color 0.22s ease,
            background 0.22s ease,
            border-color 0.22s ease,
            transform 0.22s ease,
            box-shadow 0.22s ease;
    }

    .sidebar-link::before {
        display: none;
    }

    .sidebar-link::after {
        display: none;
    }

    .sidebar-link:hover {
        color: var(--brand-blue);

        background: rgba(239, 246, 255, 0.75);

        border-color: rgba(219, 234, 254, 0.8);

        transform: translateX(3px);

        box-shadow: none;
    }

    .sidebar-link.active {
        color: #ffffff;

        background:
            linear-gradient(
                90deg,
                var(--brand-blue),
                var(--brand-blue-2)
            );

        border-color: rgba(59, 130, 246, 0.45);

        box-shadow:
            0 14px 28px rgba(37, 99, 235, 0.22),
            inset 0 1px 0 rgba(255, 255, 255, 0.16);

        transform: translateX(0);
    }

    /*
    |--------------------------------------------------------------------------
    | Colores personalizados por opción
    | Mantiene tus clases actuales y permite que cada opción conserve su color.
    |--------------------------------------------------------------------------
    */

    .sidebar-link {
        --menu-color: var(--brand-blue);
        --menu-dark: #1d4ed8;
        --menu-rgb: 37, 99, 235;
    }

    .sidebar-link.menu-dashboard {
        --menu-color: #2563eb;
        --menu-dark: #1d4ed8;
        --menu-rgb: 37, 99, 235;
    }

    .sidebar-link.menu-inventario {
        --menu-color: #1261c9;
        --menu-dark: #0b3a82;
        --menu-rgb: 18, 97, 201;
    }

    .sidebar-link.menu-entrada {
        --menu-color: #16a34a;
        --menu-dark: #166534;
        --menu-rgb: 22, 163, 74;
    }

    .sidebar-link.menu-salida {
        --menu-color: #dc2626;
        --menu-dark: #991b1b;
        --menu-rgb: 220, 38, 38;
    }

    .sidebar-link.menu-devoluciones {
        --menu-color: #16a34a;
        --menu-dark: #166534;
        --menu-rgb: 22, 163, 74;
    }

    .sidebar-link.menu-equipos {
        --menu-color: #9333ea;
        --menu-dark: #581c87;
        --menu-rgb: 147, 51, 234;
    }

    .sidebar-link.menu-xml {
        --menu-color: #d97706;
        --menu-dark: #92400e;
        --menu-rgb: 217, 119, 6;
    }

    .sidebar-link.menu-usuarios {
        --menu-color: #7c3aed;
        --menu-dark: #4c1d95;
        --menu-rgb: 124, 58, 237;
    }

    .sidebar-link.menu-entradas-admin {
        --menu-color: #f59e0b;
        --menu-dark: #92400e;
        --menu-rgb: 245, 158, 11;
    }

    .sidebar-link.menu-visual {
        --menu-color: #0891b2;
        --menu-dark: #155e75;
        --menu-rgb: 8, 145, 178;
    }

    .sidebar-link.menu-codigos {
        --menu-color: #ea580c;
        --menu-dark: #9a3412;
        --menu-rgb: 234, 88, 12;
    }

    .sidebar-link.menu-categorias {
        --menu-color: #0d9488;
        --menu-dark: #115e59;
        --menu-rgb: 13, 148, 136;
    }

    .sidebar-link.menu-proveedores {
        --menu-color: #ca8a04;
        --menu-dark: #854d0e;
        --menu-rgb: 202, 138, 4;
    }

    .sidebar-link.menu-catalogo {
        --menu-color: #2563eb;
        --menu-dark: #1e3a8a;
        --menu-rgb: 37, 99, 235;
    }

    .sidebar-link.menu-salidas-admin {
        --menu-color: #e11d48;
        --menu-dark: #9f1239;
        --menu-rgb: 225, 29, 72;
    }

    .sidebar-link.menu-auditoria {
        --menu-color: #4f46e5;
        --menu-dark: #312e81;
        --menu-rgb: 79, 70, 229;
    }

    .sidebar-link.menu-respaldos {
        --menu-color: #475569;
        --menu-dark: #1e293b;
        --menu-rgb: 71, 85, 105;
    }

    .sidebar-link:hover {
        color: var(--menu-color);
        background: rgba(var(--menu-rgb), 0.08);
        border-color: rgba(var(--menu-rgb), 0.18);
    }

    .sidebar-link.active {
        color: #ffffff;
        background:
            linear-gradient(
                90deg,
                var(--menu-color),
                var(--menu-dark)
            );
        border-color: rgba(var(--menu-rgb), 0.35);
        box-shadow:
            0 14px 28px rgba(var(--menu-rgb), 0.22),
            inset 0 1px 0 rgba(255, 255, 255, 0.16);
    }

    /*
    |--------------------------------------------------------------------------
    | Iconos de navegación con tus imágenes
    |--------------------------------------------------------------------------
    */

    .nav-mark {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        position: relative;
        z-index: 2;

        padding: 6px;

        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 16px;

        color: var(--brand-blue);

        background: #ffffff;

        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.045);

        transition:
            color 0.22s ease,
            background 0.22s ease,
            border-color 0.22s ease,
            transform 0.22s ease,
            box-shadow 0.22s ease;
    }

    .nav-mark svg {
        width: 20px;
        height: 20px;
        stroke-width: 2;
    }

    .nav-mark img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
        filter: none !important;
    }

    .sidebar-link:hover .nav-mark {
        color: var(--menu-color);

        background: #ffffff;
        border-color: rgba(var(--menu-rgb), 0.25);

        box-shadow: 0 10px 20px rgba(var(--menu-rgb), 0.12);

        transform: scale(1.04);
    }

    .sidebar-link.active .nav-mark {
        color: var(--menu-color);

        background: rgba(255, 255, 255, 0.95);

        border-color: rgba(255, 255, 255, 0.9);

        box-shadow:
            0 10px 20px rgba(15, 23, 42, 0.12),
            inset 0 1px 0 rgba(255, 255, 255, 0.95);

        transform: scale(1.03);
    }

    .nav-text {
        min-width: 0;
        position: relative;
        z-index: 2;

        overflow: hidden;
        text-overflow: ellipsis;
    }

    .nav-arrow {
        width: 16px;
        height: 16px;

        margin-left: auto;

        color: #cbd5e1;

        opacity: 0;
        transform: translateX(-5px);

        transition:
            opacity 0.2s ease,
            transform 0.2s ease,
            color 0.2s ease;
    }

    .sidebar-link:hover .nav-arrow {
        color: var(--menu-color);
        opacity: 1;
        transform: translateX(0);
    }

    .sidebar-link.active .nav-arrow {
        color: rgba(255, 255, 255, 0.9);
        opacity: 1;
        transform: translateX(0);
    }

    .nav-badge {
        margin-left: auto;
        min-width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 7px;
        border-radius: 999px;
        background: #ef4444;
        color: #ffffff;
        font-size: 11px;
        font-weight: 950;
        box-shadow: 0 8px 18px rgba(239, 68, 68, 0.32);
    }

    /*
    |--------------------------------------------------------------------------
    | Usuario y cerrar sesión
    |--------------------------------------------------------------------------
    */

    .sidebar-footer {
        margin-top: auto;
        padding: 14px 16px 16px;

        border-top: 1px solid rgba(241, 245, 249, 0.95);
        background: rgba(255, 255, 255, 0.96);
    }

    .sidebar-user-card {
        display: flex;
        align-items: center;
        gap: 10px;

        margin-bottom: 11px;
        padding: 11px;

        overflow: hidden;

        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 18px;

        background: #ffffff;

        box-shadow:
            0 10px 24px rgba(15, 23, 42, 0.045);
    }

    .sidebar-avatar {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        position: relative;

        border: 0;
        border-radius: 14px;

        color: #ffffff;
        background:
            linear-gradient(
                135deg,
                #6366f1,
                #a855f7
            );

        font-size: 15px;
        font-weight: 950;

        box-shadow:
            0 10px 22px rgba(99, 102, 241, 0.18);
    }

    .sidebar-avatar::after {
        content: "";

        width: 9px;
        height: 9px;

        position: absolute;
        right: -2px;
        bottom: -2px;

        border: 2px solid #ffffff;
        border-radius: 50%;

        background: #10b981;

        box-shadow: 0 0 8px rgba(16, 185, 129, 0.45);
    }

    .sidebar-user-info {
        min-width: 0;
        overflow: hidden;
    }

    .sidebar-user-name {
        overflow: hidden;

        color: #0f172a;

        font-size: 13px;
        font-weight: 900;

        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sidebar-user-email {
        margin-top: 3px;
        overflow: hidden;

        color: #94a3b8;

        font-size: 11px;
        font-weight: 700;

        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sidebar-footer form {
        width: 100%;
        margin: 0;
    }

    .sidebar-logout {
        color: #64748b;
        background: transparent;
        border-color: transparent;
        box-shadow: none;
    }

    .sidebar-logout::before {
        display: none;
    }

    .sidebar-logout:hover {
        color: var(--danger);

        background: var(--danger-soft);

        border-color: rgba(254, 205, 211, 0.9);

        transform: translateX(3px);

        box-shadow: none;
    }

    .sidebar-logout .nav-mark {
        color: var(--danger);
        background: #ffffff;
        border-color: rgba(254, 205, 211, 0.9);
        box-shadow: 0 8px 18px rgba(225, 29, 72, 0.08);
    }

    .sidebar-logout:hover .nav-mark {
        color: #ffffff;
        background:
            linear-gradient(
                135deg,
                var(--danger),
                var(--danger-dark)
            );

        border-color: rgba(225, 29, 72, 0.38);
        box-shadow: 0 12px 22px rgba(225, 29, 72, 0.22);
        transform: scale(1.04);
    }

    /*
    |--------------------------------------------------------------------------
    | Barra contraída
    |--------------------------------------------------------------------------
    */

    .app-shell.sidebar-collapsed .sidebar {
        padding-left: 0;
        padding-right: 0;
    }

    .app-shell.sidebar-collapsed .sidebar-top {
        justify-content: center;
        gap: 10px;
        padding-left: 13px;
        padding-right: 13px;
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
        padding: 16px 12px;
        overflow: visible;
    }

    .app-shell.sidebar-collapsed .sidebar-nav {
        gap: 10px;
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

    .app-shell.sidebar-collapsed .sidebar-user-card {
        justify-content: center;

        padding-left: 8px;
        padding-right: 8px;
    }

    .app-shell.sidebar-collapsed .nav-badge {
        position: absolute;
        top: 3px;
        right: 3px;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        font-size: 9px;
    }

    /*
    |--------------------------------------------------------------------------
    | Tooltips al contraer
    |--------------------------------------------------------------------------
    */

    .app-shell.sidebar-collapsed .sidebar-link::after,
    .app-shell.sidebar-collapsed .sidebar-logout::after {
        content: attr(data-label);

        width: auto;
        height: auto;
        min-width: max-content;

        display: block;

        position: absolute;
        top: 50%;
        right: auto;
        left: calc(100% + 14px);
        z-index: 1500;

        padding: 9px 12px;

        border: 1px solid rgba(226, 232, 240, 0.2);
        border-radius: 11px;

        color: #ffffff;

        background:
            linear-gradient(
                135deg,
                var(--menu-color),
                var(--menu-dark)
            );

        box-shadow:
            0 14px 30px rgba(var(--menu-rgb), 0.28);

        font-size: 12px;
        font-weight: 850;
        line-height: 1;
        white-space: nowrap;

        opacity: 0;
        transform: translate(-7px, -50%);

        pointer-events: none;

        transition:
            opacity 0.18s ease,
            transform 0.18s ease;
    }

    .app-shell.sidebar-collapsed .sidebar-logout::after {
        background:
            linear-gradient(
                135deg,
                var(--danger),
                var(--danger-dark)
            );
        box-shadow:
            0 14px 30px rgba(225, 29, 72, 0.28);
    }

    .app-shell.sidebar-collapsed .sidebar-link:hover::after,
    .app-shell.sidebar-collapsed .sidebar-logout:hover::after {
        opacity: 1;
        transform: translate(0, -50%);
    }

    /*
    |--------------------------------------------------------------------------
    | Menú móvil
    |--------------------------------------------------------------------------
    */

    .sidebar-mobile-overlay {
        display: none;

        position: fixed;
        inset: 0;
        z-index: 1100;

        background: rgba(15, 23, 42, 0.42);

        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }

    .sidebar-mobile-trigger {
        display: none;

        width: auto;
        min-height: 44px;

        align-items: center;
        gap: 10px;

        position: fixed;
        top: 12px;
        left: 12px;
        z-index: 1600;

        padding: 0 15px;

        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 14px;

        color: #64748b;

        background: rgba(255, 255, 255, 0.95);

        box-shadow:
            0 12px 30px rgba(15, 23, 42, 0.12);

        font-family: inherit;
        font-size: 13px;
        font-weight: 900;

        cursor: pointer;

        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);

        transition:
            color 0.2s ease,
            background 0.2s ease,
            border-color 0.2s ease,
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }

    .sidebar-mobile-trigger:hover {
        color: var(--brand-blue);
        background: #ffffff;
        border-color: var(--brand-blue-line);
        transform: translateY(-1px);
        box-shadow: 0 16px 34px rgba(37, 99, 235, 0.14);
    }

    .sidebar-mobile-trigger svg {
        width: 20px;
        height: 20px;
    }

    /*
    |--------------------------------------------------------------------------
    | Tema general para vistas Blade existentes
    |--------------------------------------------------------------------------
    */

    .container,
    .panel,
    .card,
    .help-card,
    .manual-card,
    .selected-card,
    .history-item,
    .critical-item,
    .modal-content,
    .modal-confirm-content {
        background: var(--ui-surface);
        color: var(--ui-ink);

        border: 1px solid var(--ui-line);

        box-shadow: var(--shadow-card);

        backdrop-filter: none;
        -webkit-backdrop-filter: none;
    }

    .page-header,
    .header,
    .panel-header,
    .critical-header {
        background:
            linear-gradient(
                135deg,
                #ffffff,
                #f8fafc
            );

        border-color: var(--ui-line);
    }

    h1,
    h2,
    h3,
    .panel h2,
    .manual-title,
    .selected-name,
    td strong,
    .user-name {
        color: var(--ui-ink);

        background: none;
        -webkit-text-fill-color: currentColor;

        text-shadow: none;
    }

    .header-meta,
    .meta,
    .muted,
    .field-help,
    .stock-meta,
    .code-muted {
        color: var(--ui-muted);
    }

    label {
        color: #334155;
    }

    input,
    select,
    textarea,
    .filter-form input[type="text"],
    .filter-form select {
        background: #ffffff;
        color: var(--ui-ink);

        border: 1px solid #cbd5e1;

        box-shadow: none;
    }

    input::placeholder,
    textarea::placeholder {
        color: #94a3b8;
        opacity: 1;
    }

    input:focus,
    select:focus,
    textarea:focus,
    .filter-form input[type="text"]:focus,
    .filter-form select:focus {
        border-color: var(--brand-blue);

        box-shadow:
            0 0 0 4px rgba(37, 99, 235, 0.12);

        background: #ffffff;
    }

    input[type="file"] {
        color: var(--ui-ink) !important;
        background: #ffffff !important;
        border-color: #cbd5e1 !important;
        box-shadow: none !important;
    }

    input[type="file"]:hover,
    input[type="file"]:focus {
        background: #f8fafc !important;
        border-color: var(--brand-blue) !important;
    }

    select option {
        color: var(--ui-ink);
        background: #ffffff;
    }

    input:disabled,
    select:disabled,
    textarea:disabled,
    input[readonly],
    textarea[readonly] {
        color: #334155 !important;
        background: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
        opacity: 1 !important;
        cursor: not-allowed;
    }

    table {
        background: #ffffff;
        border-spacing: 0;
    }

    thead th,
    th {
        background: #f8fafc;
        color: #475569;

        border-color: var(--ui-line);
    }

    tbody tr,
    tbody td,
    td {
        background: #ffffff;
        color: var(--ui-ink);

        border-color: #edf2f7;
    }

    tbody tr:hover,
    tbody tr:hover td {
        background: #f8fbff;
    }

    .no-photo {
        background: #f8fafc;
        color: var(--ui-muted);

        border-color: #cbd5e1;
    }

    .badge,
    .role-pill {
        background: #eff6ff;
        color: #1d4ed8;

        border: 1px solid #bfdbfe;

        box-shadow: none;
    }

    .badge-success,
    .status-pill.ok,
    .stock-pill {
        background: #ecfdf5;
        color: #047857;

        border: 1px solid #a7f3d0;
    }

    .badge-danger,
    .status-pill.pending,
    .stock-pill.empty,
    .badge-warning {
        background: #fff7ed;
        color: #c2410c;

        border: 1px solid #fed7aa;
    }

    .btn,
    .btn-alta,
    .btn-xml,
    .btn-report,
    .btn-filter,
    .btn-scan,
    .btn-submit,
    .btn-save,
    .btn-primary,
    .btn-blue,
    button[type="submit"]:not([class]) {
        min-height: 42px;

        color: #ffffff;

        background:
            linear-gradient(
                135deg,
                var(--brand-blue),
                var(--brand-blue-2)
            );

        border: 1px solid #1d4ed8;
        border-radius: 12px;

        box-shadow:
            0 10px 22px rgba(37, 99, 235, 0.18);

        transform: none;

        transition:
            transform 0.18s ease,
            box-shadow 0.18s ease,
            background 0.18s ease,
            border-color 0.18s ease;
    }

    .btn:hover,
    .btn-alta:hover,
    .btn-xml:hover,
    .btn-report:hover,
    .btn-filter:hover,
    .btn-scan:hover,
    .btn-submit:hover,
    .btn-save:hover,
    .btn-primary:hover,
    .btn-blue:hover,
    button[type="submit"]:not([class]):hover {
        background:
            linear-gradient(
                135deg,
                #1d4ed8,
                #2563eb
            );

        border-color: #1e40af;

        box-shadow:
            0 14px 28px rgba(37, 99, 235, 0.25);

        transform: translateY(-2px);
    }

    .btn-green,
    .btn-alta,
    .btn-submit,
    .btn-save {
        background: linear-gradient(135deg, #22c55e, #15803d) !important;
        border-color: #15803d !important;
        color: #ffffff !important;
    }

    .btn-green:hover,
    .btn-alta:hover,
    .btn-submit:hover,
    .btn-save:hover {
        background: linear-gradient(135deg, #16a34a, #166534) !important;
        border-color: #166534 !important;
    }

    .btn-red,
    .btn-danger,
    .btn-delete {
        background: linear-gradient(135deg, #ef4444, #b91c1c) !important;
        border-color: #b91c1c !important;
        color: #ffffff !important;
    }

    .btn-red:hover,
    .btn-danger:hover,
    .btn-delete:hover {
        background: linear-gradient(135deg, #dc2626, #991b1b) !important;
        border-color: #991b1b !important;
    }

    .btn-amber,
    .btn-scan,
    .btn-code,
    .btn-label,
    .btn-barcode {
        background: linear-gradient(135deg, #f59e0b, #b45309) !important;
        border-color: #b45309 !important;
        color: #ffffff !important;
    }

    .btn-purple,
    .btn-xml {
        background: linear-gradient(135deg, #8b5cf6, #6d28d9) !important;
        border-color: #6d28d9 !important;
        color: #ffffff !important;
    }

    .btn-clear,
    .btn-soft,
    .btn-secondary,
    .btn-back,
    .btn-cancel,
    .btn-close,
    .close-btn {
        color: #475569 !important;
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: none !important;
    }

    .btn-clear:hover,
    .btn-soft:hover,
    .btn-secondary:hover,
    .btn-back:hover,
    .btn-cancel:hover,
    .btn-close:hover,
    .close-btn:hover {
        color: var(--brand-blue) !important;
        background: #f8fafc !important;
        border-color: var(--brand-blue-line) !important;
    }

    .alert-success {
        background: #ecfdf5;
        color: #047857;

        border: 1px solid #a7f3d0;

        box-shadow: none;
    }

    .alert-danger,
    .field-error,
    .error {
        background: #fef2f2;
        color: #b91c1c;

        border-color: #fecaca;
    }

    .notice,
    .alert-info {
        color: #854d0e;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-left: 4px solid #f59e0b;
        box-shadow: none;
        text-shadow: none;
    }

    .meta-item,
    .status-box,
    .result-card,
    .info-card,
    .summary-card {
        color: var(--ui-ink);
        background: #ffffff;
        border: 1px solid var(--ui-line);
        box-shadow: 0 10px 24px rgba(15, 60, 105, 0.08);
        text-shadow: none;
    }

    .meta-item span,
    .status-box strong {
        color: #1d4ed8;
        text-shadow: none;
    }

    .status-box span,
    .result-title,
    .result-card strong,
    .modal-content p,
    .modal-confirm-content p {
        color: var(--ui-ink);
    }

    .result-meta,
    .help-text,
    .upload-subtitle {
        color: var(--ui-muted);
    }

    .upload-state,
    .upload-title {
        color: var(--ui-ink);
    }

    .empty-result {
        color: var(--ui-muted);
        background: #f8fafc;
        border: 1px solid var(--ui-line);
    }

    .badge.existing {
        color: #166534;
        background: #dcfce7;
        border-color: #86efac;
        box-shadow: none;
    }

    .badge.new {
        color: #075985;
        background: #e0f2fe;
        border-color: #7dd3fc;
        box-shadow: none;
    }

    .chart-stat.good strong {
        color: #047857 !important;
    }

    .chart-stat.danger strong {
        color: #b91c1c !important;
    }

    .panel-tag {
        color: #075985 !important;
        background: #e0f2fe !important;
        border-color: #7dd3fc !important;
    }

    .critical-counter,
    .badge-red {
        color: #b91c1c !important;
        background: #fef2f2 !important;
        border-color: #fecaca !important;
    }

    .empty-state {
        color: #047857 !important;
        background: #ecfdf5 !important;
        border-color: #a7f3d0 !important;
    }

    .barcode-form input {
        color: var(--ui-ink) !important;
        background: #ffffff !important;
        border-color: #cbd5e1 !important;
    }

    .btn-confirm-cancel {
        color: #475569 !important;
        background: #ffffff !important;
        border-color: #cbd5e1 !important;
    }

    .status.info {
        color: #075985 !important;
        background: #e0f2fe !important;
        border-color: #7dd3fc !important;
    }

    .status.success,
    .code-status.success {
        color: #166534 !important;
        background: #dcfce7 !important;
        border-color: #86efac !important;
    }

    .status.error,
    .code-status.error {
        color: #b91c1c !important;
        background: #fef2f2 !important;
        border-color: #fecaca !important;
    }

    #reader {
        background: #ffffff;
        border-color: #bfdbfe;
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

            width: 100%;
            min-height: 100vh;
            height: auto;

            overflow: visible;
        }

        .sidebar {
            width: min(310px, 88vw);
            height: 100vh;

            position: fixed;
            top: 0;
            left: 0;

            transform: translateX(-105%);

            box-shadow:
                20px 0 50px rgba(15, 23, 42, 0.18);
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

            padding-left: 12px;
            padding-right: 12px;
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
            padding: 72px 12px 18px;
        }

        .sidebar-mobile-trigger {
            display: inline-flex;
        }

        .app-shell.mobile-menu-open .sidebar-mobile-trigger {
            opacity: 0;
            pointer-events: none;
        }
    }

    @media (max-width: 560px) {
        .sidebar {
            width: min(300px, 90vw);
        }

        .app-content {
            padding: 70px 8px 12px;
        }

        .sidebar-logo {
            width: 44px;
            height: 44px;
            flex-basis: 44px;
        }

        .sidebar-brand strong {
            font-size: 14px;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Accesibilidad
    |--------------------------------------------------------------------------
    */

    .sidebar-link:focus-visible,
    .sidebar-logout:focus-visible,
    .sidebar-toggle:focus-visible,
    .sidebar-mobile-trigger:focus-visible {
        outline: 4px solid rgba(37, 99, 235, 0.12);
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

<button
    type="button"
    class="sidebar-mobile-trigger"
    id="sidebarMobileTrigger"
    aria-label="Abrir menú principal"
    aria-expanded="false"
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
            stroke-width="2"
            d="M4 7h16M4 12h16M4 17h16"
        />
    </svg>

    <span>Menú</span>
</button>

<aside class="sidebar" aria-label="Menú principal">
    <div class="sidebar-top">
        <div class="sidebar-brand-wrapper">
            <div class="sidebar-logo" aria-hidden="true">
                <img src="{{ asset('images/logo.png') }}" alt="">
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

            @if(auth()->user()?->esAdministrador())
                {{-- DASHBOARD --}}
                <a
                    href="{{ route('dashboard') }}"
                    title="Dashboard"
                    data-label="Dashboard"
                    class="sidebar-link menu-dashboard {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                >
                    <span class="nav-mark" aria-hidden="true">
                        <img src="{{ asset('images/dashboard.png') }}" alt="">
                    </span>

                    <span class="nav-text">Dashboard</span>

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
            @endif

            {{-- INVENTARIO --}}
            <a
                href="{{ route('materiales.index') }}"
                title="Inventario"
                data-label="Inventario"
                class="sidebar-link menu-inventario {{ request()->routeIs('materiales.index') && !request('sin_codigo') ? 'active' : '' }}"
            >
                <span class="nav-mark" aria-hidden="true">
                    <img src="{{ asset('images/inventario.png') }}" alt="">
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

            @if(auth()->user()?->puedeMoverStock())

                {{-- REGISTRAR ENTRADA --}}
                <a
                    href="{{ route('materiales.create') }}"
                    title="Registrar entrada"
                    data-label="Registrar entrada"
                    class="sidebar-link menu-entrada {{ request()->routeIs('materiales.create') ? 'active' : '' }}"
                >
                    <span class="nav-mark" aria-hidden="true">
                        <img src="{{ asset('images/entrada.png') }}" alt="">
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

                {{-- REGISTRAR SALIDA --}}
                <a
                    href="{{ route('materiales.salidas.create') }}"
                    title="Registrar salida"
                    data-label="Registrar salida"
                    class="sidebar-link menu-salida {{ request()->routeIs('materiales.salidas.*') ? 'active' : '' }}"
                >
                    <span class="nav-mark" aria-hidden="true">
                        <img src="{{ asset('images/salida.jpg') }}" alt="">
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

                {{-- DEVOLUCIONES Y MERMAS --}}
                <a
                    href="{{ route('materiales.devoluciones.create') }}"
                    title="Devoluciones y mermas"
                    data-label="Devoluciones"
                    class="sidebar-link menu-devoluciones {{ request()->routeIs('materiales.devoluciones.*') ? 'active' : '' }}"
                >
                    <span class="nav-mark" aria-hidden="true">
                        <img src="{{ asset('images/devoluciones.svg') }}" alt="">
                    </span>

                    <span class="nav-text">Devoluciones</span>

                    <svg
                        class="nav-arrow"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>

                {{-- EQUIPOS / PAQUETES --}}
                <a
                    href="{{ route('equipos.index') }}"
                    title="Equipos y paquetes"
                    data-label="Equipos"
                    class="sidebar-link menu-equipos {{ request()->routeIs('equipos.*') ? 'active' : '' }}"
                >
                    <span class="nav-mark" aria-hidden="true">
                        <img src="{{ asset('images/registro.png') }}" alt="">
                    </span>

                    <span class="nav-text">Equipos</span>

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
            @endif

            @if(auth()->user()?->puedeAdministrarCatalogo())
                @php($entradasPendientesCount = \App\Models\MaterialEntradaPendiente::where('estado', 'pendiente')->count())

                {{-- IMPORTAR XML --}}
                <a
                    href="{{ route('materiales.xml.create') }}"
                    title="Importar XML"
                    data-label="Importar XML"
                    class="sidebar-link menu-xml {{ request()->routeIs('materiales.xml.*') ? 'active' : '' }}"
                >
                    <span class="nav-mark" aria-hidden="true">
                        <img src="{{ asset('images/xml.png') }}" alt="">
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

                {{-- APROBAR ENTRADAS --}}
                <a
                    href="{{ route('admin.entradas.index') }}"
                    title="Aprobar entradas"
                    data-label="Aprobar entradas"
                    class="sidebar-link menu-entradas-admin {{ request()->routeIs('admin.entradas.*') ? 'active' : '' }}"
                >
                    <span class="nav-mark" aria-hidden="true">
                        <img src="{{ asset('images/entrada.png') }}" alt="">
                    </span>

                    <span class="nav-text">Aprobar entradas</span>

                    @if($entradasPendientesCount > 0)
                        <span class="nav-badge">{{ $entradasPendientesCount }}</span>
                    @else
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
                    @endif
                </a>

                {{-- USUARIOS --}}
                <a
                    href="{{ route('usuarios.roles.index') }}"
                    title="Usuarios y permisos"
                    data-label="Usuarios"
                    class="sidebar-link menu-usuarios {{ request()->routeIs('usuarios.roles.*') ? 'active' : '' }}"
                >
                    <span class="nav-mark" aria-hidden="true">
                        <img src="{{ asset('images/usuarios.png') }}" alt="">
                    </span>

                    <span class="nav-text">Usuarios</span>

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

                {{-- CATEGORIAS --}}
                <a
                    href="{{ route('admin.categorias.index') }}"
                    title="Categorias"
                    data-label="Categorias"
                    class="sidebar-link menu-categorias {{ request()->routeIs('admin.categorias.*') ? 'active' : '' }}"
                >
                    <span class="nav-mark" aria-hidden="true">
                        <img src="{{ asset('images/categoria.png') }}" alt="">
                    </span>
                    <span class="nav-text">Categorias</span>
                    <svg class="nav-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>

                {{-- PROVEEDORES --}}
                <a
                    href="{{ route('admin.proveedores.index') }}"
                    title="Proveedores"
                    data-label="Proveedores"
                    class="sidebar-link menu-proveedores {{ request()->routeIs('admin.proveedores.*') ? 'active' : '' }}"
                >
                    <span class="nav-mark" aria-hidden="true">
                        <img src="{{ asset('images/provedor.png') }}" alt="">
                    </span>
                    <span class="nav-text">Proveedores</span>
                    <svg class="nav-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>

                {{-- CATALOGO COMPLETO --}}
                <a
                    href="{{ route('admin.materiales.index') }}"
                    title="Catalogo completo"
                    data-label="Catalogo completo"
                    class="sidebar-link menu-catalogo {{ request()->routeIs('admin.materiales.*') ? 'active' : '' }}"
                >
                    <span class="nav-mark" aria-hidden="true">
                        <img src="{{ asset('images/catalogo.png') }}" alt="">
                    </span>
                    <span class="nav-text">Catalogo completo</span>
                    <svg class="nav-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>

                {{-- SALIDAS ADMIN --}}
                <a
                    href="{{ route('admin.salidas.index') }}"
                    title="Historial de salidas"
                    data-label="Historial salidas"
                    class="sidebar-link menu-salidas-admin {{ request()->routeIs('admin.salidas.*') ? 'active' : '' }}"
                >
                    <span class="nav-mark" aria-hidden="true">
                        <img src="{{ asset('images/historial1.png') }}" alt="">
                    </span>
                    <span class="nav-text">Historial salidas</span>
                    <svg class="nav-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>

                {{-- AUDITORIA --}}
                <a
                    href="{{ route('admin.auditoria.index') }}"
                    title="Auditoria"
                    data-label="Auditoria"
                    class="sidebar-link menu-auditoria {{ request()->routeIs('admin.auditoria.*') ? 'active' : '' }}"
                >
                    <span class="nav-mark" aria-hidden="true">
                        <img src="{{ asset('images/auditoria.jpg') }}" alt="">
                    </span>
                    <span class="nav-text">Auditoria</span>
                    <svg class="nav-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>

                {{-- RESPALDOS --}}
                <a
                    href="{{ route('admin.backups.index') }}"
                    title="Respaldos"
                    data-label="Respaldos"
                    class="sidebar-link menu-respaldos {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}"
                >
                    <span class="nav-mark" aria-hidden="true">
                        <img src="{{ asset('images/respaldo.jpg') }}" alt="">
                    </span>
                    <span class="nav-text">Respaldos</span>
                    <svg class="nav-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>
            @endif

            {{-- IDENTIFICADOR VISUAL --}}
            <a
                href="{{ route('materiales.visual.create') }}"
                title="Identificador visual"
                data-label="Identificador visual"
                class="sidebar-link menu-visual {{ request()->routeIs('materiales.visual.*') ? 'active' : '' }}"
            >
                <span class="nav-mark" aria-hidden="true">
                    <img src="{{ asset('images/camara.png') }}" alt="">
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

            @if(auth()->user()?->puedeAdministrarCatalogo())

                {{-- AGREGAR CÓDIGOS --}}
                <a
                    href="{{ route('materiales.index', ['sin_codigo' => 1]) }}"
                    title="Agregar códigos"
                    data-label="Agregar códigos"
                    class="sidebar-link menu-codigos {{ request('sin_codigo') ? 'active' : '' }}"
                >
                    <span class="nav-mark" aria-hidden="true">
                        <img src="{{ asset('images/codigo.jpg') }}" alt="">
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
            @endif
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
                        <img src="{{ asset('images/logo.png') }}" alt="">
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
        const mobileTrigger = document.getElementById(
            'sidebarMobileTrigger'
        );
        const overlay = document.getElementById(
            'sidebarMobileOverlay'
        );
        const sidebarLinks = document.querySelectorAll(
            '.sidebar-link'
        );
        const compactQuery = window.matchMedia(
            '(max-width: 860px)'
        );

        if (!shell || !toggle) {
            return;
        }

        function actualizarAccesibilidad() {
            const esMovil = compactQuery.matches;
            const menuMovilAbierto = shell.classList.contains(
                'mobile-menu-open'
            );
            const sidebarContraida = shell.classList.contains(
                'sidebar-collapsed'
            );

            if (esMovil) {
                toggle.setAttribute(
                    'aria-expanded',
                    menuMovilAbierto ? 'true' : 'false'
                );

                if (mobileTrigger) {
                    mobileTrigger.setAttribute(
                        'aria-expanded',
                        menuMovilAbierto ? 'true' : 'false'
                    );
                }

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

        if (mobileTrigger) {
            mobileTrigger.addEventListener('click', () => {
                shell.classList.toggle('mobile-menu-open');
                actualizarAccesibilidad();
            });
        }

        if (overlay) {
            overlay.addEventListener(
                'click',
                cerrarMenuMovil
            );
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

        if (
            typeof compactQuery.addEventListener === 'function'
        ) {
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
