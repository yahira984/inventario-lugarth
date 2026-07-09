<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Material - Inventario</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 40px 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 25px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
        }
        input[type="text"],
        input[type="number"],
        textarea,
        select, /* Añadí el select aquí para que tome el mismo estilo limpio */
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccd0d5;
            border-radius: 5px;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        input[type="file"] {
            background-color: #f8f9fa;
            padding: 10px;
            cursor: pointer;
        }
        .btn-submit {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background-color: #27ae60;
        }
        .btn-cancel {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #7f8c8d;
            text-decoration: none;
            font-weight: 500;
        }
        .btn-cancel:hover {
            color: #e74c3c;
            text-decoration: underline;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
            margin-bottom: 20px;
        }
        .alert-danger ul {
            margin: 0;
            padding-left: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Registrar Nuevo Material</h2>

    @if ($errors->any())
        <div class="alert-danger">
            <strong>Ocurrió un error:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('materiales.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="form-group">
            <label for="categoria">Categoría / Tipo de Equipo *</label>
            <select name="categoria" id="categoria" required>
                <option value="">-- Selecciona una Categoría --</option>
                <option value="EQUIPO ACERO AL CARBON">EQUIPO ACERO AL CARBON</option>
                <option value="EQUIPO ACERO INOXIDABLE">EQUIPO ACERO INOXIDABLE</option>
                <option value="EQUIPO TIPO ASA INOXIDABLE">EQUIPO TIPO ASA INOXIDABLE</option>
                <option value="EQUIPO AC SIST DSPCH MEC FILL">EQUIPO AC SIST DSPCH MEC FILL</option>
                <option value="EQUIPO AC SIST DSPCH MEC LIQUID">EQUIPO AC SIST DSPCH MEC LIQUID</option>
                <option value="EQUIPO ACERO AL CARBON UPV">EQUIPO ACERO AL CARBON UPV</option>
            </select>
        </div>

        <div class="form-group">
            <label for="numero_parte">No. de Parte / Código</label>
            <input type="text" name="numero_parte" id="numero_parte" placeholder="Ej. 3176MS">
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción del Material *</label>
            <textarea name="descripcion" id="descripcion" placeholder="Detalles del componente..." required></textarea>
        </div>

        <div class="form-group">
            <label for="marca">Marca</label>
            <input type="text" name="marca" id="marca" placeholder="Ej. BETTS">
        </div>

        <div class="form-group">
            <label for="proveedor">Proveedor</label>
            <input type="text" name="proveedor" id="proveedor" placeholder="Ej. Promotora Industrial RG">
        </div>

        <div class="form-group">
            <label for="stock">Cantidad de Entrada (Stock) *</label>
            <input type="number" name="stock" id="stock" placeholder="0" min="0" required>
        </div>
        
        <div class="form-group">
            <label for="fotografia">Fotografía (Opcional)</label>
            <input type="file" name="fotografia" id="fotografia" accept="image/*">
        </div>
        
        <button type="submit" class="btn-submit">Guardar Material en Inventario</button>
        
        <a href="{{ route('materiales.index') }}" class="btn-cancel">Cancelar y regresar al listado</a>
    </form>
</div>

</body>
</html>