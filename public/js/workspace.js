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

    const showWorkspaceToast = (message, tone = 'blue', timeout = 5200) => {
        let stack = document.querySelector('.workspace-toast-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.className = 'workspace-toast-stack';
            document.body.append(stack);
        }

        const colors = {
            blue: 'var(--ws-blue)',
            green: 'var(--ws-green)',
            red: 'var(--ws-red)',
            amber: 'var(--ws-amber)',
            purple: 'var(--ws-purple)',
        };
        const toast = document.createElement('div');
        toast.className = 'workspace-toast';
        toast.style.setProperty('--toast', colors[tone] || colors.blue);

        const copy = document.createElement('div');
        copy.textContent = message;
        const close = document.createElement('button');
        close.type = 'button';
        close.setAttribute('aria-label', 'Cerrar aviso');
        close.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>';
        close.addEventListener('click', () => toast.remove());
        toast.append(document.createElement('span'), copy, close);
        stack.append(toast);
        window.setTimeout(() => toast.remove(), timeout);
    };

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
        closeTeam();
        notifications.hidden = !notifications.hidden;
        notificationsButton.setAttribute('aria-expanded', notifications.hidden ? 'false' : 'true');
    });
    notifications?.querySelector('[data-close-popover]')?.addEventListener('click', closeNotifications);
    document.addEventListener('click', (event) => {
        if (!notifications?.hidden && !notifications.contains(event.target) && !notificationsButton?.contains(event.target)) closeNotifications();
    });

    /* Live team presence and direct messages */
    const teamDock = document.getElementById('workspaceTeamDock');
    const teamButton = document.getElementById('workspaceTeam');
    const teamPopover = document.getElementById('teamPopover');
    const teamList = document.getElementById('teamList');
    const teamStatus = document.getElementById('teamPopoverStatus');
    const onlineCount = document.getElementById('workspaceOnlineCount');
    const unreadCount = document.getElementById('workspaceUnreadCount');
    const teamUsersCount = document.getElementById('teamUsersCount');
    const teamChatsCount = document.getElementById('teamChatsCount');
    const teamTabButtons = [...document.querySelectorAll('[data-team-tab]')];
    const chat = document.getElementById('workspaceChat');
    const chatMessages = document.getElementById('workspaceChatMessages');
    const chatForm = document.getElementById('workspaceChatForm');
    const chatInput = document.getElementById('workspaceChatInput');
    let chatAvatar = document.getElementById('chatAvatar');
    const chatUserName = document.getElementById('chatUserName');
    const chatUserStatus = document.getElementById('chatUserStatus');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        || window.InventoryWorkspace?.csrfToken
        || '';
    const presenceUrl = window.InventoryWorkspace?.presenceUrl;
    const messageUrlTemplate = window.InventoryWorkspace?.messagesUrlTemplate || '';
    const presenceStorageKey = `${storagePrefix}presence`;
    let activeChatUser = null;
    let activeTeamTab = 'users';
    let latestPresenceData = { online_count: 0, unread_total: 0, users: [] };
    let presenceRequest = false;
    let messagesRequest = false;
    let presenceTimer;
    let messagesTimer;

    const closeTeam = () => {
        if (!teamPopover) return;
        teamPopover.hidden = true;
        teamButton?.setAttribute('aria-expanded', 'false');
        teamDock?.classList.remove('is-open');
    };

    const closeChat = () => {
        if (!chat) return;
        chat.hidden = true;
        activeChatUser = null;
        document.body.classList.remove('workspace-chat-open');
        window.clearInterval(messagesTimer);
    };

    const avatarElement = (user) => {
        const avatar = document.createElement('span');
        avatar.className = `team-avatar${user.online ? ' is-online' : ''}`;
        if (user.avatar_url) {
            const image = document.createElement('img');
            image.src = user.avatar_url;
            image.alt = '';
            image.dataset.noLightbox = '';
            avatar.append(image);
        } else {
            const initials = document.createElement('span');
            initials.textContent = user.initials || 'U';
            avatar.append(initials);
        }
        avatar.append(document.createElement('i'));
        return avatar;
    };

    const appendTeamHeading = (label, count) => {
        if (!teamList) return;
        const heading = document.createElement('div');
        heading.className = 'team-section-heading';
        heading.textContent = `${label} (${count})`;
        teamList.append(heading);
    };

    const appendTeamRow = (user, chatMode = false) => {
        if (!teamList) return;
        const row = document.createElement('button');
        row.type = 'button';
        row.className = `team-row${user.is_self ? ' is-self' : ''}`;
        row.disabled = Boolean(user.is_self);
        row.append(avatarElement(user));

        const copy = document.createElement('span');
        copy.className = 'team-copy';
        const name = document.createElement('strong');
        name.textContent = user.name;
        const meta = document.createElement('span');
        const role = document.createElement('b');
        role.className = 'team-role';
        role.textContent = user.role_label;
        meta.append(
            role,
            document.createTextNode(chatMode
                ? ` · ${user.last_message_at || 'Conversación reciente'}`
                : ` · ${user.last_seen}`)
        );
        copy.append(name, meta);

        if (chatMode && user.last_message) {
            const preview = document.createElement('small');
            preview.textContent = user.last_message;
            copy.append(preview);
        }
        row.append(copy);

        if (user.is_self) {
            const self = document.createElement('span');
            self.className = 'team-self-label';
            self.textContent = 'Tú';
            row.append(self);
        } else if (user.unread_count > 0) {
            const unread = document.createElement('span');
            unread.className = 'team-unread';
            unread.textContent = user.unread_count > 99 ? '99+' : String(user.unread_count);
            row.append(unread);
        } else {
            const action = document.createElement('span');
            action.className = 'team-self-label';
            action.textContent = chatMode ? 'Abrir' : 'Chat';
            row.append(action);
        }

        if (!user.is_self) row.addEventListener('click', () => openChat(user));
        teamList.append(row);
    };

    const renderTeam = (data = latestPresenceData) => {
        latestPresenceData = data;
        const users = data.users || [];
        const conversationUsers = users
            .filter((user) => !user.is_self && user.has_conversation)
            .sort((a, b) => {
                if (a.unread_count !== b.unread_count) return b.unread_count - a.unread_count;
                return Number(b.last_message_id || 0) - Number(a.last_message_id || 0);
            });

        if (onlineCount) onlineCount.textContent = String(data.online_count || 0);
        if (unreadCount) {
            const unread = Number(data.unread_total || 0);
            unreadCount.hidden = unread === 0;
            unreadCount.textContent = unread > 99 ? '99+' : String(unread);
        }
        if (teamUsersCount) teamUsersCount.textContent = String(users.length);
        if (teamChatsCount) teamChatsCount.textContent = String(conversationUsers.length);
        if (teamStatus) {
            teamStatus.textContent = activeTeamTab === 'chats'
                ? `${conversationUsers.length} conversaciones · ${data.unread_total || 0} mensajes pendientes`
                : `${data.online_count || 0} en línea · ${users.length} usuarios`;
        }
        if (!teamList) return;
        teamList.replaceChildren();

        if (activeTeamTab === 'chats') {
            if (!conversationUsers.length) {
                const empty = document.createElement('div');
                empty.className = 'workspace-empty';
                empty.innerHTML = '<strong>Aún no hay conversaciones</strong><span>Abre la pestaña Equipo y selecciona una persona para iniciar un chat.</span>';
                teamList.append(empty);
                return;
            }
            conversationUsers.forEach((user) => appendTeamRow(user, true));
            return;
        }

        if (!users.length) {
            const empty = document.createElement('div');
            empty.className = 'workspace-empty';
            empty.innerHTML = '<strong>No hay usuarios disponibles</strong><span>Los usuarios aprobados aparecerán aquí.</span>';
            teamList.append(empty);
            return;
        }

        const onlineUsers = users.filter((user) => user.online);
        const offlineUsers = users.filter((user) => !user.online);
        if (onlineUsers.length) {
            appendTeamHeading('En línea', onlineUsers.length);
            onlineUsers.forEach((user) => appendTeamRow(user));
        }
        if (offlineUsers.length) {
            appendTeamHeading('Sin conexión', offlineUsers.length);
            offlineUsers.forEach((user) => appendTeamRow(user));
        }
    };

    const notifyPresenceChanges = (users) => {
        const current = Object.fromEntries(users.map((user) => [user.id, {
            name: user.name,
            role: user.role_label,
            online: user.online,
            self: user.is_self,
        }]));
        let previous = null;
        try {
            previous = JSON.parse(sessionStorage.getItem(presenceStorageKey) || 'null');
        } catch (_) {
            previous = null;
        }

        if (previous) {
            Object.entries(current).forEach(([id, user]) => {
                if (user.self || !previous[id] || previous[id].online === user.online) return;
                showWorkspaceToast(
                    user.online
                        ? `${user.name} (${user.role}) acaba de conectarse.`
                        : `${user.name} (${user.role}) se desconectó.`,
                    user.online ? 'green' : 'amber',
                );
            });
        }
        sessionStorage.setItem(presenceStorageKey, JSON.stringify(current));
    };

    const refreshPresence = async ({ silent = false } = {}) => {
        if (!presenceUrl || presenceRequest) return;
        presenceRequest = true;
        try {
            const response = await fetch(presenceUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!response.ok) throw new Error('No se pudo actualizar el equipo.');
            const data = await response.json();
            notifyPresenceChanges(data.users || []);
            renderTeam(data);
        } catch (_) {
            if (!silent && teamStatus) teamStatus.textContent = 'No se pudo actualizar. Intentaremos nuevamente.';
        } finally {
            presenceRequest = false;
        }
    };

    const renderChatMessages = (messages) => {
        if (!chatMessages) return;
        const wasNearBottom = chatMessages.scrollHeight - chatMessages.scrollTop - chatMessages.clientHeight < 90;
        const previousLastId = chatMessages.lastElementChild?.dataset?.messageId;
        chatMessages.replaceChildren();

        if (!messages.length) {
            const empty = document.createElement('div');
            empty.className = 'workspace-empty';
            empty.innerHTML = '<strong>Inicia la conversación</strong><span>Los mensajes son privados entre ambos usuarios.</span>';
            chatMessages.append(empty);
            return;
        }

        messages.forEach((message) => {
            const bubble = document.createElement('article');
            bubble.className = `chat-message${message.mine ? ' is-mine' : ''}`;
            bubble.dataset.messageId = message.id;
            const body = document.createElement('span');
            body.textContent = message.body;
            const time = document.createElement('time');
            time.textContent = message.created_at;
            bubble.append(body, time);
            chatMessages.append(bubble);
        });
        const currentLastId = chatMessages.lastElementChild?.dataset?.messageId;
        if (wasNearBottom || previousLastId !== currentLastId) chatMessages.scrollTop = chatMessages.scrollHeight;
    };

    const conversationUrl = (userId) => messageUrlTemplate.replace('__USER__', encodeURIComponent(userId));

    const refreshMessages = async ({ silent = false } = {}) => {
        if (!activeChatUser || messagesRequest) return;
        messagesRequest = true;
        try {
            const response = await fetch(conversationUrl(activeChatUser.id), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!response.ok) throw new Error('No se pudo abrir la conversación.');
            const data = await response.json();
            activeChatUser = { ...activeChatUser, ...data.user };
            if (chatUserStatus) chatUserStatus.textContent = `${data.user.role_label} · ${data.user.online ? 'En línea' : 'Desconectado'}`;
            renderChatMessages(data.messages || []);
            refreshPresence({ silent: true });
        } catch (_) {
            if (!silent && chatMessages) {
                chatMessages.innerHTML = '<div class="workspace-empty"><strong>No se pudo cargar el chat</strong><span>Comprueba tu conexión e intenta nuevamente.</span></div>';
            }
        } finally {
            messagesRequest = false;
        }
    };

    function openChat(user) {
        if (!chat) return;
        activeChatUser = user;
        chat.hidden = false;
        document.body.classList.add('workspace-chat-open');
        closeTeam();
        if (chatUserName) chatUserName.textContent = user.name;
        if (chatUserStatus) chatUserStatus.textContent = `${user.role_label} · ${user.online ? 'En línea' : user.last_seen}`;
        if (chatAvatar) {
            const replacement = avatarElement(user);
            replacement.id = 'chatAvatar';
            chatAvatar.replaceWith(replacement);
            chatAvatar = replacement;
        }
        if (chatMessages) {
            chatMessages.innerHTML = '<div class="team-loading"><span></span><span></span><span></span></div>';
        }
        refreshMessages();
        window.clearInterval(messagesTimer);
        messagesTimer = window.setInterval(() => refreshMessages({ silent: true }), 4000);
        window.setTimeout(() => chatInput?.focus(), 80);
    }

    teamButton?.addEventListener('click', (event) => {
        event.stopPropagation();
        if (!teamPopover) return;
        closeNotifications();
        teamPopover.hidden = !teamPopover.hidden;
        teamButton.setAttribute('aria-expanded', teamPopover.hidden ? 'false' : 'true');
        teamDock?.classList.toggle('is-open', !teamPopover.hidden);
        if (!teamPopover.hidden) refreshPresence();
    });
    teamTabButtons.forEach((button) => {
        button.addEventListener('click', () => {
            activeTeamTab = button.dataset.teamTab || 'users';
            teamTabButtons.forEach((tab) => tab.classList.toggle('is-active', tab === button));
            renderTeam();
        });
    });
    teamPopover?.querySelector('[data-close-team]')?.addEventListener('click', closeTeam);
    document.getElementById('closeWorkspaceChat')?.addEventListener('click', closeChat);
    document.addEventListener('click', (event) => {
        if (!teamPopover?.hidden && !teamPopover.contains(event.target) && !teamButton?.contains(event.target)) closeTeam();
    });

    chatForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const body = chatInput?.value.trim() || '';
        const submit = chatForm.querySelector('button[type="submit"]');
        if (!activeChatUser || !body || !submit) return;
        submit.disabled = true;
        try {
            const response = await fetch(conversationUrl(activeChatUser.id), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ body }),
            });
            if (!response.ok) throw new Error('No se pudo enviar el mensaje.');
            if (chatInput) {
                chatInput.value = '';
                chatInput.style.height = '';
            }
            await refreshMessages();
        } catch (_) {
            showWorkspaceToast('No se pudo enviar el mensaje. Intenta nuevamente.', 'red');
        } finally {
            submit.disabled = false;
            chatInput?.focus();
        }
    });
    chatInput?.addEventListener('input', () => {
        chatInput.style.height = 'auto';
        chatInput.style.height = `${Math.min(chatInput.scrollHeight, 120)}px`;
    });
    chatInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            chatForm?.requestSubmit();
        }
    });

    refreshPresence({ silent: true });
    presenceTimer = window.setInterval(() => {
        if (!document.hidden) refreshPresence({ silent: true });
    }, 10000);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            refreshPresence({ silent: true });
            if (activeChatUser) refreshMessages({ silent: true });
        }
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
        closeTeam();
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
            closeTeam();
            closeChat();
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
    const lightboxTitle = lightbox?.querySelector('.lightbox-title');
    const lightboxCaption = lightbox?.querySelector('.lightbox-caption');
    let lightboxTrigger = null;

    function closeLightbox() {
        if (!lightbox || lightbox.hidden) return;
        lightbox.hidden = true;
        window.setTimeout(() => {
            if (lightbox.hidden && lightboxImage) lightboxImage.removeAttribute('src');
        }, 180);
        if (!palette || palette.hidden) setOverlay(false);
        lightboxTrigger?.focus?.({ preventScroll: true });
        lightboxTrigger = null;
    }

    const openLightbox = (trigger, image) => {
        if (window.InventoryWorkspace?.disableGlobalLightbox) return;
        const sourceImage = image || (trigger?.matches?.('img') ? trigger : trigger?.querySelector?.('img'));
        const src = sourceImage?.currentSrc || sourceImage?.src || trigger?.dataset?.workspaceLightbox;
        if (!src || !lightbox || !lightboxImage || !lightboxTitle || !lightboxCaption) return;

        lightboxTrigger = trigger;
        lightboxImage.src = src;
        lightboxImage.alt = sourceImage?.alt || trigger?.dataset?.lightboxTitle || 'Imagen ampliada';
        lightboxTitle.textContent = trigger?.dataset?.lightboxTitle
            || sourceImage?.alt
            || sourceImage?.closest('tr, article, .card')?.querySelector('h2, h3, strong')?.textContent?.trim()
            || 'Vista ampliada';
        lightboxCaption.textContent = trigger?.dataset?.lightboxCaption || '';
        lightbox.hidden = false;
        setOverlay(true);
        window.setTimeout(() => lightbox.querySelector('.lightbox-close')?.focus(), 30);
    };

    lightbox?.querySelector('.lightbox-close')?.addEventListener('click', closeLightbox);
    lightbox?.addEventListener('click', (event) => event.target === lightbox && closeLightbox());
    document.querySelector('.app-content')?.addEventListener('click', (event) => {
        if (window.InventoryWorkspace?.disableGlobalLightbox) return;
        const explicitTrigger = event.target.closest('[data-workspace-lightbox]');
        if (explicitTrigger) {
            event.preventDefault();
            event.stopPropagation();
            openLightbox(explicitTrigger);
            return;
        }
        const image = event.target.closest('img');
        if (!image || image.closest('[data-no-lightbox]')) return;
        const src = image.currentSrc || image.src;
        if (!src || (!src.includes('/storage/') && !image.closest('table, .suggestion, .result, .photo, .evidence'))) return;
        event.preventDefault();
        event.stopPropagation();
        openLightbox(image, image);
    });
    window.InventoryWorkspace.openImage = (source, title = 'Vista ampliada', caption = '') => {
        const trigger = document.createElement('button');
        trigger.dataset.workspaceLightbox = source;
        trigger.dataset.lightboxTitle = title;
        trigger.dataset.lightboxCaption = caption;
        openLightbox(trigger);
    };

    /* Loading feedback and toast messages */
    const progress = document.createElement('div');
    progress.className = 'workspace-progress';
    document.body.append(progress);
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.hasAttribute('data-workspace-async') || event.defaultPrevented || !form.checkValidity()) return;
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
    toastCandidates.forEach((source) => showWorkspaceToast(
        source.textContent.trim(),
        source.matches('.alert-bad,.alert-error') ? 'red' : 'green',
        6500,
    ));
})();
