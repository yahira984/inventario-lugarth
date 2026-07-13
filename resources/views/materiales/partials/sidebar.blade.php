<style>
    .app-shell {
        min-height: 100vh;
        display: grid;
        grid-template-columns: 260px minmax(0, 1fr);
        transition: grid-template-columns 0.2s ease;
    }

    .app-shell.sidebar-collapsed {
        grid-template-columns: 82px minmax(0, 1fr);
    }

    .sidebar {
        position: sticky;
        top: 0;
        height: 100vh;
        background: #142236;
        color: #fff;
        padding: 18px 14px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        overflow: hidden;
    }

    .sidebar-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding-bottom: 14px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.14);
    }

    .sidebar-brand {
        min-width: 0;
        padding: 0 4px;
    }

    .sidebar-brand strong,
    .sidebar-brand span,
    .sidebar-user,
    .nav-text {
        transition: opacity 0.16s ease, width 0.16s ease;
    }

    .sidebar-brand strong {
        display: block;
        font-size: 19px;
        letter-spacing: 0;
        white-space: nowrap;
    }

    .sidebar-brand span {
        display: block;
        margin-top: 5px;
        color: #b8c5d6;
        font-size: 13px;
        white-space: nowrap;
    }

    .sidebar-toggle {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 6px;
        background: #203651;
        color: #eef4fb;
        font-family: inherit;
        font-size: 16px;
        font-weight: 900;
        cursor: pointer;
    }

    .sidebar-toggle:hover {
        background: #2a4464;
    }

    .sidebar-nav {
        display: grid;
        gap: 8px;
    }

    .sidebar-link,
    .sidebar-logout {
        width: 100%;
        min-height: 42px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 6px;
        border: 1px solid transparent;
        color: #eef4fb;
        background: transparent;
        font-family: inherit;
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        text-align: left;
        white-space: nowrap;
    }

    .nav-mark {
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0;
    }

    .sidebar-link:hover,
    .sidebar-link.active {
        background: #203651;
        border-color: rgba(255, 255, 255, 0.12);
    }

    .sidebar-logout {
        color: #ffd7d4;
    }

    .sidebar-logout:hover {
        background: #3a2230;
        border-color: rgba(255, 255, 255, 0.12);
    }

    .sidebar-footer {
        margin-top: auto;
        padding-top: 16px;
        border-top: 1px solid rgba(255, 255, 255, 0.14);
    }

    .sidebar-user {
        color: #b8c5d6;
        font-size: 12px;
        line-height: 1.4;
        margin-bottom: 10px;
        word-break: break-word;
    }

    .app-content {
        min-width: 0;
        padding: 28px 18px;
    }

    .app-shell.sidebar-collapsed .sidebar {
        padding-left: 12px;
        padding-right: 12px;
    }

    .app-shell.sidebar-collapsed .sidebar-brand {
        width: 0;
        padding: 0;
        overflow: hidden;
    }

    .app-shell.sidebar-collapsed .sidebar-brand strong,
    .app-shell.sidebar-collapsed .sidebar-brand span,
    .app-shell.sidebar-collapsed .sidebar-user,
    .app-shell.sidebar-collapsed .nav-text {
        opacity: 0;
        width: 0;
        overflow: hidden;
    }

    .app-shell.sidebar-collapsed .sidebar-top {
        justify-content: center;
    }

    .app-shell.sidebar-collapsed .sidebar-link,
    .app-shell.sidebar-collapsed .sidebar-logout {
        justify-content: center;
        padding-left: 8px;
        padding-right: 8px;
    }

    .app-shell.sidebar-collapsed .sidebar-footer {
        display: flex;
        justify-content: center;
    }

    @media (max-width: 860px) {
        .app-shell,
        .app-shell.sidebar-collapsed {
            grid-template-columns: 1fr;
        }

        .sidebar {
            position: static;
            height: auto;
            padding: 14px;
        }

        .sidebar-top {
            padding-bottom: 0;
            border-bottom: none;
        }

        .sidebar-nav,
        .sidebar-footer {
            display: none;
        }

        .app-shell.mobile-menu-open .sidebar-nav {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .app-shell.mobile-menu-open .sidebar-footer {
            display: block;
        }

        .app-shell.sidebar-collapsed .sidebar-brand,
        .app-shell.sidebar-collapsed .sidebar-brand strong,
        .app-shell.sidebar-collapsed .sidebar-brand span,
        .app-shell.sidebar-collapsed .sidebar-user,
        .app-shell.sidebar-collapsed .nav-text {
            opacity: 1;
            width: auto;
            overflow: visible;
        }

        .app-shell.sidebar-collapsed .sidebar-link,
        .app-shell.sidebar-collapsed .sidebar-logout {
            justify-content: flex-start;
        }
    }

    @media (max-width: 560px) {
        .app-shell.mobile-menu-open .sidebar-nav {
            grid-template-columns: 1fr;
        }

        .app-content {
            padding: 14px 10px;
        }
    }
</style>

<aside class="sidebar">
    <div class="sidebar-top">
        <div class="sidebar-brand">
            <strong>Inventario Lugarth</strong>
            <span>Almacen y entradas</span>
        </div>

        <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Alternar menu"><<</button>
    </div>

    <nav class="sidebar-nav" id="sidebarNav">
        <a href="{{ route('materiales.index') }}" title="Inventario" class="sidebar-link {{ request()->routeIs('materiales.index') && !request('sin_codigo') ? 'active' : '' }}">
            <span class="nav-mark">IN</span>
            <span class="nav-text">Inventario</span>
        </a>
        <a href="{{ route('materiales.create') }}" title="Registrar entrada" class="sidebar-link {{ request()->routeIs('materiales.create') ? 'active' : '' }}">
            <span class="nav-mark">+</span>
            <span class="nav-text">Registrar entrada</span>
        </a>
        <a href="{{ route('materiales.xml.create') }}" title="Importar XML" class="sidebar-link {{ request()->routeIs('materiales.xml.*') ? 'active' : '' }}">
            <span class="nav-mark">XML</span>
            <span class="nav-text">Importar XML</span>
        </a>
        <a href="{{ route('materiales.visual.create') }}" title="Identificador visual" class="sidebar-link {{ request()->routeIs('materiales.visual.*') ? 'active' : '' }}">
            <span class="nav-mark">VI</span>
            <span class="nav-text">Identificador visual</span>
        </a>
        <a href="{{ route('materiales.index', ['sin_codigo' => 1]) }}" title="Agregar codigos" class="sidebar-link {{ request('sin_codigo') ? 'active' : '' }}">
            <span class="nav-mark">CB</span>
            <span class="nav-text">Agregar codigos</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        @auth
            <div class="sidebar-user">
                {{ Auth::user()->name }}<br>
                {{ Auth::user()->email }}
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-logout" title="Cerrar sesion">
                    <span class="nav-mark">OUT</span>
                    <span class="nav-text">Cerrar sesion</span>
                </button>
            </form>
        @endauth
    </div>
</aside>

<script>
    (() => {
        const shell = document.querySelector('.app-shell');
        const toggle = document.getElementById('sidebarToggle');

        if (!shell || !toggle) {
            return;
        }

        const compactQuery = window.matchMedia('(max-width: 860px)');

        function applySavedState() {
            if (compactQuery.matches) {
                shell.classList.remove('sidebar-collapsed');
                toggle.textContent = 'Menu';
                toggle.setAttribute('aria-expanded', shell.classList.contains('mobile-menu-open') ? 'true' : 'false');
                return;
            }

            shell.classList.remove('mobile-menu-open');

            if (localStorage.getItem('inventarioSidebarCollapsed') === '1') {
                shell.classList.add('sidebar-collapsed');
            }

            toggle.textContent = shell.classList.contains('sidebar-collapsed') ? '>>' : '<<';
            toggle.setAttribute('aria-expanded', shell.classList.contains('sidebar-collapsed') ? 'false' : 'true');
        }

        toggle.addEventListener('click', () => {
            if (compactQuery.matches) {
                shell.classList.toggle('mobile-menu-open');
                toggle.setAttribute('aria-expanded', shell.classList.contains('mobile-menu-open') ? 'true' : 'false');
                return;
            }

            shell.classList.toggle('sidebar-collapsed');
            localStorage.setItem('inventarioSidebarCollapsed', shell.classList.contains('sidebar-collapsed') ? '1' : '0');
            toggle.textContent = shell.classList.contains('sidebar-collapsed') ? '>>' : '<<';
            toggle.setAttribute('aria-expanded', shell.classList.contains('sidebar-collapsed') ? 'false' : 'true');
        });

        compactQuery.addEventListener('change', applySavedState);
        applySavedState();
    })();
</script>
