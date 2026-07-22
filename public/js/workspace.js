(() => {
    'use strict';

    const shell = document.querySelector('.app-shell');
    const sidebar = document.getElementById('workspaceSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileMenu = document.getElementById('workspaceMobileMenu');
    const mobileMore = document.querySelector('[data-mobile-more]');
    const overlay = document.getElementById('workspaceOverlay');
    const compactQuery = window.matchMedia('(max-width: 1024px)');
    const storagePrefix = `lugarth:${window.InventoryWorkspace?.userRole || 'user'}:`;

    if (!shell || !sidebar) return;

    const setOverlay = (visible) => {
        if (!overlay) return;
        overlay.hidden = !visible;
        document.body.classList.toggle('workspace-modal-open', visible);
    };

    const syncSidebar = () => {
        if (compactQuery.matches) {
            shell.classList.remove('sidebar-collapsed');
            shell.classList.remove('mobile-menu-open');
        } else {
            shell.classList.toggle('sidebar-collapsed', localStorage.getItem(`${storagePrefix}sidebar`) === 'compact');
        }
        const collapsed = shell.classList.contains('sidebar-collapsed');
        sidebarToggle?.setAttribute('aria-label', collapsed ? 'Expandir menú' : 'Contraer menú');
        sidebarToggle?.setAttribute('title', collapsed ? 'Expandir menú' : 'Contraer menú');
    };

    const openMobileSidebar = () => {
        shell.classList.add('mobile-menu-open');
        mobileMenu?.setAttribute('aria-expanded', 'true');
        setOverlay(true);
    };

    const closeMobileSidebar = () => {
        shell.classList.remove('mobile-menu-open');
        mobileMenu?.setAttribute('aria-expanded', 'false');
        setOverlay(false);
    };

    sidebarToggle?.addEventListener('click', () => {
        if (compactQuery.matches) {
            closeMobileSidebar();
            return;
        }
        shell.classList.toggle('sidebar-collapsed');
        localStorage.setItem(`${storagePrefix}sidebar`, shell.classList.contains('sidebar-collapsed') ? 'compact' : 'open');
        syncSidebar();
    });
    mobileMenu?.addEventListener('click', openMobileSidebar);
    mobileMore?.addEventListener('click', openMobileSidebar);
    sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => compactQuery.matches && closeMobileSidebar()));
    compactQuery.addEventListener?.('change', syncSidebar);
    syncSidebar();

    document.querySelectorAll('.sidebar-group').forEach((group) => {
        const key = group.dataset.group;
        const saved = localStorage.getItem(`${storagePrefix}group:${key}`);
        if (!group.querySelector('.sidebar-link.active') && saved !== null) group.open = saved === 'open';
        group.addEventListener('toggle', () => localStorage.setItem(`${storagePrefix}group:${key}`, group.open ? 'open' : 'closed'));
    });

    /* Favorites */
    const favoriteContainer = document.getElementById('sidebarFavorites');
    const favoriteList = document.getElementById('sidebarFavoritesList');
    const favoriteKey = `${storagePrefix}favorites`;
    let favorites = new Set(JSON.parse(localStorage.getItem(favoriteKey) || '[]'));

    const renderFavorites = () => {
        if (!favoriteContainer || !favoriteList) return;
        favoriteList.replaceChildren();
        const rows = [...document.querySelectorAll('.sidebar-item-row[data-nav-url]')];
        rows.forEach((row) => {
            const url = row.dataset.navUrl;
            row.querySelector('.nav-favorite')?.classList.toggle('is-favorite', favorites.has(url));
            if (!favorites.has(url)) return;
            const source = row.querySelector('.sidebar-link');
            if (!source) return;
            const clone = source.cloneNode(true);
            clone.classList.remove('active');
            clone.querySelector('.nav-arrow')?.remove();
            clone.querySelector('.nav-badge')?.remove();
            favoriteList.append(clone);
        });
        favoriteContainer.hidden = favoriteList.children.length === 0;
    };

    document.querySelectorAll('.nav-favorite').forEach((button) => {
        button.addEventListener('click', () => {
            const url = button.closest('.sidebar-item-row')?.dataset.navUrl;
            if (!url) return;
            favorites.has(url) ? favorites.delete(url) : favorites.add(url);
            localStorage.setItem(favoriteKey, JSON.stringify([...favorites]));
            renderFavorites();
        });
    });
    renderFavorites();

    /* Theme and fullscreen */
    const themeButton = document.getElementById('workspaceTheme');
    const themeKey = `${storagePrefix}theme`;
    const applyTheme = (theme) => {
        document.body.dataset.workspaceTheme = theme;
        themeButton?.setAttribute('title', theme === 'dark' ? 'Usar tema claro' : 'Usar tema oscuro');
        if (window.Chart?.instances) {
            const ink = theme === 'dark' ? '#dceaf6' : '#334155';
            const muted = theme === 'dark' ? '#a7bacb' : '#64748b';
            Object.values(window.Chart.instances).forEach((chart) => {
                if (chart.options?.plugins?.legend?.labels) chart.options.plugins.legend.labels.color = ink;
                if (chart.options?.plugins?.textoCentral) {
                    chart.options.plugins.textoCentral.colorPrincipal = ink;
                    chart.options.plugins.textoCentral.colorSecundario = muted;
                }
                Object.values(chart.options?.scales || {}).forEach((scale) => {
                    if (scale.ticks) scale.ticks.color = ink;
                    if (scale.grid) scale.grid.color = theme === 'dark' ? 'rgba(167,186,203,.16)' : 'rgba(100,116,139,.15)';
                });
                chart.update('none');
            });
        }
    };
    applyTheme(localStorage.getItem(themeKey) || 'light');
    themeButton?.addEventListener('click', () => {
        const next = document.body.dataset.workspaceTheme === 'dark' ? 'light' : 'dark';
        localStorage.setItem(themeKey, next);
        applyTheme(next);
    });
    document.getElementById('workspaceFullscreen')?.addEventListener('click', async () => {
        try {
            document.fullscreenElement ? await document.exitFullscreen() : await document.documentElement.requestFullscreen();
        } catch (_) {
            // Fullscreen can be blocked by browser policy; the page remains usable.
        }
    });

    /* Notifications */
    const notificationsButton = document.getElementById('workspaceNotifications');
    const notifications = document.getElementById('notificationPopover');
    const closeNotifications = () => {
        if (!notifications) return;
        notifications.hidden = true;
        notificationsButton?.setAttribute('aria-expanded', 'false');
    };
    notificationsButton?.addEventListener('click', (event) => {
        event.stopPropagation();
        if (!notifications) return;
        notifications.hidden = !notifications.hidden;
        notificationsButton.setAttribute('aria-expanded', notifications.hidden ? 'false' : 'true');
    });
    notifications?.querySelector('[data-close-popover]')?.addEventListener('click', closeNotifications);
    document.addEventListener('click', (event) => {
        if (!notifications?.hidden && !notifications.contains(event.target) && !notificationsButton?.contains(event.target)) closeNotifications();
    });

    /* Global command palette */
    const palette = document.getElementById('commandPalette');
    const commandInput = document.getElementById('commandInput');
    const commandResults = document.getElementById('commandResults');
    const commandStatus = document.getElementById('commandStatus');
    const quickResults = commandResults?.innerHTML || '';
    let searchTimer;
    let searchController;
    let selectedResult = -1;

    const openPalette = () => {
        if (!palette) return;
        closeNotifications();
        palette.hidden = false;
        setOverlay(true);
        selectedResult = -1;
        window.setTimeout(() => commandInput?.focus(), 30);
    };
    const closePalette = () => {
        if (!palette) return;
        palette.hidden = true;
        if (!shell.classList.contains('mobile-menu-open')) setOverlay(false);
    };
    document.querySelectorAll('[data-open-command]').forEach((button) => button.addEventListener('click', openPalette));
    overlay?.addEventListener('click', () => {
        closePalette();
        closeMobileSidebar();
    });
    palette?.addEventListener('click', (event) => event.target === palette && closePalette());

    const appendSearchResult = (result) => {
        const link = document.createElement('a');
        link.className = 'command-result';
        link.href = result.url;

        const icon = document.createElement('span');
        icon.className = `command-result-icon tone-${result.tone || 'blue'}`;
        icon.textContent = (result.type || 'R').slice(0, 1);
        icon.style.fontWeight = '850';

        const copy = document.createElement('span');
        const title = document.createElement('strong');
        const meta = document.createElement('small');
        title.textContent = result.title;
        meta.textContent = `${result.type} · ${result.meta || ''}`;
        copy.append(title, meta);

        const action = document.createElement('em');
        action.textContent = 'Abrir';
        link.append(icon, copy, action);
        commandResults.append(link);
    };

    const runSearch = async () => {
        const query = commandInput?.value.trim() || '';
        selectedResult = -1;
        if (query.length < 2) {
            if (commandResults) commandResults.innerHTML = quickResults;
            if (commandStatus) commandStatus.textContent = 'Escribe al menos 2 caracteres o elige un acceso rápido.';
            return;
        }
        searchController?.abort();
        searchController = new AbortController();
        if (commandStatus) commandStatus.textContent = 'Buscando en inventario, equipos y movimientos...';
        try {
            const response = await fetch(`${window.InventoryWorkspace.searchUrl}?q=${encodeURIComponent(query)}`, {
                headers: { Accept: 'application/json' },
                signal: searchController.signal,
            });
            if (!response.ok) throw new Error('No se pudo completar la búsqueda.');
            const data = await response.json();
            commandResults?.replaceChildren();
            if (!data.results?.length) {
                if (commandStatus) commandStatus.textContent = `No encontramos resultados para “${query}”.`;
                const empty = document.createElement('div');
                empty.className = 'workspace-empty';
                empty.innerHTML = '<strong>Sin coincidencias</strong><span>Prueba con un apodo, número de parte, código o referencia.</span>';
                commandResults?.append(empty);
                return;
            }
            if (commandStatus) commandStatus.textContent = `${data.results.length} resultados encontrados.`;
            data.results.forEach(appendSearchResult);
        } catch (error) {
            if (error.name === 'AbortError') return;
            if (commandStatus) commandStatus.textContent = 'La búsqueda no respondió. Intenta nuevamente.';
        }
    };
    commandInput?.addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(runSearch, 240);
    });

    const moveCommandSelection = (direction) => {
        const results = [...(commandResults?.querySelectorAll('.command-result') || [])];
        if (!results.length) return;
        results.forEach((item) => item.classList.remove('is-selected'));
        selectedResult = (selectedResult + direction + results.length) % results.length;
        results[selectedResult].classList.add('is-selected');
        results[selectedResult].scrollIntoView({ block: 'nearest' });
    };
    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            palette?.hidden ? openPalette() : closePalette();
            return;
        }
        if (event.key === 'Escape') {
            closePalette();
            closeNotifications();
            if (compactQuery.matches) closeMobileSidebar();
            closeLightbox();
            return;
        }
        if (!palette?.hidden && event.key === 'ArrowDown') { event.preventDefault(); moveCommandSelection(1); }
        if (!palette?.hidden && event.key === 'ArrowUp') { event.preventDefault(); moveCommandSelection(-1); }
        if (!palette?.hidden && event.key === 'Enter' && selectedResult >= 0) {
            const selected = commandResults?.querySelectorAll('.command-result')[selectedResult];
            if (selected) { event.preventDefault(); selected.click(); }
        }
    });

    /* Semantic buttons */
    const classifyButton = (button) => {
        const text = (button.textContent || button.getAttribute('aria-label') || '').trim().toLowerCase();
        if (/xml|importar/.test(text)) button.classList.add('workspace-action-purple');
        else if (/eliminar|borrar|rechazar|salida|retirar|vender|merma|cancelar/.test(text)) button.classList.add('workspace-action-red');
        else if (/editar|corregir|pendiente|revisar/.test(text)) button.classList.add('workspace-action-amber');
        else if (/aprobar|guardar|registrar|crear|entrada|devolver|restaurar/.test(text)) button.classList.add('workspace-action-green');
        else if (/excel/.test(text)) button.classList.add('workspace-action-green');
        else if (/pdf/.test(text)) button.classList.add('workspace-action-red');
        else if (/reporte|analizar|buscar/.test(text)) button.classList.add('workspace-action-teal');
        else if (/volver|cerrar|limpiar/.test(text)) button.classList.add('workspace-action-soft');
        else button.classList.add('workspace-action-blue');
    };
    document.querySelectorAll('.app-content :is(button, a.btn, a[class*="btn-"])').forEach(classifyButton);

    /* Professional tables */
    document.querySelectorAll('.app-content table').forEach((table, tableIndex) => {
        const headers = [...table.querySelectorAll('thead th')].map((cell) => cell.textContent.trim() || 'Dato');
        if (!headers.length) return;
        table.classList.add('workspace-mobile-cards');
        table.querySelectorAll('tbody tr').forEach((row) => {
            [...row.children].forEach((cell, index) => cell.dataset.columnLabel = headers[index] || 'Dato');
        });

        let scroll = table.closest('.workspace-table-scroll, .table-wrap, .table-responsive, .responsive-table');
        if (scroll) {
            scroll.classList.add('workspace-table-scroll');
        } else {
            scroll = document.createElement('div');
            scroll.className = 'workspace-table-scroll';
            table.before(scroll);
            scroll.append(table);
        }
        const shell = document.createElement('div');
        shell.className = 'workspace-table-shell';
        scroll.before(shell);
        shell.append(scroll);

        if (headers.length < 4) return;
        const tools = document.createElement('div');
        tools.className = 'workspace-table-tools';
        const density = document.createElement('button');
        density.type = 'button';
        density.textContent = 'Vista compacta';
        const columns = document.createElement('button');
        columns.type = 'button';
        columns.textContent = 'Columnas';
        tools.append(density, columns);
        shell.prepend(tools);

        const rowSelectors = [...table.querySelectorAll('.workspace-row-select')];
        if (rowSelectors.length) {
            const selectMode = document.createElement('button');
            selectMode.type = 'button';
            selectMode.textContent = 'Seleccionar';
            const selectionActions = document.createElement('div');
            selectionActions.className = 'workspace-selection-actions';
            selectionActions.hidden = true;
            const selectionCount = document.createElement('span');
            const exportSelected = document.createElement('button');
            exportSelected.type = 'button'; exportSelected.textContent = 'Exportar selección';
            const printSelected = document.createElement('button');
            printSelected.type = 'button'; printSelected.textContent = 'Imprimir etiquetas';
            selectionActions.append(selectionCount, exportSelected, printSelected);
            tools.prepend(selectionActions, selectMode);

            const selectedIds = () => rowSelectors.filter((input) => input.checked).map((input) => input.value);
            const updateSelection = () => {
                const count = selectedIds().length;
                selectionCount.textContent = `${count} seleccionados`;
                exportSelected.disabled = count === 0;
                printSelected.disabled = count === 0;
            };
            selectMode.addEventListener('click', () => {
                const opening = selectionActions.hidden;
                selectionActions.hidden = !opening;
                rowSelectors.forEach((input) => { input.hidden = !opening; if (!opening) input.checked = false; });
                selectMode.textContent = opening ? 'Cancelar selección' : 'Seleccionar';
                updateSelection();
            });
            rowSelectors.forEach((input) => input.addEventListener('change', updateSelection));
            exportSelected.addEventListener('click', () => {
                const ids = selectedIds();
                if (ids.length) window.location.href = `/reportes/inventario.csv?ids=${encodeURIComponent(ids.join(','))}`;
            });
            printSelected.addEventListener('click', () => {
                const ids = selectedIds();
                if (ids.length) window.open(`/materiales/etiquetas/lote?ids=${encodeURIComponent(ids.join(','))}`, '_blank', 'noopener');
            });
        }

        const tableKey = `${storagePrefix}table:${window.InventoryWorkspace.routeName}:${tableIndex}`;
        const compact = localStorage.getItem(`${tableKey}:density`) === 'compact';
        shell.classList.toggle('workspace-density-compact', compact);
        density.textContent = compact ? 'Vista cómoda' : 'Vista compacta';
        density.addEventListener('click', () => {
            shell.classList.toggle('workspace-density-compact');
            const isCompact = shell.classList.contains('workspace-density-compact');
            density.textContent = isCompact ? 'Vista cómoda' : 'Vista compacta';
            localStorage.setItem(`${tableKey}:density`, isCompact ? 'compact' : 'comfortable');
        });

        const hiddenColumns = new Set(JSON.parse(localStorage.getItem(`${tableKey}:columns`) || '[]'));
        const setColumn = (index, hidden) => {
            table.querySelectorAll('tr').forEach((row) => row.children[index]?.classList.toggle('workspace-column-hidden', hidden));
        };
        hiddenColumns.forEach((index) => setColumn(Number(index), true));

        columns.addEventListener('click', () => {
            const existing = shell.querySelector('.workspace-column-menu');
            if (existing) { existing.remove(); return; }
            const menu = document.createElement('div');
            menu.className = 'workspace-column-menu';
            headers.forEach((header, index) => {
                const label = document.createElement('label');
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.checked = !hiddenColumns.has(index);
                checkbox.disabled = index === 0;
                checkbox.addEventListener('change', () => {
                    checkbox.checked ? hiddenColumns.delete(index) : hiddenColumns.add(index);
                    setColumn(index, !checkbox.checked);
                    localStorage.setItem(`${tableKey}:columns`, JSON.stringify([...hiddenColumns]));
                });
                label.append(checkbox, document.createTextNode(header));
                menu.append(label);
            });
            shell.append(menu);
        });
    });

    /* Image gallery */
    const lightbox = document.getElementById('workspaceLightbox');
    const lightboxImage = lightbox?.querySelector('img');
    const lightboxCaption = lightbox?.querySelector('.lightbox-caption');
    function closeLightbox() {
        if (!lightbox || lightbox.hidden) return;
        lightbox.hidden = true;
        if (!palette || palette.hidden) setOverlay(false);
    }
    lightbox?.querySelector('.lightbox-close')?.addEventListener('click', closeLightbox);
    lightbox?.addEventListener('click', (event) => event.target === lightbox && closeLightbox());
    document.querySelector('.app-content')?.addEventListener('click', (event) => {
        const image = event.target.closest('img');
        if (!image || image.closest('[data-no-lightbox]')) return;
        const src = image.currentSrc || image.src;
        if (!src || (!src.includes('/storage/') && !image.closest('table, .suggestion, .result, .photo, .evidence'))) return;
        event.preventDefault();
        if (!lightbox || !lightboxImage || !lightboxCaption) return;
        lightboxImage.src = src;
        lightboxImage.alt = image.alt || 'Imagen ampliada';
        lightboxCaption.textContent = image.alt || image.closest('tr, article, .card')?.querySelector('h2, h3, strong')?.textContent?.trim() || 'Vista ampliada';
        lightbox.hidden = false;
        setOverlay(true);
    });

    /* Loading feedback and toast messages */
    const progress = document.createElement('div');
    progress.className = 'workspace-progress';
    document.body.append(progress);
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (event.defaultPrevented || !form.checkValidity()) return;
            const submitter = event.submitter || form.querySelector('[type="submit"]');
            submitter?.classList.add('workspace-loading');
            if (submitter) submitter.disabled = true;
            progress.classList.remove('is-done');
            progress.classList.add('is-active');
        });
    });
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('.workspace-loading').forEach((button) => { button.classList.remove('workspace-loading'); button.disabled = false; });
        progress.classList.remove('is-active');
    });

    const highlightedMaterial = document.querySelector('.workspace-highlight-row');
    if (highlightedMaterial) {
        window.setTimeout(() => {
            highlightedMaterial.scrollIntoView({
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                block: 'center',
                inline: 'nearest',
            });
        }, 120);
    }

    const toastCandidates = [...document.querySelectorAll('.app-content :is(.alert-ok, .alert-success, .alert-bad, .alert-error)')].slice(0, 3);
    if (toastCandidates.length) {
        const stack = document.createElement('div');
        stack.className = 'workspace-toast-stack';
        toastCandidates.forEach((source) => {
            const toast = document.createElement('div');
            toast.className = 'workspace-toast';
            toast.style.setProperty('--toast', source.matches('.alert-bad,.alert-error') ? 'var(--ws-red)' : 'var(--ws-green)');
            const message = document.createElement('div');
            message.textContent = source.textContent.trim();
            const close = document.createElement('button');
            close.type = 'button'; close.textContent = '×'; close.setAttribute('aria-label', 'Cerrar aviso');
            close.addEventListener('click', () => toast.remove());
            toast.append(document.createElement('span'), message, close);
            stack.append(toast);
            window.setTimeout(() => toast.remove(), 6500);
        });
        document.body.append(stack);
    }
})();
