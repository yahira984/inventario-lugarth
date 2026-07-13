<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Material - AppLugarth</title>
    <style>
        /* --- ESTILOS ULTRA-FUTURISTAS MASTER (Modo Edición) --- */
        :root {
            --bg: #030712; 
            --surface: rgba(15, 23, 42, 0.7); 
            --ink: #ffffff; 
            --muted: #94a3b8; 
            --cyan-glow: #06b6d4;
            --blue-glow: #3b82f6;
            --line: rgba(56, 189, 248, 0.2);
            --shadow-glass: 0 10px 40px rgba(0, 0, 0, 0.6); 
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at top left, #0a192f 0%, #030712 100%);
            color: var(--ink);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .app-shell { display: flex; height: 100vh; width: 100vw; overflow: hidden; }
        .app-content { flex: 1; padding: 40px 20px; overflow-y: auto; display: flex; justify-content: center; }

        /* --- CONTENEDOR DEL FORMULARIO --- */
        .container {
            width: 100%;
            max-width: 900px;
            background: var(--surface);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: var(--shadow-glass), inset 0 0 20px rgba(56, 189, 248, 0.05);
            padding: 40px;
        }

        .page-header { margin-bottom: 30px; border-bottom: 1px solid var(--line); padding-bottom: 20px; }

        .page-header h1 {
            margin: 0 0 8px 0;
            background: linear-gradient(to right, #00f2fe, #4facfe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 32px;
            font-weight: 900;
            letter-spacing: 1px;
            text-shadow: 0 0 20px rgba(0, 242, 254, 0.2); 
        }

        .page-header p { margin: 0; color: var(--muted); font-size: 14px; }

        /* --- ALERTAS --- */
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #ef4444;
            color: #fca5a5;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
        }

        /* --- FORMULARIOS --- */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        .form-group { display: flex; flex-direction: column; margin-bottom: 24px; }
        .form-group.full { grid-column: span 2; }
        
        label { color: #38bdf8; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }

        input, select, textarea {
            width: 100%;
            padding: 14px 16px;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #ffffff;
            font-size: 14px;
            outline: none;
            transition: all 0.3s;
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--cyan-glow);
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.3);
            background: rgba(0, 0, 0, 0.6);
        }

        /* --- BOTONES --- */
        .form-actions { display: flex; gap: 16px; margin-top: 32px; }

        .btn-save {
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 16px 32px;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 14px;
            cursor: pointer;
            flex: 1;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
        }

        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(37, 99, 235, 0.6); }

        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            color: #94a3b8;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 14px 24px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-back:hover { border-color: #fff; color: #fff; }

        /* --- COMPONENTES ADICIONALES --- */
        .input-group { display: flex; gap: 12px; }
        .btn-scan {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 0 24px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s;
        }
        .code-status { margin-top: 10px; padding: 12px; border-radius: 8px; font-size: 13px; font-weight: 600; display: none; }
        .code-status.success { display: block; background: rgba(16, 185, 129, 0.15); color: #6ee7b7; border-left: 3px solid #10b981; }
        .code-status.error { display: block; background: rgba(239, 68, 68, 0.15); color: #f87171; border-left: 3px solid #ef4444; }

        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .form-group.full { grid-column: span 1; }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        @include('materiales.partials.sidebar')
        <main class="app-content">
            <div class="container">
                <div class="page-header">
                    <h1>Editar Material</h1>
                    <p>Actualiza datos, stock y código de barras.</p>
                </div>

                @if ($errors->any())
                    <div class="alert-danger">
                        <strong>Revisa estos datos:</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('materiales.update', $material) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="form-grid">
                        <div class="form-group full">
                            <label>Código de Barras</label>
                            <div class="input-group">
                                <input type="text" name="codigo_barras" id="codigo_barras" value="{{ old('codigo_barras', $material->codigo_barras) }}">
                                <button type="button" class="btn-scan" onclick="abrirEscaner()">Escanear</button>
                            </div>
                            <div id="codigo_status" class="code-status"></div>
                        </div>

                        <div class="form-group full">
                            <label>Descripción *</label>
                            <textarea name="descripcion" required>{{ old('descripcion', $material->descripcion) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label>Categoría *</label>
                            <select name="categoria" required>
                                @foreach(['IMPORTADO XML','EQUIPO ACERO AL CARBON','EQUIPO ACERO INOXIDABLE','EQUIPO TIPO ASA INOXIDABLE','EQUIPO AC SIST DSPCH MEC FILL','EQUIPO AC SIST DSPCH MEC LIQUID','EQUIPO ACERO AL CARBON UPV'] as $cat)
                                    <option value="{{ $cat }}" {{ old('categoria', $material->categoria) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>No. de Parte</label>
                            <input type="text" name="numero_parte" value="{{ old('numero_parte', $material->numero_parte) }}">
                        </div>

                        <div class="form-group">
                            <label>Marca</label>
                            <input type="text" name="marca" value="{{ old('marca', $material->marca) }}">
                        </div>

                        <div class="form-group">
                            <label>Proveedor</label>
                            <input type="text" name="proveedor" value="{{ old('proveedor', $material->proveedor) }}">
                        </div>

                        <div class="form-group">
                            <label>Stock *</label>
                            <input type="number" name="stock" value="{{ old('stock', $material->stock) }}" required>
                        </div>

                        <div class="form-group">
                            <label>Fotografía</label>
                            <input type="file" name="fotografia" accept="image/*">
                            <!-- Aquí está lo que faltaba: mostrar la imagen si existe -->
                            @if($material->fotografia)
                            <div style="margin-bottom: 12px; border: 1px solid rgba(255,255,255,0.1); padding: 8px; border-radius: 10px; display: inline-block;">
                            <img src="{{ asset('storage/' . $material->fotografia) }}" alt="Foto actual" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; display: block;">
                            <span style="font-size: 10px; color: var(--muted); margin-top: 5px; display: block;">Foto actual cargada</span>
                        </div>
                        @endif
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('materiales.index') }}" class="btn-back">Cancelar</a>
                        <button type="submit" class="btn-save">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <!-- (Modal scanner omitido por brevedad, usa el mismo de la pantalla anterior) -->
</body>
</html>