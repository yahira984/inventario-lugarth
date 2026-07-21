<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorias - Inventario</title>
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    <style>
        body { margin: 0; font-family: "Segoe UI", Tahoma, sans-serif; background: #f6f8fb; color: #102033; }
        .app-shell { display: flex; min-height: 100vh; }
        .app-content { flex: 1; padding: 32px 18px; overflow-x: hidden; }
        .container { max-width: 1120px; margin: 0 auto; background: #fff; border: 1px solid #dbe5f0; border-radius: 16px; padding: 26px; box-shadow: 0 16px 40px rgba(15, 23, 42, .08); }
        .header { display: flex; justify-content: space-between; gap: 16px; align-items: flex-end; margin-bottom: 20px; }
        h1 { margin: 0 0 6px; font-size: 32px; }
        h2 { margin: 0 0 12px; font-size: 20px; }
        .muted { color: #64748b; font-size: 13px; font-weight: 600; }
        .grid { display: grid; grid-template-columns: 360px minmax(0, 1fr); gap: 18px; align-items: start; }
        .box { border: 1px solid #dbe5f0; border-radius: 14px; padding: 18px; background: #f8fafc; }
        .field { margin-bottom: 13px; }
        label { display: block; margin-bottom: 6px; color: #334155; font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: .06em; }
        input, textarea { width: 100%; min-height: 42px; border: 1px solid #cbd5e1; border-radius: 10px; padding: 10px 12px; font-family: inherit; color: #102033; background: #fff; }
        textarea { min-height: 82px; resize: vertical; }
        
        /* --- COLORES FORZADOS --- */
        .btn { min-height: 40px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; padding: 0 13px; text-decoration: none; font-weight: 900; cursor: pointer; transition: all 0.2s; border: none !important; background: linear-gradient(135deg, #8b5cf6, #5b21b6) !important; color: #fff !important; }
        .btn:hover { transform: translateY(-1px); filter: brightness(1.1); }
        .btn-soft { background: rgba(139, 92, 246, 0.15) !important; color: #8b5cf6 !important; border: 1px solid rgba(139, 92, 246, 0.3) !important; }
        .btn-soft:hover { background: rgba(139, 92, 246, 0.25) !important; color: #5b21b6 !important; border-color: #5b21b6 !important; }
        .btn-danger { background: linear-gradient(135deg, #ef4444, #b91c1c) !important; border: none !important; color: #fff !important; }
        
        .alert-success, .alert-danger { border-radius: 12px; padding: 13px 15px; margin-bottom: 16px; font-weight: 800; }
        .alert-success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .alert-danger { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .table-wrap { overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 14px; }
        table { width: 100%; min-width: 820px; border-collapse: collapse; }
        th { background: #f2f7fd; color: #335171; font-size: 11px; text-transform: uppercase; letter-spacing: .08em; text-align: left; padding: 12px; border-bottom: 1px solid #dbe5f0; }
        td { padding: 12px; border-bottom: 1px solid #edf2f7; vertical-align: top; }
        tr:last-child td { border-bottom: 0; }
        .edit-form { display: grid; grid-template-columns: minmax(180px, 1fr) minmax(180px, 1fr) auto auto; gap: 10px; align-items: center; }
        .toggle { display: inline-flex; align-items: center; gap: 7px; font-weight: 800; color: #334155; white-space: nowrap; }
        
        /* Acento púrpura en checkboxes */
        .toggle input { width: 16px; min-height: 16px; accent-color: #8b5cf6 !important; }
        
        /* Píldora de "X materiales" con colores púrpura */
        .pill { display: inline-flex; padding: 6px 10px; border-radius: 999px; background: rgba(139, 92, 246, 0.1) !important; color: #5b21b6 !important; border: 1px solid rgba(139, 92, 246, 0.3) !important; font-size: 12px; font-weight: 900; }
        
        .links { margin-top: 16px; }
        @media (max-width: 980px) { .app-content { padding-top: 76px; } .header { display: block; } .grid { grid-template-columns: 1fr; } .edit-form { grid-template-columns: 1fr; } .btn { width: 100%; } }
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')

    <main class="app-content">
        <div class="container">
            <div class="header">
                <div>
                    <h1>Categorias</h1>
                    <div class="muted">Crea y ordena las categorias que aparecen al registrar o editar materiales.</div>
                    <div class="muted">{{ $categorias->total() }} categorias registradas y sincronizadas con el inventario.</div>
                </div>
                <a href="{{ route('materiales.create') }}" class="btn btn-soft">Registrar material</a>
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

            <div class="grid">
                <section class="box">
                    <h2>Nueva categoria</h2>
                    <form method="POST" action="{{ route('admin.categorias.store') }}">
                        @csrf
                        <div class="field">
                            <label for="nombre">Nombre</label>
                            <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" placeholder="Ej. Refacciones hidraulicas" required>
                        </div>
                        <div class="field">
                            <label for="descripcion">Descripcion corta</label>
                            <textarea name="descripcion" id="descripcion" placeholder="Uso interno para el almacen">{{ old('descripcion') }}</textarea>
                        </div>
                <button type="submit" class="btn btn-green">Crear categoria</button>
                    </form>
                </section>

                <section>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Categoria</th>
                                    <th>Materiales</th>
                                    <th>Editar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categorias as $categoria)
                                    <tr>
                                        <td>
                                            <strong>{{ $categoria->nombre }}</strong>
                                            <div class="muted">{{ $categoria->descripcion ?: 'Sin descripcion' }}</div>
                                            <div class="muted">{{ $categoria->activa ? 'Activa' : 'Desactivada' }}</div>
                                        </td>
                                        <td><span class="pill">{{ $usoPorCategoria[$categoria->nombre] ?? 0 }} materiales</span></td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.categorias.update', $categoria) }}" class="edit-form">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="nombre" value="{{ $categoria->nombre }}" required>
                                                <input type="text" name="descripcion" value="{{ $categoria->descripcion }}" placeholder="Descripcion">
                                                <label class="toggle">
                                                    <input type="checkbox" name="activa" value="1" {{ $categoria->activa ? 'checked' : '' }}>
                                                    Activa
                                                </label>
                                        <button type="submit" class="btn btn-green">Guardar</button>
                                            </form>

                                            @if(($usoPorCategoria[$categoria->nombre] ?? 0) === 0)
                                                <form method="POST" action="{{ route('admin.categorias.destroy', $categoria) }}" style="margin-top:8px;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Eliminar esta categoria?')">Eliminar</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="links">{{ $categorias->links() }}</div>
                </section>
            </div>
        </div>
    </main>
</div>
</body>
</html>
