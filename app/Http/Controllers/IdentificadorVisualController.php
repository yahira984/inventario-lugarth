<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Support\VisualImageDescriptor;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class IdentificadorVisualController extends Controller
{
    private const PUNTAJE_MINIMO = 60;

    public function __construct(private readonly VisualImageDescriptor $visualDescriptor) {}

    public function create()
    {
        return view('materiales.identificador_visual', [
            'resultados' => collect(),
            'analisis' => null,
            'preview' => null,
            'busquedaRealizada' => false,
        ]);
    }

    public function search(Request $request)
    {
        $datos = $request->validate([
            'fotografia' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:8192',
            ],
        ], [
            'fotografia.required' => 'Toma una foto o selecciona una imagen para buscar sugerencias.',
            'fotografia.image' => 'El archivo debe ser una imagen.',
            'fotografia.mimes' => 'La imagen debe ser JPG, JPEG, PNG o WEBP.',
            'fotografia.max' => 'La imagen no debe pesar más de 8 MB.',
        ]);

        $archivo = $datos['fotografia'];
        $descriptor = $this->visualDescriptor->fromPath($archivo->getRealPath());

        return view('materiales.identificador_visual', [
            'resultados' => $this->buscarMateriales($descriptor),
            'analisis' => [
                'descriptor' => $descriptor,
                'observaciones' => $this->observacionesDescriptor($descriptor),
                'terminos' => [],
            ],
            'preview' => $this->previewDataUri($archivo->getRealPath(), $archivo->getMimeType()),
            'busquedaRealizada' => true,
        ]);
    }

    private function buscarMateriales(array $descriptorFoto): Collection
    {
        $comparados = Material::query()
            ->where('es_plantilla_equipo', false)
            ->whereNotNull('fotografia')
            ->where('fotografia', '<>', '')
            ->get()
            ->map(function (Material $material) use ($descriptorFoto) {
                $descriptorMaterial = $this->visualDescriptor->forMaterial($material);
                [$puntaje, $motivos] = $this->compararDescriptores($descriptorFoto, $descriptorMaterial);

                $material->puntaje_visual = $puntaje;
                $material->motivos_visual = $motivos;

                return $material;
            });

        $resultados = $comparados
            ->filter(fn (Material $material) => $material->puntaje_visual >= self::PUNTAJE_MINIMO)
            ->sortByDesc('puntaje_visual')
            ->values();

        return $this->expandirVariantesMismaPieza($resultados)
            ->sortByDesc('puntaje_visual')
            ->take(20)
            ->values();
    }

    private function expandirVariantesMismaPieza(Collection $resultados): Collection
    {
        if ($resultados->isEmpty() || (int) $resultados->max('puntaje_visual') < 90) {
            return $resultados;
        }

        $porGrupo = $resultados
            ->filter(fn (Material $material) => $material->puntaje_visual >= 90)
            ->groupBy(fn (Material $material) => $this->llavePieza($material))
            ->map(fn (Collection $grupo) => (int) $grupo->max('puntaje_visual'));

        if ($porGrupo->isEmpty()) {
            return $resultados;
        }

        $variantes = Material::query()
            ->where('es_plantilla_equipo', false)
            ->whereNotNull('fotografia')
            ->where('fotografia', '<>', '')
            ->whereNotIn('id', $resultados->pluck('id')->all())
            ->get()
            ->filter(fn (Material $material) => $porGrupo->has($this->llavePieza($material)))
            ->map(function (Material $material) use ($porGrupo) {
                $puntajeBase = (int) $porGrupo->get($this->llavePieza($material), 90);
                $material->puntaje_visual = max(90, min(99, $puntajeBase - 1));
                $material->motivos_visual = ['misma pieza en otra categoria'];

                return $material;
            });

        return $resultados->concat($variantes);
    }

    private function llavePieza(Material $material): string
    {
        return $this->normalizarTexto($material->descripcion).'|'.$this->normalizarTexto($material->marca);
    }

    private function normalizarTexto(?string $texto): string
    {
        $texto = trim((string) $texto);
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;

        return preg_replace('/[^A-Z0-9]+/', ' ', strtoupper($texto)) ?: '';
    }

    private function compararDescriptores(array $foto, array $material): array
    {
        if (($foto['calidad'] ?? '') !== 'ok' || ($material['calidad'] ?? '') !== 'ok') {
            return [0, ['foto no comparable']];
        }

        if (($foto['sha1'] ?? null) && ($foto['sha1'] ?? null) === ($material['sha1'] ?? null)) {
            return [100, ['imagen exacta']];
        }

        $aDist = $this->hammingDistance($foto['ahash'] ?? '', $material['ahash'] ?? '');
        $dDist = $this->hammingDistance($foto['dhash'] ?? '', $material['dhash'] ?? '');
        $histograma = $this->histogramSimilarity($foto['histogram'] ?? [], $material['histogram'] ?? []);
        $aspecto = $this->aspectSimilarity($foto['aspect_ratio'] ?? null, $material['aspect_ratio'] ?? null);

        $puntaje = (int) round(
            $this->scorePorDistancia($aDist, 64, 36) +
            $this->scorePorDistancia($dDist, 64, 36) +
            ($histograma * 20) +
            ($aspecto * 8)
        );

        $puntaje = min(99, max(0, $puntaje));

        if (($aDist + $dDist) <= 4 && $histograma >= 0.86) {
            $puntaje = max($puntaje, 96);
        } elseif (($aDist + $dDist) <= 9 && $histograma >= 0.75) {
            $puntaje = max($puntaje, 88);
        }

        $motivos = [];
        if ($puntaje >= 95) {
            $motivos[] = 'imagen practicamente igual';
        } elseif ($puntaje >= 80) {
            $motivos[] = 'muy parecida';
        } elseif ($puntaje >= self::PUNTAJE_MINIMO) {
            $motivos[] = 'parecida';
        }

        if (($foto['forma'] ?? null) && ($foto['forma'] ?? null) === ($material['forma'] ?? null)) {
            $motivos[] = 'forma similar';
        }

        if (($foto['color'] ?? null) && ($foto['color'] ?? null) === ($material['color'] ?? null)) {
            $motivos[] = 'color similar';
        }

        return [$puntaje, array_slice(array_unique($motivos), 0, 4)];
    }

    private function hammingDistance(string $a, string $b): int
    {
        if (strlen($a) !== strlen($b) || $a === '') {
            return 64;
        }

        $distancia = 0;
        for ($i = 0; $i < strlen($a); $i++) {
            if ($a[$i] !== $b[$i]) {
                $distancia++;
            }
        }

        return $distancia;
    }

    private function histogramSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b) || count($a) === 0) {
            return 0;
        }

        $similaridad = 0;
        foreach ($a as $indice => $valor) {
            $similaridad += min($valor, $b[$indice] ?? 0);
        }

        return min(1, max(0, $similaridad));
    }

    private function aspectSimilarity(?float $a, ?float $b): float
    {
        if (! $a || ! $b) {
            return 0;
        }

        $diferencia = abs(log(max(0.01, $a) / max(0.01, $b)));

        return max(0, 1 - min(1, $diferencia / 1.2));
    }

    private function scorePorDistancia(int $distancia, int $maximo, int $peso): float
    {
        return max(0, (1 - min($distancia, $maximo) / $maximo) * $peso);
    }

    private function observacionesDescriptor(array $descriptor): array
    {
        $observaciones = ['comparacion visual estricta'];

        if (($descriptor['forma'] ?? null) === 'alargada') {
            $observaciones[] = 'forma alargada';
        } elseif (($descriptor['forma'] ?? null) === 'redonda') {
            $observaciones[] = 'forma redonda';
        }

        if ($descriptor['color'] ?? null) {
            $observaciones[] = 'tono '.$descriptor['color'];
        }

        return $observaciones;
    }

    private function previewDataUri(string $ruta, ?string $mime): ?string
    {
        $contenido = @file_get_contents($ruta);

        if ($contenido === false || ! $mime) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($contenido);
    }
}
