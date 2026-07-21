<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Support\VisualImageDescriptor;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class IdentificadorVisualController extends Controller
{
    private const PUNTAJE_MINIMO = 74;

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

        if ($resultados->isNotEmpty()) {
            $margen = ($descriptorFoto['foreground_ratio'] ?? 0) > 0.55 ? 5 : 10;
            $corteRelativo = max(self::PUNTAJE_MINIMO, (int) $resultados->max('puntaje_visual') - $margen);
            $resultados = $resultados
                ->filter(fn (Material $material) => $material->puntaje_visual >= $corteRelativo)
                ->values();
        }

        return $this->expandirVariantesMismaPieza($resultados)
            ->sortByDesc('puntaje_visual')
            ->take(5)
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

        if (($foto['foreground_ratio'] ?? 0) > 0.55) {
            $puntaje = min($puntaje, 59);
        }

        [$puntajeRegional, $motivosRegionales] = $this->compararRegiones(
            $foto['regions'] ?? [],
            $material['regions'] ?? []
        );

        if ($puntajeRegional > $puntaje) {
            $puntaje = $puntajeRegional;
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

        $motivos = array_merge($motivos, $motivosRegionales);

        return [$puntaje, array_slice(array_unique($motivos), 0, 4)];
    }

    /**
     * @param  array<int, array<string, mixed>>  $foto
     * @param  array<int, array<string, mixed>>  $material
     * @return array{0: int, 1: array<int, string>}
     */
    private function compararRegiones(array $foto, array $material): array
    {
        if ($foto === [] || $material === []) {
            return [0, []];
        }

        $regionesColorFoto = array_values(array_filter(
            $foto,
            fn (array $region) => (float) ($region['saturation'] ?? 0) >= 35
                && (float) ($region['pixels_ratio'] ?? 0) >= 0.02
        ));
        $comparacionColorDominante = $regionesColorFoto !== [];

        if ($comparacionColorDominante) {
            $foto = $regionesColorFoto;
            $material = array_values(array_filter(
                $material,
                fn (array $region) => (float) ($region['saturation'] ?? 0) >= 30
                    && (float) ($region['pixels_ratio'] ?? 0) >= 0.008
            ));

            if ($material === []) {
                return [0, []];
            }
        }

        $mejorPuntaje = 0;
        $mejorForma = 0.0;
        $mejorColor = 0.0;

        foreach ($foto as $regionFoto) {
            foreach ($material as $regionMaterial) {
                if ($comparacionColorDominante) {
                    $proporcionFoto = max(0.0001, (float) ($regionFoto['pixels_ratio'] ?? 0));
                    $proporcionMaterial = max(0.0001, (float) ($regionMaterial['pixels_ratio'] ?? 0));
                    $relacionTamano = $proporcionFoto / $proporcionMaterial;

                    if ($relacionTamano > 2.25 || $relacionTamano < (1 / 2.25)) {
                        continue;
                    }
                }

                $formaHu = $this->huSimilarity($regionFoto['hu'] ?? [], $regionMaterial['hu'] ?? []);
                $contorno = $this->radialSimilarity(
                    $regionFoto['radial'] ?? [],
                    $regionMaterial['radial'] ?? []
                );
                $aspecto = $this->aspectSimilarity(
                    (float) ($regionFoto['aspect_ratio'] ?? 0),
                    (float) ($regionMaterial['aspect_ratio'] ?? 0)
                );
                $relleno = max(0, 1 - (abs(
                    (float) ($regionFoto['fill_ratio'] ?? 0) - (float) ($regionMaterial['fill_ratio'] ?? 0)
                ) / 0.65));
                $relacionTamano = max(0.01, (float) ($regionFoto['pixels_ratio'] ?? 0))
                    / max(0.01, (float) ($regionMaterial['pixels_ratio'] ?? 0));
                $tamano = max(0, 1 - min(1, abs(log($relacionTamano)) / log(3)));
                $forma = ($contorno * 0.42)
                    + ($formaHu * 0.30)
                    + ($aspecto * 0.08)
                    + ($relleno * 0.05)
                    + ($tamano * 0.15);
                $color = $this->regionColorSimilarity($regionFoto, $regionMaterial);
                $puntaje = $comparacionColorDominante
                    ? (int) round((($forma * 0.58) + ($color * 0.42)) * 100)
                    : (int) round((($forma * 0.72) + ($color * 0.28)) * 100);

                $saturacionFoto = (float) ($regionFoto['saturation'] ?? 0);
                $saturacionMaterial = (float) ($regionMaterial['saturation'] ?? 0);

                if (($saturacionFoto >= 55 && $saturacionMaterial < 25)
                    || ($saturacionMaterial >= 55 && $saturacionFoto < 25)) {
                    $puntaje = min($puntaje, 68);
                }

                if ($comparacionColorDominante && $contorno < 0.58) {
                    $puntaje = min($puntaje, 70);
                }

                if ($puntaje <= $mejorPuntaje) {
                    continue;
                }

                $mejorPuntaje = min(99, $puntaje);
                $mejorForma = $forma;
                $mejorColor = $color;
            }
        }

        $motivos = [];
        if ($mejorForma >= 0.72) {
            $motivos[] = 'forma de la pieza similar';
        }
        if ($mejorColor >= 0.70) {
            $motivos[] = 'color de la pieza similar';
        }
        if ($mejorPuntaje >= self::PUNTAJE_MINIMO) {
            $motivos[] = 'pieza separada del fondo';
        }

        return [$mejorPuntaje, $motivos];
    }

    private function radialSimilarity(array $first, array $second): float
    {
        $count = count($first);
        if ($count < 12 || $count !== count($second)) {
            return 0;
        }

        $bestError = INF;
        $orientations = [$second, array_reverse($second)];

        foreach ($orientations as $candidate) {
            for ($shift = 0; $shift < $count; $shift++) {
                $squaredError = 0.0;
                $derivativeError = 0.0;

                for ($index = 0; $index < $count; $index++) {
                    $candidateIndex = ($index + $shift) % $count;
                    $previous = ($index - 1 + $count) % $count;
                    $candidatePrevious = ($candidateIndex - 1 + $count) % $count;
                    $difference = (float) $first[$index] - (float) $candidate[$candidateIndex];
                    $firstSlope = (float) $first[$index] - (float) $first[$previous];
                    $candidateSlope = (float) $candidate[$candidateIndex] - (float) $candidate[$candidatePrevious];

                    $squaredError += $difference ** 2;
                    $derivativeError += abs($firstSlope - $candidateSlope);
                }

                $rootMeanSquare = sqrt($squaredError / $count);
                $meanDerivativeError = $derivativeError / $count;
                $bestError = min($bestError, ($rootMeanSquare * 0.78) + ($meanDerivativeError * 0.22));
            }
        }

        return max(0, 1 - min(1, $bestError / 0.32));
    }

    private function huSimilarity(array $first, array $second): float
    {
        if (count($first) !== 7 || count($second) !== 7) {
            return 0;
        }

        $weights = [0.30, 0.24, 0.18, 0.14, 0.06, 0.05, 0.03];
        $distance = 0.0;

        foreach ($weights as $index => $weight) {
            $difference = abs((float) $first[$index] - (float) $second[$index]);
            $distance += $weight * min(1, $difference / 3.5);
        }

        return max(0, 1 - $distance);
    }

    private function regionColorSimilarity(array $first, array $second): float
    {
        $firstSaturation = (float) ($first['saturation'] ?? 0);
        $secondSaturation = (float) ($second['saturation'] ?? 0);

        if ($firstSaturation >= 35 && $secondSaturation >= 35) {
            $hueDifference = abs((float) ($first['hue'] ?? 0) - (float) ($second['hue'] ?? 0));
            $hueDifference = min($hueDifference, 360 - $hueDifference);
            $hueSimilarity = max(0, 1 - ($hueDifference / 100));
            $saturationSimilarity = max(0, 1 - (abs($firstSaturation - $secondSaturation) / 255));

            return ($hueSimilarity * 0.78) + ($saturationSimilarity * 0.22);
        }

        if ($firstSaturation < 50 && $secondSaturation < 50) {
            return max(0, 1 - (abs(
                (float) ($first['brightness'] ?? 0) - (float) ($second['brightness'] ?? 0)
            ) / 210));
        }

        return 0.12;
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
