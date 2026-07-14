<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios y permisos - Inventario</title>
    <style>
        :root {
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.72);
            --panel: rgba(30, 41, 59, 0.72);
            --ink: #fff;
            --muted: #94a3b8;
            --cyan: #06b6d4;
            --green: #10b981;
            --red: #ef4444;
            --line: rgba(56, 189, 248, 0.22);
        }

        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: radial-gradient(circle at top left, #0a192f 0%, var(--bg) 100%); color: var(--ink); font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
        .app-shell { display: flex; min-height: 100vh; }
        .app-content { flex: 1; padding: 34px 20px; overflow-x: hidden; }
        .container { width: min(1050px, 100%); margin: 0 auto; background: var(--surface); border: 1px solid var(--line); border-radius: 20px; box-shadow: 0 18px 55px rgba(0,0,0,.55); padding: 30px; backdrop-filter: blur(16px); }
        .page-header { border-bottom: 1px solid var(--line); padding-bottom: 18px; margin-bottom: 20px; }
        h1 { margin: 0; font-size: 32px; font-weight: 900; background: linear-gradient(to right, #00f2fe, #4facfe); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .meta { margin: 8px 0 0; color: var(--muted); font-size: 14px; font-weight: 700; }
        .alert-success, .alert-danger { border-radius: 12px; padding: 14px 16px; margin-bottom: 18px; font-weight: 800; }
        .alert-success { background: rgba(16, 185, 129, 0.12); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.3); }
        .alert-danger { background: rgba(239, 68, 68, 0.12); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }
        .table-wrap { overflow-x: auto; border: 1px solid rgba(255,255,255,.08); border-radius: 14px; }
        table { width: 100%; border-collapse: collapse; min-width: 760px; background: rgba(15, 23, 42, 0.64); }
        th { color: #7dd3fc; font-size: 12px; text-transform: uppercase; letter-spacing: .08em; text-align: left; padding: 14px; border-bottom: 1px solid var(--line); }
        td { padding: 14px; border-bottom: 1px solid rgba(255,255,255,.07); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        .user-name { font-weight: 900; }
        .muted { color: var(--muted); font-size: 12px; font-weight: 700; margin-top: 4px; }
        .role-pill { display: inline-flex; padding: 7px 11px; border-radius: 999px; font-size: 12px; font-weight: 900; text-transform: uppercase; background: rgba(6, 182, 212, .12); color: #7dd3fc; border: 1px solid rgba(6, 182, 212, .28); }
        .status-pill { display: inline-flex; padding: 7px 11px; border-radius: 999px; font-size: 12px; font-weight: 900; text-transform: uppercase; }
        .status-pill.ok { background: rgba(16, 185, 129, .13); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, .3); }
        .status-pill.pending { background: rgba(245, 158, 11, .14); color: #fcd34d; border: 1px solid rgba(245, 158, 11, .32); }
        .approval-toggle { min-height: 42px; display: inline-flex; align-items: center; gap: 8px; padding: 0 12px; border-radius: 10px; background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12); color: #e2e8f0; font-size: 12px; font-weight: 900; white-space: nowrap; }
        .approval-toggle input { width: 16px; height: 16px; accent-color: var(--green); }
        form { display: flex; gap: 10px; align-items: center; margin: 0; }
        select { min-width: 170px; min-height: 42px; border-radius: 10px; border: 1px solid rgba(255,255,255,.12); background: rgba(0,0,0,.36); color: #fff; padding: 0 12px; font-weight: 800; }
        select option { background: #0f172a; color: #fff; }
        button { min-height: 42px; border: none; border-radius: 10px; padding: 0 14px; color: #fff; background: linear-gradient(135deg, #0ea5e9, #2563eb); font-family: inherit; font-weight: 900; cursor: pointer; }
        button:hover { filter: brightness(1.08); transform: translateY(-1px); }
        .help-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
        .help-card { background: var(--panel); border: 1px solid var(--line); border-radius: 14px; padding: 14px; }
        .help-card strong { display: block; margin-bottom: 6px; }
        @media (max-width: 900px) { .help-grid { grid-template-columns: 1fr; } .app-content { padding: 18px 10px; } }
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')

    <main class="app-content">
        <div class="container">
            <div class="page-header">
                <h1>Usuarios y permisos</h1>
                <p class="meta">Define que puede hacer cada persona dentro del sistema.</p>
            </div>

            @if(session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert-danger">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <section class="help-grid">
                <div class="help-card">
                    <strong>Administrador</strong>
                    <div class="muted">Edita catalogo, importa XML, genera QR y administra usuarios.</div>
                </div>
                <div class="help-card">
                    <strong>Almacenista</strong>
                    <div class="muted">Registra entradas y salidas, imprime etiquetas existentes y consulta inventario.</div>
                </div>
                <div class="help-card">
                    <strong>Consultor</strong>
                    <div class="muted">Solo consulta dashboard, inventario, reportes e identificador visual.</div>
                </div>
            </section>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Estado</th>
                            <th>Rol actual</th>
                            <th>Aprobar y asignar rol</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuarios as $usuario)
                            <tr>
                                <td>
                                    <div class="user-name">{{ $usuario->name }}</div>
                                    <div class="muted">{{ $usuario->email }}</div>
                                </td>
                                <td>
                                    @if($usuario->aprobado())
                                        <span class="status-pill ok">Aprobado</span>
                                    @else
                                        <span class="status-pill pending">Pendiente</span>
                                    @endif
                                    <div class="muted">{{ $usuario->approved_at?->format('d/m/Y H:i') ?? 'Esperando autorizacion' }}</div>
                                </td>
                                <td><span class="role-pill">{{ $usuario->role }}</span></td>
                                <td>
                                    <form method="POST" action="{{ route('usuarios.roles.update', $usuario) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role">
                                            <option value="administrador" {{ $usuario->role === 'administrador' ? 'selected' : '' }}>Administrador</option>
                                            <option value="almacenista" {{ $usuario->role === 'almacenista' ? 'selected' : '' }}>Almacenista</option>
                                            <option value="consultor" {{ $usuario->role === 'consultor' ? 'selected' : '' }}>Consultor</option>
                                        </select>
                                        <label class="approval-toggle">
                                            <input type="checkbox" name="approved" value="1" {{ $usuario->aprobado() ? 'checked' : '' }}>
                                            Aprobar correo
                                        </label>
                                        <button type="submit">Guardar permisos</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
