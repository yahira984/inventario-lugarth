<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = Material::query();

        if ($request->has('filtrar_categoria') && $request->filtrar_categoria != '') {
            $query->where('categoria', $request->filtrar_categoria);
        }

        if ($request->has('buscar') && $request->buscar != '') {
            $termino = $request->buscar;
            $query->where(function($q) use ($termino) {
                $q->where('numero_parte', 'LIKE', '%' . $termino . '%')
                  ->orWhere('descripcion', 'LIKE', '%' . $termino . '%')
                  ->orWhere('codigo_barras', '=', $termino);
            });
        }

        $materiales = $query->get(); 
        return view('materiales.index', compact('materiales')); 
    }

    public function create()
    {
        return view('materiales.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'categoria'      => 'required|string',
            'codigo_barras'  => 'nullable|string',
            'numero_parte'   => 'nullable|string',
            'descripcion'    => 'required|string',
            'marca'          => 'nullable|string',
            'proveedor'      => 'nullable|string',
            'stock'          => 'required|integer',
            'fotografia'     => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'evidencia_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // Hasta 10MB para fotos de celular
        ]);

        $data = $request->all();

        // 1. Guardar la foto del producto normal
        if ($request->hasFile('fotografia')) {
            $data['fotografia'] = $this->comprimirYGuardar($request->file('fotografia'), 'materiales');
        }

        // 2. Guardar la foto de la EVIDENCIA (cámara)
        if ($request->hasFile('evidencia_foto')) {
            $data['evidencia_foto'] = $this->comprimirYGuardar($request->file('evidencia_foto'), 'evidencias');
        }

        Material::create($data);

        return redirect()->route('materiales.index')->with('success', 'Material registrado correctamente en el almacén.');
    }

    public function buscarPorCodigo(Request $request)
    {
        $material = Material::where('codigo_barras', $request->codigo)->first();

        if ($material) {
            return response()->json([
                'encontrado' => true,
                'categoria' => $material->categoria,
                'numero_parte' => $material->numero_parte,
                'descripcion' => $material->descripcion,
                'marca' => $material->marca,
                'proveedor' => $material->proveedor,
            ]);
        }

        return response()->json(['encontrado' => false]);
    }

    // --- Función Mágica para Comprimir Imágenes ---
    private function comprimirYGuardar($imagen, $carpetaDestino)
    {
        $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();
        $rutaAbsoluta = storage_path('app/public/' . $carpetaDestino);

        if (!file_exists($rutaAbsoluta)) {
            mkdir($rutaAbsoluta, 0777, true);
        }

        $rutaFinal = $rutaAbsoluta . '/' . $nombreImagen;
        $info = getimagesize($imagen->getRealPath());

        if ($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/jpg') {
            $img = imagecreatefromjpeg($imagen->getRealPath());
            imagejpeg($img, $rutaFinal, 40); // 40% de calidad para ahorrar espacio
            imagedestroy($img);
        } elseif ($info['mime'] == 'image/png') {
            $img = imagecreatefrompng($imagen->getRealPath());
            imagepng($img, $rutaFinal, 6); 
            imagedestroy($img);
        } else {
            $imagen->storeAs($carpetaDestino, $nombreImagen, 'public');
        }

        return $carpetaDestino . '/' . $nombreImagen;
    }
}