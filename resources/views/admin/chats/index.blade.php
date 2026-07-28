<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chats internos - Inventario</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <style>
        :root {
            --chat-ink: #0b2a47;
            --chat-muted: #60778d;
            --chat-line: #d6e2ec;
            --chat-blue: #1769d2;
            --chat-green: #079669;
            --chat-orange: #d97706;
            --chat-red: #dc2626;
        }

        * { box-sizing: border-box; }
        body { margin: 0; color: var(--chat-ink); background: #f3f7fa; font-family: "Segoe UI", Tahoma, sans-serif; }
        button, select { font: inherit; }
        .app-shell { display: flex; min-height: 100vh; }
        .app-content { min-width: 0; flex: 1; padding: 34px clamp(18px, 3vw, 48px) 70px; }
        .chat-admin { width: min(1480px, 100%); margin: 0 auto; display: grid; gap: 18px; }
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 24px 26px;
            background: #fff;
            border: 1px solid var(--chat-line);
            border-radius: 8px;
            box-shadow: 0 14px 34px rgba(21, 59, 91, .07);
        }
        .page-header h1 { margin: 0 0 6px; font-size: clamp(24px, 2.4vw, 34px); }
        .page-header p { margin: 0; color: var(--chat-muted); font-size: 13px; font-weight: 600; }
        .schedule-state { display: inline-flex; align-items: center; gap: 9px; padding: 10px 12px; color: #065f46; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 7px; font-size: 12px; font-weight: 800; white-space: nowrap; }
        .schedule-state i { width: 9px; height: 9px; background: #10b981; border-radius: 50%; box-shadow: 0 0 0 4px rgba(16, 185, 129, .12); }
        .flash { padding: 13px 15px; border-radius: 7px; font-size: 13px; font-weight: 750; }
        .flash-success { color: #065f46; background: #ecfdf5; border: 1px solid #a7f3d0; }
        .flash-warning { color: #92400e; background: #fff7ed; border: 1px solid #fed7aa; }
        .flash-error { color: #991b1b; background: #fef2f2; border: 1px solid #fecaca; }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .stat {
            min-width: 0;
            padding: 18px;
            background: #fff;
            border: 1px solid var(--chat-line);
            border-top: 3px solid var(--stat-color, var(--chat-blue));
            border-radius: 8px;
            box-shadow: 0 10px 26px rgba(21, 59, 91, .05);
        }
        .stat span { display: block; color: var(--chat-muted); font-size: 10px; font-weight: 850; text-transform: uppercase; }
        .stat strong { display: block; margin-top: 7px; overflow: hidden; font-size: 24px; text-overflow: ellipsis; white-space: nowrap; }
        .stat small { display: block; margin-top: 5px; color: var(--chat-muted); font-size: 11px; }
        .management-grid { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(320px, .75fr); gap: 14px; }
        .panel { min-width: 0; padding: 22px; background: #fff; border: 1px solid var(--chat-line); border-radius: 8px; box-shadow: 0 12px 30px rgba(21, 59, 91, .06); }
        .panel h2 { margin: 0 0 5px; font-size: 20px; }
        .panel > p { margin: 0 0 18px; color: var(--chat-muted); font-size: 12px; line-height: 1.55; }
        .retention-form { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: end; gap: 10px; }
        .field { display: grid; gap: 7px; }
        .field label { color: #34516c; font-size: 10px; font-weight: 850; text-transform: uppercase; }
        .field select { width: 100%; min-height: 44px; padding: 0 12px; color: var(--chat-ink); background: #fff; border: 1px solid #afc3d5; border-radius: 7px; outline: none; }
        .field select:focus { border-color: var(--chat-blue); box-shadow: 0 0 0 3px rgba(23, 105, 210, .13); }
        .button {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 0 15px;
            color: #fff;
            background: var(--button-color, var(--chat-blue));
            border: 1px solid var(--button-color, var(--chat-blue));
            border-radius: 7px;
            box-shadow: 0 7px 16px color-mix(in srgb, var(--button-color, var(--chat-blue)) 20%, transparent);
            cursor: pointer;
            font-size: 12px;
            font-weight: 850;
            text-decoration: none;
            transition: filter .16s ease, transform .16s ease;
        }
        .button:hover { filter: brightness(.91); transform: translateY(-1px); }
        .button:focus-visible { outline: 3px solid color-mix(in srgb, var(--button-color, var(--chat-blue)) 28%, transparent); outline-offset: 2px; }
        .button-orange { --button-color: var(--chat-orange); }
        .button-teal { --button-color: #0f8c8c; }
        .button-red { --button-color: var(--chat-red); }
        .button-small { min-height: 36px; padding: 0 11px; font-size: 10px; }
        .conversation-actions { display: flex; flex-wrap: wrap; gap: 7px; }
        .conversation-actions form { margin: 0; }
        .cleanup-actions { display: grid; gap: 10px; }
        .cleanup-actions form, .cleanup-actions .button { width: 100%; }
        .cleanup-note { padding: 11px 12px; color: #6b4b16; background: #fff8e8; border: 1px solid #f7d791; border-radius: 7px; font-size: 11px; line-height: 1.45; }
        .table-panel { padding: 0; overflow: hidden; }
        .table-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 20px 22px; border-bottom: 1px solid var(--chat-line); }
        .table-heading h2 { margin: 0; }
        .table-heading span { color: var(--chat-muted); font-size: 11px; font-weight: 700; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; min-width: 880px; border-collapse: collapse; }
        th { padding: 12px 14px; color: #48637b; background: #edf5fb; border-bottom: 1px solid var(--chat-line); font-size: 10px; text-align: left; text-transform: uppercase; }
        td { padding: 14px; border-bottom: 1px solid #e7eef4; font-size: 12px; vertical-align: middle; }
        tbody tr:hover { background: #f8fbfd; }
        tbody tr:last-child td { border-bottom: 0; }
        .people { display: grid; gap: 4px; }
        .people strong { font-size: 13px; }
        .people small { color: var(--chat-muted); }
        .metric { font-weight: 850; }
        .unread { color: var(--chat-red); }
        .empty { padding: 46px 20px; color: var(--chat-muted); text-align: center; }
        .empty strong { display: block; margin-bottom: 5px; color: var(--chat-ink); font-size: 16px; }

        @media (max-width: 1050px) {
            .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .management-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 860px) {
            .app-content { padding: 82px 12px 92px; }
            .page-header { align-items: flex-start; padding: 19px; }
            .schedule-state { white-space: normal; }
        }

        @media (max-width: 680px) {
            .page-header { display: grid; }
            .stats { grid-template-columns: 1fr 1fr; }
            .stat strong { font-size: 20px; }
            .panel { padding: 18px; }
            .retention-form { grid-template-columns: 1fr; }
            .retention-form .button { width: 100%; }
            .table-panel { overflow: visible; background: transparent; border: 0; box-shadow: none; }
            .table-heading { padding: 18px; background: #fff; border: 1px solid var(--chat-line); border-radius: 8px; }
            .table-wrap { overflow: visible; }
            table, tbody, tr, td { display: block; width: 100%; min-width: 0; }
            thead { display: none; }
            tbody { display: grid; gap: 10px; margin-top: 10px; }
            tr { padding: 14px; background: #fff; border: 1px solid var(--chat-line); border-radius: 8px; box-shadow: 0 8px 20px rgba(21, 59, 91, .05); }
            td { display: grid; grid-template-columns: 112px minmax(0, 1fr); gap: 10px; padding: 8px 0; border: 0; }
            td::before { content: attr(data-label); color: #60778d; font-size: 9px; font-weight: 850; text-transform: uppercase; }
            td:last-child { display: block; padding-top: 12px; }
            td:last-child .button { width: 100%; }
        }

        @media (max-width: 420px) {
            .stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="chat-admin">
            <header class="page-header">
                <div>
                    <h1>Administración de chats</h1>
                    <p>Controla cuánto tiempo se conservan los mensajes sin revisar su contenido privado.</p>
                </div>
                <span class="schedule-state"><i></i>Limpieza diaria a las 03:30</span>
            </header>

            @if(session('success'))
                <div class="flash flash-success">{{ session('success') }}</div>
            @endif
            @if(session('warning'))
                <div class="flash flash-warning">{{ session('warning') }}</div>
            @endif
            @if($errors->any())
                <div class="flash flash-error">{{ $errors->first() }}</div>
            @endif

            <section class="stats" aria-label="Resumen del chat">
                <article class="stat" style="--stat-color:#1769d2">
                    <span>Mensajes guardados</span>
                    <strong>{{ number_format($totalMessages) }}</strong>
                    <small>{{ number_format($pinnedMessages) }} fijados y protegidos de la limpieza automática</small>
                </article>
                <article class="stat" style="--stat-color:#dc2626">
                    <span>Sin leer</span>
                    <strong>{{ number_format($unreadMessages) }}</strong>
                    <small>Mensajes pendientes de abrir</small>
                </article>
                <article class="stat" style="--stat-color:#0f8c8c">
                    <span>Espacio aproximado</span>
                    <strong>{{ $estimatedSize }}</strong>
                    <small>Incluye una estimación de índices y estructura</small>
                </article>
                <article class="stat" style="--stat-color:#d97706">
                    <span>Retención actual</span>
                    <strong>{{ $retentionLabel }}</strong>
                    <small>
                        @if($oldestMessageAt)
                            Mensaje más antiguo: {{ \Illuminate\Support\Carbon::parse($oldestMessageAt)->format('d/m/Y') }}
                        @else
                            Todavía no hay mensajes guardados
                        @endif
                    </small>
                </article>
            </section>

            <section class="management-grid">
                <article class="panel">
                    <h2>Conservación automática</h2>
                    <p>Todos los días el sistema elimina únicamente los mensajes que ya superaron el periodo seleccionado.</p>
                    <form method="POST" action="{{ route('admin.chats.retention') }}" class="retention-form">
                        @csrf
                        @method('PATCH')
                        <div class="field">
                            <label for="retention_days">Conservar mensajes durante</label>
                            <select id="retention_days" name="retention_days">
                                <option value="7" @selected($retentionDays === 7)>7 días</option>
                                <option value="30" @selected($retentionDays === 30)>30 días (recomendado)</option>
                                <option value="90" @selected($retentionDays === 90)>90 días</option>
                                <option value="0" @selected($retentionDays === 0)>No eliminar automáticamente</option>
                            </select>
                        </div>
                        <button class="button button-orange" type="submit">Guardar política</button>
                    </form>
                </article>

                <article class="panel">
                    <h2>Limpieza manual</h2>
                    <p>Úsala cuando necesites liberar espacio en ese momento.</p>
                    <div class="cleanup-actions">
                        <form method="POST" action="{{ route('admin.chats.purge') }}">
                            @csrf
                            @method('DELETE')
                            <button class="button button-teal" type="submit" onclick="return confirm('¿Eliminar ahora todos los mensajes que superaron la retención configurada?')">
                                Borrar mensajes antiguos
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.chats.clear') }}">
                            @csrf
                            @method('DELETE')
                            <button class="button button-red" type="submit" onclick="return confirm('¿Eliminar el historial normal? Los mensajes fijados se conservarán.')">
                                Borrar historial no fijado
                            </button>
                        </form>
                        <div class="cleanup-note">Las limpiezas quedan registradas en Auditoría con el administrador, la fecha y la cantidad eliminada.</div>
                    </div>
                </article>
            </section>

            <section class="panel table-panel">
                <div class="table-heading">
                    <div>
                        <h2>Conversaciones</h2>
                        <span>{{ $conversations->count() }} conversaciones con historial</span>
                    </div>
                </div>
                @if($conversations->isEmpty())
                    <div class="empty">
                        <strong>No hay conversaciones guardadas</strong>
                        <span>Cuando el equipo use el chat, aquí aparecerán únicamente sus datos generales.</span>
                    </div>
                @else
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Participantes</th>
                                    <th>Mensajes</th>
                                    <th>Sin leer</th>
                                    <th>Fijados</th>
                                    <th>Última actividad</th>
                                    <th>Espacio aprox.</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($conversations as $conversation)
                                    <tr>
                                        <td data-label="Participantes">
                                            <div class="people">
                                                <strong>{{ $conversation['first_user']->name }} y {{ $conversation['second_user']->name }}</strong>
                                                <small>{{ ucfirst($conversation['first_user']->role ?? 'usuario') }} · {{ ucfirst($conversation['second_user']->role ?? 'usuario') }}</small>
                                            </div>
                                        </td>
                                        <td data-label="Mensajes"><span class="metric">{{ number_format($conversation['total_messages']) }}</span></td>
                                        <td data-label="Sin leer"><span class="metric unread">{{ number_format($conversation['unread_messages']) }}</span></td>
                                        <td data-label="Fijados"><span class="metric">{{ number_format($conversation['pinned_messages']) }}</span></td>
                                        <td data-label="Última actividad">{{ \Illuminate\Support\Carbon::parse($conversation['last_message_at'])->format('d/m/Y H:i') }}</td>
                                        <td data-label="Espacio aprox.">{{ $conversation['estimated_size'] }}</td>
                                        <td data-label="Acción">
                                            <div class="conversation-actions">
                                                <a
                                                    class="button button-teal button-small"
                                                    href="{{ route('admin.chats.conversations.export', [$conversation['first_user'], $conversation['second_user']]) }}"
                                                >
                                                    Descargar
                                                </a>
                                                <form method="POST" action="{{ route('admin.chats.conversations.destroy', [$conversation['first_user'], $conversation['second_user']]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="button button-red button-small" type="submit" onclick="return confirm('Descarga la conversación si necesitas conservarla. ¿Eliminarla completa ahora?')">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </main>
</div>
</body>
</html>
