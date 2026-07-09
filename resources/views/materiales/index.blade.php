<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Inventario - Almacén</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        h1 { color: #2c3e50; margin-top: 0; margin-bottom: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        
        /* Estilos para la barra superior (Filtro y Botón) */
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        .btn-alta { background-color: #3498db; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; transition: background 0.3s; }
        .btn-alta:hover { background-color: #2980b9; }
        
        .filter-form { display: flex; gap: 10px; align-items: center; }
        .filter-form select { padding: 9px 12px; border: 1px solid #ccd0d5; border-radius: 5px; font-size: 14px; outline: none; }
        .filter-form select:focus { border-color: #3498db; }
        .btn-filter { background-color: #2c3e50; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: background 0.3s; }
        .btn-filter:hover { background-color: #1a252f; }
        .btn-clear { background-color: #95a5a6; color: white; text-decoration: none; padding: 10px 15px; border-radius: 5px; font-size: 14px; transition: background 0.3s; }
        .btn-clear:hover { background-color: #7f8c8d; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; color: #2c3e50; font-weight: 600; }
        tr:hover { background-color: #f1f2f6; }
        
        .img-material { width: 70px; height: 70px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; background-color: #f9f9f9; }
        .no-photo { width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; font-size: 11px; color: #95a5a6; background-color: #f1f2f6; border-radius: 6px; border: 1px dashed #bdc3c7; }
        
        .badge { padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 0.9em; display: inline-block; }
        .badge-success { background-color: #2ecc71; color: white; }
        .badge-danger { background-color: #e74c3c; color: white; }
        .badge-category { background-color: #e67e22; color: white; font-size: 0.85em; }
    </style>
</head>
<body>

<div class="container">
    <h1>Consulta de Materiales en Almacén</h1>
    
    <div class="top-bar">
        <a href="{{ route('materiales.create') }}" class="btn-alta">+ Registrar Entrada de Material</a>

        <form action="{{ route('materiales.index') }}" method="GET" class="filter-form">
            <select name="filtrar_categoria">
                <option value="">-- Ver Todas las Categorías --</option>
                <option value="EQUIPO ACERO AL CARBON" {{ request('filtrar_categoria') == 'EQUIPO ACERO AL CARBON' ? 'selected' : '' }}>EQUIPO ACERO AL CARBON</option>
                <option value="EQUIPO ACERO INOXIDABLE" {{ request('filtrar_categoria') == 'EQUIPO ACERO INOXIDABLE' ? 'selected' : '' }}>EQUIPO ACERO INOXIDABLE</option>
                <option value="EQUIPO TIPO ASA INOXIDABLE" {{ request('filtrar_categoria') == 'EQUIPO TIPO ASA INOXIDABLE' ? 'selected' : '' }}>EQUIPO TIPO ASA INOXIDABLE</option>
                <option value="EQUIPO AC SIST DSPCH MEC FILL" {{ request('filtrar_categoria') == 'EQUIPO AC SIST DSPCH MEC FILL' ? 'selected' : '' }}>EQUIPO AC SIST DSPCH MEC FILL</option>
                <option value="EQUIPO AC SIST DSPCH MEC LIQUID" {{ request('filtrar_categoria') == 'EQUIPO AC SIST DSPCH MEC LIQUID' ? 'selected' : '' }}>EQUIPO AC SIST DSPCH MEC LIQUID</option>
                <option value="EQUIPO ACERO AL CARBON UPV" {{ request('filtrar_categoria') == 'EQUIPO ACERO AL CARBON UPV' ? 'selected' : '' }}>EQUIPO ACERO AL CARBON UPV</option>
            </select>
            <button type="submit" class="btn-filter">Filtrar</button>
            
            @if(request('filtrar_categoria'))
                <a href="{{ route('materiales.index') }}" class="btn-clear">Limpiar</a>
            @endif
        </form>
    </div>

    @if(session('success'))
        <div style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            {{ session('success') }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Fotografía</th>
                <th>Categoría</th>
                <th>No. de Parte / Código</th>
                <th>Descripción</th>
                <th>Marca</th>
                <th>Proveedor</th>
                <th>Stock Actual</th>
            </tr>
        </thead>
        <tbody>
            @forelse($materiales as $material)
                <tr>
                    <td>
                        @if($material->fotografia)
                            <img src="{{ asset('storage/' . $material->fotografia) }}" class="img-material" alt="Foto">
                        @else
                            <div class="no-photo">Sin foto</div>
                        @endif
                    </td>
                    <td><span class="badge badge-category">{{ $material->categoria }}</span></td>
                    <td><strong>{{ $material->numero_parte ?? 'N/A' }}</strong></td>
                    <td>{{ $material->descripcion }}</td>
                    <td>{{ $material->marca ?? 'N/A' }}</td>
                    <td>{{ $material->proveedor ?? 'N/A' }}</td>
                    <td>
                        <span class="badge {{ $material->stock > 0 ? 'badge-success' : 'badge-danger' }}">
                            {{ $material->stock }} pzas
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #7f8c8d; padding: 40px;">
                        No se encontraron materiales registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

</body>
</html>