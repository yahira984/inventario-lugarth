<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class IdentificadorVisualController extends Controller
{
    private const PUNTAJE_MINIMO = 60;

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
        $descriptor = $this->descriptorImagen($archivo->getRealPath());

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
                $descriptorMaterial = $this->descriptorMaterial($material->fotografia);
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
        return $this->normalizarTexto($material->descripcion) . '|' . $this->normalizarTexto($material->marca);
    }

    private function normalizarTexto(?string $texto): string
    {
        $texto = trim((string) $texto);
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;

        return preg_replace('/[^A-Z0-9]+/', ' ', strtoupper($texto)) ?: '';
    }

    private function descriptorImagen(string $ruta): array
    {
        $tamano = @getimagesize($ruta);
        $descriptor = [
            'calidad' => 'basica',
            'sha1' => is_file($ruta) ? @sha1_file($ruta) : null,
            'width' => $tamano[0] ?? null,
            'height' => $tamano[1] ?? null,
            'ahash' => null,
            'dhash' => null,
            'histogram' => [],
            'aspect_ratio' => null,
            'brightness' => null,
            'color' => null,
            'forma' => null,
        ];

        if (!function_exists('imagecreatefromstring')) {
            return $descriptor;
        }

        $contenido = @file_get_contents($ruta);
        if ($contenido === false) {
            return $descriptor;
        }

        $imagen = @imagecreatefromstring($contenido);
        if (!$imagen) {
            return $descriptor;
        }

        $descriptor['ahash'] = $this->averageHash($imagen);
        $descriptor['dhash'] = $this->differenceHash($imagen);

        $originalW = imagesx($imagen);
        $originalH = imagesy($imagen);
        $max = 180;
        $escala = min($max / max($originalW, 1), $max / max($originalH, 1), 1);
        $w = max(1, (int) round($originalW * $escala));
        $h = max(1, (int) round($originalH * $escala));
        $muestra = imagecreatetruecolor($w, $h);
        imagecopyresampled($muestra, $imagen, 0, 0, 0, 0, $w, $h, $originalW, $originalH);
        imagedestroy($imagen);

        $fondo = $this->colorPromedioEsquinas($muestra, $w, $h);
        $minX = $w;
        $minY = $h;
        $maxX = 0;
        $maxY = 0;
        $pixelesObjeto = 0;
        $rTotal = 0;
        $gTotal = 0;
        $bTotal = 0;
        $brilloTotal = 0;
        $saturacionTotal = 0;
        $histograma = array_fill(0, 64, 0);

        for ($y = 0; $y < $h; $y += 2) {
            for ($x = 0; $x < $w; $x += 2) {
                [$r, $g, $b] = $this->rgbAt($muestra, $x, $y);
                $distancia = $this->distanciaColor([$r, $g, $b], $fondo);
                $brillo = ($r + $g + $b) / 3;
                $saturacion = max($r, $g, $b) - min($r, $g, $b);

                if ($distancia > 34 || ($saturacion > 42 && $distancia > 18)) {
                    $pixelesObjeto++;
                    $minX = min($minX, $x);
                    $minY = min($minY, $y);
                    $maxX = max($maxX, $x);
                    $maxY = max($maxY, $y);
                    $rTotal += $r;
                    $gTotal += $g;
                    $bTotal += $b;
                    $brilloTotal += $brillo;
                    $saturacionTotal += $saturacion;
                    $histograma[$this->histogramIndex($r, $g, $b)]++;
                }
            }
        }

        imagedestroy($muestra);

        if ($pixelesObjeto === 0) {
            return $descriptor;
        }

        $bboxW = max(1, $maxX - $minX + 1);
        $bboxH = max(1, $maxY - $minY + 1);
        $ratioObjeto = max($bboxW / $bboxH, $bboxH / $bboxW);
        $rProm = $rTotal / $pixelesObjeto;
        $gProm = $gTotal / $pixelesObjeto;
        $bProm = $bTotal / $pixelesObjeto;
        $brilloProm = $brilloTotal / $pixelesObjeto;
        $saturacionProm = $saturacionTotal / $pixelesObjeto;

        return array_merge($descriptor, [
            'calidad' => 'ok',
            'aspect_ratio' => round($ratioObjeto, 3),
            'foreground_ratio' => round($pixelesObjeto / max(1, (($w / 2) * ($h / 2))), 4),
            'brightness' => round($brilloProm, 2),
            'saturation' => round($saturacionProm, 2),
            'histogram' => array_map(fn (int $valor) => $valor / $pixelesObjeto, $histograma),
            'color' => $this->clasificarColor($rProm, $gProm, $bProm, $brilloProm, $saturacionProm),
            'forma' => $this->clasificarForma($ratioObjeto),
        ]);
    }

    private function descriptorMaterial(string $rutaRelativa): array
    {
        $ruta = storage_path('app/public/' . ltrim($rutaRelativa, '/\\'));

        if (!is_file($ruta)) {
            return [];
        }

        return $this->descriptorImagen($ruta);
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

    private function averageHash($imagen): string
    {
        $muestra = $this->resizeForHash($imagen, 8, 8);
        $grises = [];
        $total = 0;

        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                [$r, $g, $b] = $this->rgbAt($muestra, $x, $y);
                $gris = ($r * 0.299) + ($g * 0.587) + ($b * 0.114);
                $grises[] = $gris;
                $total += $gris;
            }
        }

        imagedestroy($muestra);
        $promedio = $total / 64;

        return implode('', array_map(fn (float $gris) => $gris >= $promedio ? '1' : '0', $grises));
    }

    private function differenceHash($imagen): string
    {
        $muestra = $this->resizeForHash($imagen, 9, 8);
        $bits = [];

        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                [$r1, $g1, $b1] = $this->rgbAt($muestra, $x, $y);
                [$r2, $g2, $b2] = $this->rgbAt($muestra, $x + 1, $y);
                $gris1 = ($r1 * 0.299) + ($g1 * 0.587) + ($b1 * 0.114);
                $gris2 = ($r2 * 0.299) + ($g2 * 0.587) + ($b2 * 0.114);
                $bits[] = $gris1 > $gris2 ? '1' : '0';
            }
        }

        imagedestroy($muestra);

        return implode('', $bits);
    }

    private function resizeForHash($imagen, int $w, int $h)
    {
        $muestra = imagecreatetruecolor($w, $h);
        imagecopyresampled($muestra, $imagen, 0, 0, 0, 0, $w, $h, imagesx($imagen), imagesy($imagen));

        return $muestra;
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
        if (!$a || !$b) {
            return 0;
        }

        $diferencia = abs(log(max(0.01, $a) / max(0.01, $b)));

        return max(0, 1 - min(1, $diferencia / 1.2));
    }

    private function scorePorDistancia(int $distancia, int $maximo, int $peso): float
    {
        return max(0, (1 - min($distancia, $maximo) / $maximo) * $peso);
    }

    private function histogramIndex(int $r, int $g, int $b): int
    {
        $rBin = min(3, intdiv($r, 64));
        $gBin = min(3, intdiv($g, 64));
        $bBin = min(3, intdiv($b, 64));

        return ($rBin * 16) + ($gBin * 4) + $bBin;
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
            $observaciones[] = 'tono ' . $descriptor['color'];
        }

        return $observaciones;
    }

    private function colorPromedioEsquinas($imagen, int $w, int $h): array
    {
        $puntos = [
            [0, 0],
            [max(0, $w - 1), 0],
            [0, max(0, $h - 1)],
            [max(0, $w - 1), max(0, $h - 1)],
        ];

        $total = [0, 0, 0];
        foreach ($puntos as [$x, $y]) {
            [$r, $g, $b] = $this->rgbAt($imagen, $x, $y);
            $total[0] += $r;
            $total[1] += $g;
            $total[2] += $b;
        }

        return [$total[0] / 4, $total[1] / 4, $total[2] / 4];
    }

    private function rgbAt($imagen, int $x, int $y): array
    {
        $color = imagecolorat($imagen, $x, $y);

        return [
            ($color >> 16) & 0xFF,
            ($color >> 8) & 0xFF,
            $color & 0xFF,
        ];
    }

    private function distanciaColor(array $a, array $b): float
    {
        return sqrt((($a[0] - $b[0]) ** 2) + (($a[1] - $b[1]) ** 2) + (($a[2] - $b[2]) ** 2));
    }

    private function clasificarColor(float $r, float $g, float $b, float $brillo, float $saturacion): ?string
    {
        if ($brillo < 82) {
            return 'oscuro';
        }

        if ($r > 118 && $g > 90 && $b < 95 && ($r - $b) > 38) {
            return 'dorado';
        }

        if ($saturacion < 45 && $brillo > 95) {
            return 'plateado';
        }

        return null;
    }

    private function clasificarForma(float $ratio): string
    {
        if ($ratio >= 2.45) {
            return 'alargada';
        }

        if ($ratio >= 1.45) {
            return 'media';
        }

        return 'redonda';
    }

    private function previewDataUri(string $ruta, ?string $mime): ?string
    {
        $contenido = @file_get_contents($ruta);

        if ($contenido === false || !$mime) {
            return null;
        }

        return 'data:' . $mime . ';base64,' . base64_encode($contenido);
    }
}
