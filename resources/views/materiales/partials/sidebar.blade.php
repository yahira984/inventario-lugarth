@php
    $workspaceUser = auth()->user();
    $workspaceIsAdmin = $workspaceUser?->puedeAdministrarCatalogo() ?? false;
    $workspaceCanMove = $workspaceUser?->puedeMoverStock() ?? false;
    $workspaceIsConsultant = $workspaceUser?->esConsultor() ?? false;
    $workspaceNotificationCount = ($workspaceStockAlerts ?? 0)
        + ($workspacePendingEntries ?? 0)
        + ($workspacePendingUsers ?? 0);

    $workspaceItem = static fn (
        string $label,
        string $route,
        string $icon,
        string $tone,
        bool $active = false,
        int $badge = 0,
        ?string $description = null,
    ): array => compact('label', 'route', 'icon', 'tone', 'active', 'badge', 'description');

    $workspaceGroups = [
        [
            'label' => 'Inicio',
            'key' => 'inicio',
            'items' => array_values(array_filter([
                $workspaceIsAdmin ? $workspaceItem(
                    'Dashboard', route('dashboard'), 'images/dashboard.png', 'blue', request()->routeIs('dashboard'), 0,
                    'Indicadores, compras y alertas gerenciales'
                ) : null,
            ])),
        ],
        [
            'label' => 'Operación',
            'key' => 'operacion',
            'items' => array_values(array_filter([
                $workspaceItem(
                    'Inventario', route('materiales.index'), 'images/inventario.png', 'blue',
                    request()->routeIs('materiales.index') && !request('sin_codigo'), $workspaceStockAlerts ?? 0,
                    'Existencias reales y ubicaciones'
                ),
                $workspaceCanMove ? $workspaceItem(
                    'Registrar entrada', route('materiales.create'), 'images/entrada.png', 'green', request()->routeIs('materiales.create'), 0,
                    'Alta o reposición con evidencia'
                ) : null,
                $workspaceCanMove ? $workspaceItem(
                    'Registrar salida', route('materiales.salidas.create'), 'images/salida.jpg', 'red', request()->routeIs('materiales.salidas.*'), 0,
                    'Retiro manual o por código'
                ) : null,
                $workspaceCanMove ? $workspaceItem(
                    'Devoluciones y mermas', route('materiales.devoluciones.create'), 'images/devoluciones.svg', 'green', request()->routeIs('materiales.devoluciones.*'), 0,
                    'Regresos a stock y bajas con evidencia'
                ) : null,
            ])),
        ],
        [
            'label' => 'Equipos',
            'key' => 'equipos',
            'items' => array_values(array_filter([
                $workspaceCanMove ? $workspaceItem(
                    'Equipos y paquetes', route('equipos.index'), 'images/registro.png', 'purple', request()->routeIs('equipos.index') || request()->routeIs('equipos.show'), 0,
                    'Recetas de piezas por equipo'
                ) : null,
                $workspaceCanMove ? $workspaceItem(
                    'Retirar equipo', route('equipos.withdrawals.create'), 'images/salida.jpg', 'red', request()->routeIs('equipos.withdrawals.create'), 0,
                    'Venta o retiro con validación de stock'
                ) : null,
                $workspaceCanMove ? $workspaceItem(
                    'Historial de equipos', route('equipos.withdrawals.history'), 'images/historial1.png', 'pink', request()->routeIs('equipos.withdrawals.history'), 0,
                    'Ventas y retiros realizados'
                ) : null,
            ])),
        ],
        [
            'label' => 'Compras',
            'key' => 'compras',
            'items' => array_values(array_filter([
                $workspaceIsAdmin ? $workspaceItem(
                    'Aprobar entradas', route('admin.entradas.index'), 'images/entrada.png', 'amber', request()->routeIs('admin.entradas.*'), $workspacePendingEntries ?? 0,
                    'Solicitudes pendientes de almacén'
                ) : null,
                $workspaceIsAdmin ? $workspaceItem(
                    'Importar XML', route('materiales.xml.create'), 'images/xml.png', 'purple', request()->routeIs('materiales.xml.*'), 0,
                    'Facturas CFDI y costos de compra'
                ) : null,
                $workspaceIsAdmin ? $workspaceItem(
                    'Proveedores', route('admin.proveedores.index'), 'images/provedor.png', 'amber', request()->routeIs('admin.proveedores.*'), 0,
                    'Compras y materiales por proveedor'
                ) : null,
                $workspaceIsAdmin ? $workspaceItem(
                    'Órdenes de compra', route('admin.ordenes.index'), 'images/registro.png', 'amber', request()->routeIs('admin.ordenes.*'), 0,
                    'Planeación y seguimiento de pedidos'
                ) : null,
            ])),
        ],
        [
            'label' => 'Catálogos',
            'key' => 'catalogos',
            'items' => array_values(array_filter([
                $workspaceIsAdmin ? $workspaceItem(
                    'Categorías', route('admin.categorias.index'), 'images/categoria.png', 'teal', request()->routeIs('admin.categorias.*'), 0,
                    'Clasificación de piezas reales'
                ) : null,
                $workspaceIsAdmin ? $workspaceItem(
                    'Materiales', route('admin.materiales.index'), 'images/catalogo.png', 'blue', request()->routeIs('admin.materiales.*'), 0,
                    'Ficha completa e historial por pieza'
                ) : null,
                $workspaceIsAdmin ? $workspaceItem(
                    'Códigos y etiquetas', route('materiales.index', ['sin_codigo' => 1]), 'images/codigo.jpg', 'orange', request()->routeIs('materiales.index') && request('sin_codigo'), 0,
                    'Códigos de barras, QR e impresión'
                ) : null,
            ])),
        ],
        [
            'label' => 'Herramientas',
            'key' => 'herramientas',
            'items' => array_values(array_filter([
                $workspaceItem(
                    'Identificador visual', route('materiales.visual.create'), 'images/camara.png', 'cyan', request()->routeIs('materiales.visual.*'), 0,
                    'Buscar materiales a partir de una foto'
                ),
                ($workspaceIsAdmin || $workspaceIsConsultant) ? $workspaceItem(
                    'Reportes', route('reportes.index'), 'images/historial.jpg', 'teal', request()->routeIs('reportes.*'), 0,
                    'Exportaciones y últimos movimientos'
                ) : null,
            ])),
        ],
        [
            'label' => 'Administración',
            'key' => 'administracion',
            'items' => array_values(array_filter([
                $workspaceIsAdmin ? $workspaceItem(
                    'Usuarios', route('usuarios.roles.index'), 'images/usuarios.png', 'indigo', request()->routeIs('usuarios.roles.*'), $workspacePendingUsers ?? 0,
                    'Aprobaciones, roles y permisos'
                ) : null,
                $workspaceIsAdmin ? $workspaceItem(
                    'Auditoría', route('admin.auditoria.index'), 'images/auditoria.jpg', 'indigo', request()->routeIs('admin.auditoria.*'), 0,
                    'Actividad completa del sistema'
                ) : null,
                $workspaceIsAdmin ? $workspaceItem(
                    'Historial de salidas', route('admin.salidas.index'), 'images/historial1.png', 'pink', request()->routeIs('admin.salidas.*'), 0,
                    'Trazabilidad de piezas retiradas'
                ) : null,
                $workspaceIsAdmin ? $workspaceItem(
                    'Respaldos', route('admin.backups.index'), 'images/respaldo.jpg', 'slate', request()->routeIs('admin.backups.*'), 0,
                    'Copias y restauración de la base de datos'
                ) : null,
            ])),
        ],
    ];

    $workspaceGroups = array_values(array_filter($workspaceGroups, fn (array $group): bool => count($group['items']) > 0));
    $workspaceFlatItems = collect($workspaceGroups)->flatMap(fn (array $group) => $group['items'])->values();

    $workspaceRouteName = request()->route()?->getName() ?? '';
    $workspaceBreadcrumbs = match (true) {
        str_starts_with($workspaceRouteName, 'admin.entradas.') => ['Compras', 'Entradas pendientes'],
        str_starts_with($workspaceRouteName, 'materiales.xml.') => ['Compras', 'Importar XML'],
        str_starts_with($workspaceRouteName, 'admin.proveedores.') => ['Compras', 'Proveedores'],
        str_starts_with($workspaceRouteName, 'admin.ordenes.') => ['Compras', 'Órdenes de compra'],
        str_starts_with($workspaceRouteName, 'equipos.withdrawals.history') => ['Equipos', 'Historial'],
        str_starts_with($workspaceRouteName, 'equipos.withdrawals.create') => ['Equipos', 'Retirar equipo'],
        str_starts_with($workspaceRouteName, 'equipos.') => ['Equipos', 'Equipos y paquetes'],
        str_starts_with($workspaceRouteName, 'materiales.devoluciones.') => ['Operación', 'Devoluciones y mermas'],
        str_starts_with($workspaceRouteName, 'materiales.salidas.') => ['Operación', 'Registrar salida'],
        $workspaceRouteName === 'materiales.create' => ['Operación', 'Registrar entrada'],
        str_starts_with($workspaceRouteName, 'admin.categorias.') => ['Catálogos', 'Categorías'],
        str_starts_with($workspaceRouteName, 'admin.materiales.') => ['Catálogos', 'Materiales'],
        str_starts_with($workspaceRouteName, 'materiales.visual.') => ['Herramientas', 'Identificador visual'],
        str_starts_with($workspaceRouteName, 'reportes.') => ['Herramientas', 'Reportes'],
        str_starts_with($workspaceRouteName, 'usuarios.roles.') => ['Administración', 'Usuarios'],
        str_starts_with($workspaceRouteName, 'admin.auditoria.') => ['Administración', 'Auditoría'],
        str_starts_with($workspaceRouteName, 'admin.salidas.') => ['Administración', 'Historial de salidas'],
        str_starts_with($workspaceRouteName, 'admin.backups.') => ['Administración', 'Respaldos'],
        $workspaceRouteName === 'profile.edit' => ['Cuenta', 'Mi perfil'],
        $workspaceRouteName === 'dashboard' => ['Inicio', 'Dashboard'],
        default => ['Operación', 'Inventario'],
    };
@endphp

<link rel="stylesheet" href="{{ asset('css/workspace.css') }}">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<button type="button" class="workspace-mobile-menu" id="workspaceMobileMenu" aria-label="Abrir menú" aria-expanded="false">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    <span>Menú</span>
</button>

<aside class="sidebar" id="workspaceSidebar" aria-label="Menú principal">
    <div class="sidebar-top">
        <a class="sidebar-brand-wrapper" href="{{ $workspaceIsAdmin ? route('dashboard') : route('materiales.index') }}" aria-label="Ir al inicio">
            <span class="sidebar-logo"><img src="{{ asset('images/logo.png') }}" alt="Logo Lugarth"></span>
            <span class="sidebar-brand"><strong>Inventario Lugarth</strong><small>Almacén y entradas</small></span>
        </a>
        <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Contraer menú" title="Contraer menú">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        </button>
    </div>

    <div class="sidebar-quick-search">
        <button type="button" class="sidebar-search-button" data-open-command aria-label="Buscar en todo el sistema" title="Buscar en todo el sistema">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/></svg>
            <span>Buscar en el sistema</span><kbd>Ctrl K</kbd>
        </button>
    </div>

    <div class="sidebar-navigation">
        <div class="sidebar-favorites" id="sidebarFavorites" hidden>
            <div class="sidebar-section-label">Favoritos</div>
            <div class="sidebar-favorites-list" id="sidebarFavoritesList"></div>
        </div>

        <nav class="sidebar-nav" id="sidebarNav">
            @foreach($workspaceGroups as $group)
                @php($groupActive = collect($group['items'])->contains(fn (array $item): bool => $item['active']))
                <details class="sidebar-group" data-group="{{ $group['key'] }}" {{ $groupActive ? 'open' : '' }}>
                    <summary>
                        <span>{{ $group['label'] }}</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                    </summary>
                    <div class="sidebar-group-items">
                        @foreach($group['items'] as $item)
                            <div class="sidebar-item-row" data-nav-url="{{ $item['route'] }}" data-nav-label="{{ $item['label'] }}" data-nav-icon="{{ asset($item['icon']) }}" data-nav-tone="{{ $item['tone'] }}">
                                <a href="{{ $item['route'] }}" class="sidebar-link tone-{{ $item['tone'] }} {{ $item['active'] ? 'active' : '' }}" data-label="{{ $item['label'] }}" title="{{ $item['label'] }}">
                                    <span class="nav-mark"><img src="{{ asset($item['icon']) }}" alt=""></span>
                                    <span class="nav-copy"><strong>{{ $item['label'] }}</strong><small>{{ $item['description'] }}</small></span>
                                    @if($item['badge'] > 0)
                                        <span class="nav-badge">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                                    @else
                                        <svg class="nav-arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                                    @endif
                                </a>
                                <button type="button" class="nav-favorite" aria-label="Fijar {{ $item['label'] }}" title="Agregar a favoritos">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.2L5.8 21 7 14.2 2 9.3l6.9-1L12 2Z"/></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </details>
            @endforeach
        </nav>
    </div>

    <div class="sidebar-footer">
        <a href="{{ route('profile.edit') }}" class="sidebar-profile {{ request()->routeIs('profile.*') ? 'active' : '' }}" data-label="Mi perfil" title="Mi perfil">
            @if($workspaceUser?->avatar)
                <img src="{{ asset('storage/' . $workspaceUser->avatar) }}" alt="Avatar" style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover;">
            @else
                <span class="sidebar-avatar">{{ strtoupper(mb_substr($workspaceUser?->name ?? 'U', 0, 1)) }}</span>
            @endif
            <span class="sidebar-user-info"><strong>{{ $workspaceUser?->name }}</strong><small>{{ ucfirst($workspaceUser?->role ?? 'usuario') }}</small></span>
            <span class="user-status" aria-label="Cuenta activa"></span>
        </a>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" class="sidebar-logout" data-label="Cerrar sesión" title="Cerrar sesión">
                <span class="nav-mark"><img src="{{ asset('images/logo.png') }}" alt=""></span>
                <span class="nav-copy"><strong>Cerrar sesión</strong><small>Salir de forma segura</small></span>
            </button>
        </form>
    </div>
</aside>

<header class="workspace-topbar" id="workspaceTopbar">
    <div class="workspace-breadcrumbs" aria-label="Ruta de navegación">
        <span>{{ $workspaceBreadcrumbs[0] }}</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg><strong>{{ $workspaceBreadcrumbs[1] }}</strong>
    </div>
    <button type="button" class="workspace-search" data-open-command aria-label="Abrir búsqueda global">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/></svg>
        <span>Buscar pieza, equipo, proveedor o movimiento</span><kbd>Ctrl K</kbd>
    </button>
    <div class="workspace-top-actions">
        @if(request()->routeIs('dashboard'))
            <button type="button" class="workspace-icon-button desktop-only" id="workspaceFullscreen" aria-label="Pantalla completa" title="Pantalla completa">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 3H3v5M16 3h5v5M8 21H3v-5M16 21h5v-5"/></svg>
            </button>
        @endif
        <button type="button" class="workspace-icon-button" id="workspaceTheme" aria-label="Cambiar tema" title="Cambiar tema">
            <svg class="theme-sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
        </button>
        <button type="button" class="workspace-icon-button" id="workspaceNotifications" aria-label="Notificaciones" title="Notificaciones" aria-expanded="false">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
            @if($workspaceNotificationCount > 0)<span class="workspace-count">{{ $workspaceNotificationCount > 99 ? '99+' : $workspaceNotificationCount }}</span>@endif
        </button>
        
        <a class="workspace-user-button" href="{{ route('profile.edit') }}" title="Abrir mi perfil" style="display: flex; align-items: center; gap: 8px;">
            @if($workspaceUser?->avatar)
                <img src="{{ asset('storage/' . $workspaceUser->avatar) }}" alt="Avatar" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">
            @else
                <span>{{ strtoupper(mb_substr($workspaceUser?->name ?? 'U', 0, 1)) }}</span>
            @endif
            <strong>{{ strtok($workspaceUser?->name ?? 'Usuario', ' ') }}</strong>
        </a>
    </div>
</header>

<section class="workspace-popover notification-popover" id="notificationPopover" hidden aria-label="Centro de notificaciones">
    <header><div><strong>Notificaciones</strong><small>{{ $workspaceNotificationCount }} asuntos requieren atención</small></div><button type="button" data-close-popover aria-label="Cerrar">×</button></header>
    <div class="notification-list">
        @if(($workspacePendingEntries ?? 0) > 0)
            <a href="{{ route('admin.entradas.index') }}" class="notification-item tone-amber"><span class="notification-dot"></span><span><strong>{{ $workspacePendingEntries }} entradas por aprobar</strong><small>Revisa evidencias y corrige los datos antes de sumar stock.</small></span></a>
        @endif
        @if(($workspaceStockAlerts ?? 0) > 0)
            <a href="{{ route('materiales.index', ['stock' => 'critico']) }}" class="notification-item tone-red"><span class="notification-dot"></span><span><strong>{{ $workspaceStockAlerts }} alertas de stock</strong><small>Hay piezas en mínimo o sin existencias.</small></span></a>
        @endif
        @if(($workspacePendingUsers ?? 0) > 0)
            <a href="{{ route('usuarios.roles.index') }}" class="notification-item tone-indigo"><span class="notification-dot"></span><span><strong>{{ $workspacePendingUsers }} usuarios pendientes</strong><small>Aprueba sus correos y asigna el rol correcto.</small></span></a>
        @endif
        @forelse(($workspaceRecentActivity ?? collect())->take(4) as $activity)
            <a href="{{ route('admin.auditoria.index', ['buscar' => $activity->accion]) }}" class="notification-item"><span class="notification-dot"></span><span><strong>{{ $activity->accion }}</strong><small>{{ $activity->user?->name ?? 'Sistema' }} · {{ $activity->created_at?->diffForHumans() }}</small></span></a>
        @empty
            @if($workspaceNotificationCount === 0)<div class="workspace-empty"><strong>Todo está al día</strong><span>No hay asuntos pendientes en este momento.</span></div>@endif
        @endforelse
    </div>
</section>

<div class="workspace-overlay" id="workspaceOverlay" hidden></div>

<section class="command-palette" id="commandPalette" hidden role="dialog" aria-modal="true" aria-labelledby="commandTitle" data-search-url="{{ route('buscar.global') }}">
    <div class="command-box">
        <header>
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/></svg>
            <input id="commandInput" type="search" autocomplete="off" placeholder="Busca una pieza, apodo, equipo, proveedor, usuario o movimiento..." aria-labelledby="commandTitle">
            <kbd>Esc</kbd>
        </header>
        <div class="command-status" id="commandStatus">Escribe al menos 2 caracteres o elige un acceso rápido.</div>
        <div class="command-results" id="commandResults">
            <div class="command-section-title" id="commandTitle">Accesos rápidos</div>
            @foreach($workspaceFlatItems->take(8) as $item)
                <a href="{{ $item['route'] }}" class="command-result"><span class="command-result-icon tone-{{ $item['tone'] }}"><img src="{{ asset($item['icon']) }}" alt=""></span><span><strong>{{ $item['label'] }}</strong><small>{{ $item['description'] }}</small></span><em>Abrir</em></a>
            @endforeach
        </div>
    </div>
</section>

<section class="workspace-lightbox" id="workspaceLightbox" hidden role="dialog" aria-modal="true" aria-label="Vista ampliada de imagen">
    <button type="button" class="lightbox-close" aria-label="Cerrar imagen">×</button>
    <div class="lightbox-stage"><img src="" alt=""><div class="lightbox-caption"></div></div>
</section>

<nav class="workspace-mobile-tabs" aria-label="Accesos principales">
    @if($workspaceIsAdmin)
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><img src="{{ asset('images/dashboard.png') }}" alt=""><span>Inicio</span></a>
    @elseif($workspaceCanMove)
        <a href="{{ route('equipos.index') }}" class="{{ request()->routeIs('equipos.*') ? 'active' : '' }}"><img src="{{ asset('images/registro.png') }}" alt=""><span>Equipos</span></a>
    @else
        <a href="{{ route('materiales.index') }}" class="{{ request()->routeIs('materiales.index') ? 'active' : '' }}"><img src="{{ asset('images/inventario.png') }}" alt=""><span>Inventario</span></a>
    @endif
    @if($workspaceCanMove)
        <a href="{{ route('materiales.index') }}" class="{{ request()->routeIs('materiales.index') ? 'active' : '' }}"><img src="{{ asset('images/inventario.png') }}" alt=""><span>Inventario</span></a>
        <a href="{{ route('materiales.create') }}" class="{{ request()->routeIs('materiales.create') ? 'active' : '' }}"><img src="{{ asset('images/entrada.png') }}" alt=""><span>Entrada</span></a>
        <a href="{{ route('materiales.salidas.create') }}" class="{{ request()->routeIs('materiales.salidas.*') ? 'active' : '' }}"><img src="{{ asset('images/salida.jpg') }}" alt=""><span>Salida</span></a>
    @else
        <a href="{{ route('materiales.visual.create') }}" class="{{ request()->routeIs('materiales.visual.*') ? 'active' : '' }}"><img src="{{ asset('images/camara.png') }}" alt=""><span>Identificar</span></a>
        <a href="{{ route('reportes.index') }}" class="{{ request()->routeIs('reportes.*') ? 'active' : '' }}"><img src="{{ asset('images/historial.jpg') }}" alt=""><span>Reportes</span></a>
        <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}"><img src="{{ asset('images/usuarios.png') }}" alt=""><span>Perfil</span></a>
    @endif
    <button type="button" data-mobile-more><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/></svg><span>Menú</span></button>
</nav>

<script>
    window.InventoryWorkspace = {
        routeName: @json($workspaceRouteName),
        userRole: @json($workspaceUser?->role),
        searchUrl: @json(route('buscar.global')),
    };
</script>