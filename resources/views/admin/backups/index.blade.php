<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Respaldos - Inventario</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <style>
        :root {
            --backup-ink: #0b2945;
            --backup-muted: #5d748a;
            --backup-line: #d6e2ec;
            --backup-blue: #1268d3;
            --backup-blue-dark: #0b4f9f;
            --backup-green: #078b61;
            --backup-green-soft: #eafaf3;
            --backup-red: #d52f3f;
            --backup-red-soft: #fff0f2;
            --backup-amber: #c66b08;
            --backup-amber-soft: #fff8e8;
        }

        * { box-sizing: border-box; }
        body { margin: 0; color: var(--backup-ink); background: #f3f7fa; font-family: "Segoe UI", Tahoma, sans-serif; }
        button, input { font: inherit; }
        .app-shell { min-height: 100vh; display: flex; }
        .app-content { min-width: 0; flex: 1; padding: 34px clamp(18px, 3vw, 48px) 72px; }
        .backup-page { width: min(1480px, 100%); display: grid; gap: 18px; margin: 0 auto; }
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 24px 26px;
            background: #fff;
            border: 1px solid var(--backup-line);
            border-radius: 8px;
            box-shadow: 0 14px 34px rgba(19, 54, 84, .07);
        }
        .page-header h1 { margin: 0 0 6px; font-size: clamp(24px, 2.4vw, 34px); letter-spacing: 0; }
        .page-header p { margin: 0; color: var(--backup-muted); font-size: 13px; font-weight: 600; }
        .engine-state {
            max-width: 330px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            color: #075b43;
            background: var(--backup-green-soft);
            border: 1px solid #9de4c9;
            border-radius: 7px;
        }
        .engine-state.is-compatible { color: #7a4808; background: var(--backup-amber-soft); border-color: #f2cd86; }
        .engine-state i { width: 10px; height: 10px; flex: 0 0 10px; background: #0aae78; border-radius: 50%; box-shadow: 0 0 0 4px rgba(10, 174, 120, .12); }
        .engine-state.is-compatible i { background: var(--backup-amber); box-shadow: 0 0 0 4px rgba(198, 107, 8, .12); }
        .engine-state span { min-width: 0; display: grid; gap: 2px; }
        .engine-state strong { font-size: 11px; }
        .engine-state small { overflow: hidden; font-size: 9px; text-overflow: ellipsis; white-space: nowrap; }

        .flash { padding: 13px 15px; border: 1px solid; border-radius: 7px; font-size: 12px; font-weight: 750; line-height: 1.5; }
        .flash-success { color: #086044; background: var(--backup-green-soft); border-color: #9de4c9; }
        .flash-error { color: #991b2a; background: var(--backup-red-soft); border-color: #f4b4bd; }
        .flash ul { margin: 0; padding-left: 18px; }

        .operation-status {
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr);
            gap: 11px;
            align-items: center;
            padding: 14px 16px;
            color: #174d79;
            background: #edf7ff;
            border: 1px solid #b9d9f2;
            border-radius: 8px;
        }
        .operation-status[hidden] { display: none !important; }
        .operation-status.is-success { color: #086044; background: var(--backup-green-soft); border-color: #9de4c9; }
        .operation-status.is-error { color: #991b2a; background: var(--backup-red-soft); border-color: #f4b4bd; }
        .operation-spinner { width: 28px; height: 28px; border: 3px solid #b9d9f2; border-top-color: var(--backup-blue); border-radius: 50%; animation: backup-spin .75s linear infinite; }
        .operation-status.is-success .operation-spinner, .operation-status.is-error .operation-spinner { animation: none; border: 0; }
        .operation-status.is-success .operation-spinner::before, .operation-status.is-error .operation-spinner::before { width: 28px; height: 28px; display: grid; place-items: center; color: #fff; background: var(--backup-green); border-radius: 50%; content: "✓"; font-size: 16px; font-weight: 900; }
        .operation-status.is-error .operation-spinner::before { background: var(--backup-red); content: "!"; }
        .operation-status strong { display: block; margin-bottom: 2px; font-size: 13px; }
        .operation-status p { margin: 0; font-size: 11px; line-height: 1.45; }

        .actions-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 14px; }
        .action-panel {
            min-width: 0;
            padding: 22px;
            background: #fff;
            border: 1px solid var(--backup-line);
            border-top: 3px solid var(--panel-color);
            border-radius: 8px;
            box-shadow: 0 12px 30px rgba(19, 54, 84, .06);
        }
        .action-panel.backup { --panel-color: var(--backup-green); }
        .action-panel.restore { --panel-color: var(--backup-red); }
        .panel-heading { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px; }
        .panel-icon { width: 42px; height: 42px; flex: 0 0 42px; display: grid; place-items: center; color: #fff; background: var(--panel-color); border-radius: 7px; }
        .panel-icon svg { width: 21px; height: 21px; fill: none; stroke: currentColor; stroke-width: 1.9; stroke-linecap: round; stroke-linejoin: round; }
        .panel-heading h2 { margin: 0 0 4px; font-size: 19px; }
        .panel-heading p { margin: 0; color: var(--backup-muted); font-size: 11px; line-height: 1.5; }
        .backup-facts { display: grid; gap: 8px; margin: 0 0 18px; padding: 0; list-style: none; }
        .backup-facts li { display: flex; align-items: center; gap: 8px; color: #49647c; font-size: 11px; font-weight: 650; }
        .backup-facts li::before { width: 7px; height: 7px; flex: 0 0 7px; background: var(--panel-color); border-radius: 50%; content: ""; }
        .field { display: grid; gap: 7px; margin-bottom: 12px; }
        .field label { color: #34516c; font-size: 10px; font-weight: 850; text-transform: uppercase; }
        .file-control {
            position: relative;
            min-height: 92px;
            display: grid;
            place-items: center;
            gap: 4px;
            padding: 15px;
            color: #456078;
            background: #f8fbfd;
            border: 1px dashed #9eb7ca;
            border-radius: 7px;
            text-align: center;
            cursor: pointer;
            transition: border-color .16s ease, background .16s ease;
        }
        .file-control:hover, .file-control:focus-within { background: #f0f7fd; border-color: var(--backup-blue); }
        .file-control input { position: absolute; width: 1px; height: 1px; opacity: 0; }
        .file-control strong { font-size: 12px; }
        .file-control small { color: var(--backup-muted); font-size: 9px; }
        .confirmation-input {
            width: 100%;
            min-height: 44px;
            padding: 0 12px;
            color: var(--backup-ink);
            background: #fff;
            border: 1px solid #afc3d5;
            border-radius: 7px;
            outline: none;
        }
        .confirmation-input:focus { border-color: var(--backup-red); box-shadow: 0 0 0 3px rgba(213, 47, 63, .12); }
        .danger-note { margin: 0 0 13px; padding: 10px 11px; color: #88400d; background: var(--backup-amber-soft); border: 1px solid #f2cd86; border-radius: 6px; font-size: 10px; line-height: 1.5; }
        .button {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 16px;
            color: #fff;
            background: var(--button-color, var(--backup-blue));
            border: 1px solid var(--button-color, var(--backup-blue));
            border-radius: 7px;
            box-shadow: 0 8px 18px color-mix(in srgb, var(--button-color, var(--backup-blue)) 20%, transparent);
            cursor: pointer;
            font-size: 12px;
            font-weight: 850;
            text-decoration: none;
            transition: filter .16s ease, transform .16s ease;
        }
        .button:hover { filter: brightness(.91); transform: translateY(-1px); }
        .button:disabled { cursor: wait; filter: grayscale(.35); opacity: .65; transform: none; }
        .button-green { --button-color: var(--backup-green); }
        .button-red { --button-color: var(--backup-red); }
        .button-blue { --button-color: var(--backup-blue); }
        .button-small { min-height: 36px; padding: 0 12px; font-size: 10px; }
        .action-panel .button { width: 100%; }

        .history-panel { overflow: hidden; background: #fff; border: 1px solid var(--backup-line); border-radius: 8px; box-shadow: 0 12px 30px rgba(19, 54, 84, .06); }
        .history-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 19px 22px; border-bottom: 1px solid var(--backup-line); }
        .history-heading h2 { margin: 0 0 3px; font-size: 19px; }
        .history-heading p { margin: 0; color: var(--backup-muted); font-size: 10px; }
        .history-count { padding: 6px 9px; color: #0b579f; background: #edf7ff; border: 1px solid #b9d9f2; border-radius: 6px; font-size: 10px; font-weight: 850; }
        .backup-list { display: grid; }
        .backup-item { display: grid; grid-template-columns: minmax(0, 1fr) auto auto auto; align-items: center; gap: 18px; padding: 14px 20px; border-bottom: 1px solid #e6eef4; }
        .backup-item:last-child { border-bottom: 0; }
        .backup-item:hover { background: #f8fbfd; }
        .backup-name { min-width: 0; display: grid; gap: 3px; }
        .backup-name strong { overflow: hidden; font-size: 12px; text-overflow: ellipsis; white-space: nowrap; }
        .backup-name small, .backup-meta { color: var(--backup-muted); font-size: 10px; }
        .backup-meta { white-space: nowrap; }
        .empty-state { padding: 42px 20px; color: var(--backup-muted); text-align: center; }
        .empty-state strong { display: block; margin-bottom: 4px; color: var(--backup-ink); font-size: 15px; }

        @keyframes backup-spin { to { transform: rotate(360deg); } }

        @media (min-width: 1700px) {
            .actions-grid { grid-template-columns: minmax(480px, .8fr) minmax(560px, 1.2fr); }
        }
        @media (max-width: 920px) {
            .app-content { padding: 82px 14px 96px; }
            .actions-grid { grid-template-columns: 1fr; }
            .page-header { align-items: flex-start; }
        }
        @media (max-width: 680px) {
            .app-content { padding-inline: 10px; }
            .page-header { display: grid; padding: 19px; }
            .engine-state { width: 100%; max-width: none; }
            .action-panel { padding: 18px; }
            .backup-item { grid-template-columns: minmax(0, 1fr) auto; gap: 8px 12px; }
            .backup-item .button { grid-column: 1 / -1; width: 100%; }
            .backup-item .backup-meta { text-align: right; }
            .history-heading { align-items: flex-start; }
        }
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="backup-page">
            <header class="page-header">
                <div>
                    <h1>Respaldos de base de datos</h1>
                    <p>Crea, descarga y restaura copias completas sin cargar todo el archivo en memoria.</p>
                </div>
                <div class="engine-state {{ $capabilities['native'] ? '' : 'is-compatible' }}">
                    <i></i>
                    <span>
                        <strong>{{ $capabilities['native'] ? 'Motor rápido disponible' : 'Motor compatible disponible' }}</strong>
                        <small>{{ $capabilities['engine'] }} · {{ $capabilities['server_version'] }}</small>
                    </span>
                </div>
            </header>

            @if(session('success'))
                <div class="flash flash-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="flash flash-error">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="flash flash-error">
                    <ul>
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="operation-status" id="backupOperationStatus" hidden aria-live="polite">
                <span class="operation-spinner"></span>
                <div>
                    <strong id="backupOperationTitle">Procesando...</strong>
                    <p id="backupOperationMessage">No cierres esta ventana hasta que termine la operación.</p>
                </div>
            </div>

            <section class="actions-grid">
                <article class="action-panel backup">
                    <div class="panel-heading">
                        <span class="panel-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>
                        </span>
                        <div>
                            <h2>Crear respaldo</h2>
                            <p>Genera una copia SQL completa y comienza la descarga al terminar.</p>
                        </div>
                    </div>
                    <ul class="backup-facts">
                        <li>Se escribe directamente en disco para ahorrar memoria.</li>
                        <li>Incluye estructura, usuarios, inventario y movimientos.</li>
                        <li>Los respaldos anteriores permanecen disponibles abajo.</li>
                    </ul>
                    <form method="POST" action="{{ route('admin.backups.store') }}" id="backupCreateForm">
                        @csrf
                        <button class="button button-green" type="submit">Crear y descargar respaldo</button>
                    </form>
                </article>

                <article class="action-panel restore">
                    <div class="panel-heading">
                        <span class="panel-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4v6h6M5.5 15a8 8 0 1 0 .6-7.8L4 10"/></svg>
                        </span>
                        <div>
                            <h2>Restaurar respaldo</h2>
                            <p>Reemplaza la base actual y aplica las migraciones que falten en copias antiguas.</p>
                        </div>
                    </div>
                    <p class="danger-note">
                        Antes de restaurar, el sistema crea automáticamente una copia de seguridad del estado actual.
                    </p>
                    <form method="POST" action="{{ route('admin.backups.restore') }}" enctype="multipart/form-data" id="backupRestoreForm">
                        @csrf
                        <div class="field">
                            <label for="backup_sql">Archivo SQL</label>
                            <label class="file-control" for="backup_sql">
                                <input type="file" id="backup_sql" name="backup_sql" accept=".sql,.txt" required>
                                <strong id="backupFileName">Seleccionar respaldo .sql</strong>
                                <small id="backupFileHelp">Máximo {{ $maximumUploadMegabytes }} MB</small>
                            </label>
                        </div>
                        <div class="field">
                            <label for="confirmation">Confirmación</label>
                            <input
                                class="confirmation-input"
                                type="text"
                                id="confirmation"
                                name="confirmation"
                                autocomplete="off"
                                placeholder="Escribe RESTAURAR"
                                required
                            >
                        </div>
                        <button class="button button-red" type="submit">Restaurar base de datos</button>
                    </form>
                </article>
            </section>

            <section class="history-panel">
                <div class="history-heading">
                    <div>
                        <h2>Respaldos guardados</h2>
                        <p>Las copias quedan protegidas en el almacenamiento privado del sistema.</p>
                    </div>
                    <span class="history-count">{{ $backups->count() }} archivos</span>
                </div>
                @if($backups->isEmpty())
                    <div class="empty-state">
                        <strong>Todavía no hay respaldos guardados</strong>
                        <span>Crea el primero desde el botón verde.</span>
                    </div>
                @else
                    <div class="backup-list">
                        @foreach($backups as $backup)
                            <article class="backup-item">
                                <div class="backup-name">
                                    <strong>{{ $backup['name'] }}</strong>
                                    <small>Copia SQL completa</small>
                                </div>
                                <span class="backup-meta">{{ $backup['size_label'] }}</span>
                                <time class="backup-meta" datetime="{{ date(DATE_ATOM, $backup['modified_at']) }}">{{ $backup['modified_label'] }}</time>
                                <a class="button button-blue button-small" href="{{ route('admin.backups.download', $backup['name']) }}">Descargar</a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </main>
</div>
<script>
    (() => {
        const createForm = document.getElementById('backupCreateForm');
        const restoreForm = document.getElementById('backupRestoreForm');
        const fileInput = document.getElementById('backup_sql');
        const fileName = document.getElementById('backupFileName');
        const fileHelp = document.getElementById('backupFileHelp');
        const status = document.getElementById('backupOperationStatus');
        const statusTitle = document.getElementById('backupOperationTitle');
        const statusMessage = document.getElementById('backupOperationMessage');
        const maximumBytes = {{ $maximumUploadMegabytes * 1024 * 1024 }};
        const chunkUploadUrl = @json(route('admin.backups.restore.chunk'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const chunkSize = 4 * 1024 * 1024;

        const showStatus = (state, title, message) => {
            status.hidden = false;
            status.classList.remove('is-success', 'is-error');
            if (state) status.classList.add(`is-${state}`);
            statusTitle.textContent = title;
            statusMessage.textContent = message;
            status.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        };

        const responseMessage = async (response) => {
            const raw = await response.text();
            let data = {};
            try {
                data = raw ? JSON.parse(raw) : {};
            } catch (_) {
                // Los errores del servidor web pueden llegar como HTML.
            }

            if (response.ok) return data;
            if (response.status === 413) {
                throw new Error('El servidor rechazó el archivo por su tamaño. Reinicia Herd para aplicar el nuevo límite y vuelve a intentarlo.');
            }
            if ([401, 419].includes(response.status)) {
                throw new Error('Tu sesión terminó mientras se procesaba el respaldo. Recarga la página, inicia sesión y vuelve a intentarlo.');
            }
            if (response.status === 403) {
                throw new Error('Tu usuario ya no tiene permiso de administrador en la base restaurada.');
            }
            if ([502, 503, 504].includes(response.status)) {
                throw new Error('El servidor tardó demasiado o está reiniciando. Espera 30 segundos y recarga Respaldos para comprobar el resultado.');
            }

            const validation = Object.values(data.errors || {}).flat()[0];
            throw new Error(
                validation
                || data.message
                || `El servidor no entregó el detalle del error (HTTP ${response.status}). Revisa storage/logs/laravel.log.`,
            );
        };

        const submitAsync = async (form, options) => {
            const button = form.querySelector('button[type="submit"]');
            if (options.confirm && !window.confirm(options.confirm)) return;

            button.disabled = true;
            showStatus('', options.title, options.progress);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await responseMessage(response);
                showStatus('success', options.successTitle, data.message);
                options.onSuccess?.(data);
            } catch (error) {
                const message = error instanceof TypeError
                    ? 'Se perdió la comunicación con el servidor. Espera 30 segundos y recarga la página para comprobar si terminó.'
                    : (error.message || 'Ocurrió un error inesperado.');
                showStatus('error', 'No se completó la operación', message);
            } finally {
                button.disabled = false;
            }
        };

        const uploadInChunks = async (file) => {
            const randomBytes = new Uint8Array(16);
            if (window.crypto?.getRandomValues) {
                window.crypto.getRandomValues(randomBytes);
            } else {
                randomBytes.forEach((_, index) => {
                    randomBytes[index] = Math.floor(Math.random() * 256);
                });
            }
            const uploadId = Array.from(randomBytes)
                .map((value) => value.toString(16).padStart(2, '0'))
                .join('');
            const totalChunks = Math.ceil(file.size / chunkSize);

            for (let index = 0; index < totalChunks; index++) {
                const start = index * chunkSize;
                const chunk = file.slice(start, Math.min(start + chunkSize, file.size));
                const body = new FormData();
                body.append('_token', csrfToken);
                body.append('backup_chunk', chunk, `chunk-${index}.part`);
                body.append('upload_id', uploadId);
                body.append('chunk_index', index);
                body.append('total_chunks', totalChunks);
                body.append('backup_name', file.name);
                body.append('total_size', file.size);

                const percentage = Math.round((index / totalChunks) * 100);
                showStatus(
                    '',
                    'Subiendo respaldo',
                    `Enviando bloque ${index + 1} de ${totalChunks} (${percentage}%). No cierres esta ventana.`,
                );

                const response = await fetch(chunkUploadUrl, {
                    method: 'POST',
                    body,
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                await responseMessage(response);
            }

            return uploadId;
        };

        createForm?.addEventListener('submit', (event) => {
            event.preventDefault();
            submitAsync(createForm, {
                title: 'Creando respaldo',
                progress: 'MySQL está escribiendo la copia directamente en disco. Esto normalmente tarda unos segundos.',
                successTitle: 'Respaldo terminado',
                onSuccess: (data) => {
                    if (!data.download_url) return;
                    const download = document.createElement('a');
                    download.href = data.download_url;
                    download.hidden = true;
                    document.body.append(download);
                    download.click();
                    download.remove();
                    window.setTimeout(() => window.location.reload(), 2500);
                },
            });
        });

        restoreForm?.addEventListener('submit', async (event) => {
            event.preventDefault();
            const selected = fileInput.files?.[0];
            if (!selected) {
                showStatus('error', 'Falta el respaldo', 'Selecciona el archivo SQL que deseas restaurar.');
                return;
            }
            if (selected.size > maximumBytes) {
                showStatus('error', 'Archivo demasiado grande', `El respaldo supera el límite de {{ $maximumUploadMegabytes }} MB.`);
                return;
            }
            if (document.getElementById('confirmation').value.trim() !== 'RESTAURAR') {
                showStatus('error', 'Falta la confirmación', 'Escribe exactamente RESTAURAR antes de continuar.');
                return;
            }
            if (!window.confirm('Se reemplazará la base actual. El sistema creará primero un respaldo de seguridad. ¿Continuar?')) {
                return;
            }

            const button = restoreForm.querySelector('button[type="submit"]');
            button.disabled = true;

            try {
                const uploadToken = await uploadInChunks(selected);
                showStatus(
                    '',
                    'Restaurando base de datos',
                    'La carga terminó. Se está protegiendo la base actual y aplicando el respaldo recibido.',
                );

                const body = new FormData();
                body.append('_token', csrfToken);
                body.append('upload_token', uploadToken);
                body.append('backup_name', selected.name);
                body.append('confirmation', 'RESTAURAR');

                const response = await fetch(restoreForm.action, {
                    method: 'POST',
                    body,
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await responseMessage(response);
                showStatus('success', 'Restauración terminada', data.message);
                window.setTimeout(() => {
                    window.location.href = data.redirect_url || @json(route('admin.backups.index'));
                }, 2200);
            } catch (error) {
                const message = error instanceof TypeError
                    ? 'Se perdió la comunicación con el servidor. Espera 30 segundos y recarga la página para comprobar si terminó.'
                    : (error.message || 'Ocurrió un error inesperado.');
                showStatus('error', 'No se completó la operación', message);
            } finally {
                button.disabled = false;
            }
        });

        fileInput?.addEventListener('change', () => {
            const selected = fileInput.files?.[0];
            if (!selected) {
                fileName.textContent = 'Seleccionar respaldo .sql';
                fileHelp.textContent = 'Máximo {{ $maximumUploadMegabytes }} MB';
                return;
            }

            const megabytes = selected.size / (1024 * 1024);
            fileName.textContent = selected.name;
            fileHelp.textContent = `${megabytes.toFixed(megabytes >= 10 ? 1 : 2)} MB · listo para validar`;
        });
    })();
</script>
</body>
</html>
