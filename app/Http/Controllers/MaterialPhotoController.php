<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialPhoto;
use App\Support\AuditLogger;
use App\Support\ImageStorage;
use App\Support\VisualEmbeddingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialPhotoController extends Controller
{
    private const MAX_PHOTOS_PER_MATERIAL = 3;

    public function __construct(private readonly VisualEmbeddingService $visualAi) {}

    public function store(Request $request, Material $material): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);
        abort_if($material->es_plantilla_equipo, 404);

        $currentPaths = collect([$material->fotografia])
            ->concat($material->photos()->pluck('path'))
            ->filter()
            ->unique()
            ->values();
        $availableSlots = max(0, self::MAX_PHOTOS_PER_MATERIAL - $currentPaths->count());

        if ($availableSlots === 0) {
            return back()->withErrors([
                'photos' => 'Este producto ya tiene sus 3 fotografias. Elimina una antes de agregar otro angulo.',
            ]);
        }

        $data = $request->validate([
            'photos' => ['required', 'array', 'min:1', 'max:'.$availableSlots],
            'photos.*' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:8192'],
            'angle' => ['nullable', 'string', 'max:80'],
        ], [
            'photos.required' => 'Selecciona al menos una fotografia.',
            'photos.max' => "Solo quedan {$availableSlots} espacios. Cada producto puede tener hasta 3 fotografias.",
        ]);

        $created = collect();
        $knownSignatures = $material->photos()
            ->pluck('visual_descriptor_signature')
            ->filter()
            ->all();
        if (filled($material->visual_descriptor_signature)) {
            $knownSignatures[] = $material->visual_descriptor_signature;
        }

        try {
            DB::transaction(function () use ($request, $material, $data, $created, &$knownSignatures): void {
                foreach ($request->file('photos') as $file) {
                    $signature = sha1_file($file->getRealPath()) ?: null;
                    if ($signature && in_array($signature, $knownSignatures, true)) {
                        continue;
                    }

                    $path = ImageStorage::storeOptimized($file, 'materiales/referencias', 1100, 68);
                    $created->push($material->photos()->create([
                        'path' => $path,
                        'angulo' => trim((string) ($data['angle'] ?? '')) ?: null,
                        'es_principal' => false,
                    ]));
                    if ($signature) {
                        $knownSignatures[] = $signature;
                    }
                }
            });
        } catch (\Throwable $exception) {
            $created->each(fn (MaterialPhoto $photo) => ImageStorage::delete($photo->path));
            throw $exception;
        }

        foreach ($created as $photo) {
            $this->visualAi->indexPhoto($photo);
        }

        if ($created->isEmpty()) {
            return back()->withErrors([
                'photos' => 'Las imagenes seleccionadas ya estaban registradas para este producto.',
            ]);
        }

        if (blank($material->fotografia)) {
            $primaryPhoto = $created->first();
            $primaryPhoto->update(['es_principal' => true]);
            $material->update(['fotografia' => $primaryPhoto->path]);
        }

        AuditLogger::registrar('Inventario', 'Fotos de referencia', "Agrego {$created->count()} fotos a {$material->descripcion}.", [
            'material_id' => $material->id,
            'photos' => $created->pluck('id')->all(),
        ], $request);

        return back()->with('success', 'Fotografias agregadas e indexadas como nuevos angulos de referencia.');
    }

    public function primary(Request $request, Material $material, MaterialPhoto $photo): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);
        abort_unless($photo->material_id === $material->id, 404);

        DB::transaction(function () use ($material, $photo): void {
            $previousPath = $material->fotografia;
            if (
                filled($previousPath)
                && $previousPath !== $photo->path
                && ! $material->photos()->where('path', $previousPath)->exists()
            ) {
                $material->photos()->create([
                    'path' => $previousPath,
                    'angulo' => 'Vista anterior',
                    'es_principal' => false,
                    'visual_descriptor' => $material->visual_descriptor,
                    'visual_descriptor_signature' => $material->visual_descriptor_signature,
                ]);
            }

            $material->photos()->update(['es_principal' => false]);
            $photo->update(['es_principal' => true]);
            $material->update(['fotografia' => $photo->path]);
        });

        return back()->with('success', 'La fotografia seleccionada ahora es la imagen principal.');
    }

    public function destroy(Request $request, Material $material, MaterialPhoto $photo): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);
        abort_unless($photo->material_id === $material->id, 404);

        if ($material->fotografia === $photo->path) {
            return back()->withErrors([
                'photos' => 'Elige otra imagen principal antes de eliminar esta fotografia.',
            ]);
        }

        $path = $photo->path;
        $photo->delete();
        ImageStorage::delete($path);

        return back()->with('success', 'Fotografia de referencia eliminada.');
    }
}
