<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Material</title>
    <style>
        :root {
            --bg: #edf1f5;
            --surface: #ffffff;
            --ink: #1f2933;
            --muted: #607080;
            --line: #d8e0e8;
            --blue: #2563a8;
            --blue-dark: #17426f;
            --green: #188653;
            --red: #c2413a;
            --shadow: 0 18px 45px rgba(31, 41, 51, 0.12);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--ink);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            padding: 28px 18px;
        }

        .container {
            width: min(700px, 100%);
            margin: 0 auto;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .page-header {
            padding: 24px 28px;
            background: #f8fafc;
            border-bottom: 1px solid var(--line);
        }

        .page-header h1 {
            margin: 0;
            color: var(--blue-dark);
            font-size: 24px;
        }

        .page-header p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        .form-body {
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        label {
            font-size: 13px;
            font-weight: 800;
            color: var(--blue-dark);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        select,
        textarea {
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            color: var(--ink);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 168, 0.14);
        }

        .foto-actual {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 6px;
        }

        .foto-actual img {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid var(--line);
        }

        .foto-actual span {
            font-size: 13px;
            color: var(--muted);
        }

        .form-actions {
            display: flex;
            gap: 10px;
            padding: 0 28px 28px;
        }

        .btn-save {
            flex: 1;
            padding: 12px;
            background-color: var(--blue);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 800;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s;
        }

        .btn-save:hover {
            background-color: var(--blue-dark);
            box-shadow: 0 6px 18px rgba(37, 99, 168, 0.22);
        }

        .btn-back {
            padding: 12px 20px;
            background-color: #e6ecf2;
            color: var(--ink);
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 800;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: background 0.2s;
        }

        .btn-back:hover {
            background-color: #d5dee8;
        }

        .error {
            color: var(--red);
            font-size: 13px;
            font-weight: 700;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <h1>Editar Material</h1>
        <p>Modifica los datos del material y guarda los cambios.</p>
    </div>

    <form action="{{ route('materiales.update', ['material' => $material->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-body">

            <div class="form-group">
                <label>Descripcion *</label>
                <input type="text" name="descripcion" value="{{ old('descripcion', $material->descripcion) }}" required>
                @error('descripcion') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Categoria *</label>
                <select name="categoria" required>
                    @foreach([
                        'EQUIPO ACERO AL CARBON',
                        'EQUIPO ACERO INOXIDABLE',
                        'EQUIPO TIPO ASA INOXIDABLE',
                        'EQUIPO AC SIST DSPCH MEC FILL',
                        'EQUIPO AC SIST DSPCH MEC LIQUID',
                        'EQUIPO ACERO AL CARBON UPV',
                    ] as $cat)
                        <option value="{{ $cat }}" {{ old('categoria', $material->categoria) === $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
                @error('categoria') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>No. de Parte</label>
                <input type="text" name="numero_parte" value="{{ old('numero_parte', $material->numero_parte) }}">
            </div>

            <div class="form-group">
                <label>Codigo de Barras</label>
                <input type="text" name="codigo_barras" value="{{ old('codigo_barras', $material->codigo_barras) }}">
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
                <input type="number" name="stock" value="{{ old('stock', $material->stock) }}" min="0" required>
                @error('stock') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Fotografia</label>
                @if($material->fotografia)
                    <div class="foto-actual">
                        <img src="{{ asset('storage/' . $material->fotografia) }}" alt="Foto actual">
                        <span>Foto actual. Sube una nueva para reemplazarla.</span>
                    </div>
                @endif
                <input type="file" name="fotografia" accept="image/*">
                @error('fotografia') <span class="error">{{ $message }}</span> @enderror
            </div>

        </div>

        <div class="form-actions">
            <a href="{{ route('materiales.index') }}" class="btn-back">← Cancelar</a>
            <button type="submit" class="btn-save">Guardar Cambios</button>
        </div>
    </form>
</div>

</body>
</html>