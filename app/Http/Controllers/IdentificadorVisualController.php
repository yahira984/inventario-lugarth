<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class IdentificadorVisualController extends Controller
{
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
            'fotografia.max' => 'La imagen no debe pesar mas de 8 MB.',
        ]);

        $archivo = $datos['fotografia'];
        $analisis = $this->analizarImagen($archivo->getRealPath(), $archivo->getMimeType());

        return view('materiales.identificador_visual', [
            'resultados' => $this->buscarMateriales($analisis),
            'analisis' => $analisis,
            'preview' => $this->previewDataUri($archivo->getRealPath(), $archivo->getMimeType()),
            'busquedaRealizada' => true,
        ]);
    }

    private function buscarMateriales(array $analisis): Collection
    {
        $terminos = $analisis['terminos'] ?? [];
        $descriptorFoto = $analisis['descriptor'] ?? [];

        $resultados = Material::query()
            ->get()
            ->map(function (Material $material) use ($terminos, $descriptorFoto) {
                $texto = $this->normalizarTexto(implode(' ', [
                    $material->numero_parte,
                    $material->codigo_barras,
                    $material->descripcion,
                    $material->marca,
                    $material->proveedor,
                    $material->categoria,
                ]));

                $puntaje = 0;
                $motivos = [];

                foreach ($terminos as $termino => $peso) {
                    if ($termino !== '' && Str::contains($texto, $termino)) {
                        $puntaje += $peso;
                        $motivos[] = "texto: {$termino}";
                    }
                }

                if ($material->fotografia) {
                    $descriptorMaterial = $this->descriptorMaterial($material->fotografia);
                    $puntosVisuales = $this->compararDescriptores($descriptorFoto, $descriptorMaterial);

                    if ($puntosVisuales > 0) {
                        $puntaje += $puntosVisuales;
                        $motivos[] = 'foto parecida';
                    }
                }

                if ($puntaje === 0 && $material->stock > 0 && $material->fotografia) {
                    $puntaje = 1;
                    $motivos[] = 'con foto en inventario';
                }

                $material->puntaje_visual = $puntaje;
                $material->motivos_visual = array_slice(array_unique($motivos), 0, 4);

                return $material;
            })
            ->filter(fn (Material $material) => $material->puntaje_visual > 0)
            ->sortByDesc('puntaje_visual')
            ->take(24)
            ->values();

        if ($resultados->isNotEmpty()) {
            return $resultados;
        }

        return Material::query()
            ->where('stock', '>', 0)
            ->latest()
            ->take(12)
            ->get()
            ->each(function (Material $material) {
                $material->puntaje_visual = 1;
                $material->motivos_visual = ['revision manual'];
            });
    }

    private function analizarImagen(string $ruta, ?string $mime = null): array
    {
        $descriptor = $this->descriptorImagen($ruta, $mime);
        $terminos = [];
        $observaciones = [];

        $color = $descriptor['color'] ?? null;
        if ($color === 'plateado') {
            $terminos += [
                'acero inoxidable' => 14,
                'inoxidable' => 12,
                'galvanizado' => 10,
                'zinc' => 8,
                'cromado' => 8,
                'acero' => 6,
            ];
            $observaciones[] = 'color metalico claro';
        } elseif ($color === 'dorado') {
            $terminos += [
                'laton' => 14,
                'bronce' => 10,
                'cobre' => 8,
                'dorado' => 6,
            ];
            $observaciones[] = 'tono dorado';
        } elseif ($color === 'oscuro') {
            $terminos += [
                'negro' => 10,
                'pavonado' => 10,
                'acero al carbon' => 8,
                'goma' => 6,
            ];
            $observaciones[] = 'pieza oscura';
        }

        $forma = $descriptor['forma'] ?? null;
        if ($forma === 'alargada') {
            $terminos += [
                'tornillo' => 16,
                'perno' => 14,
                'pija' => 12,
                'birlo' => 10,
                'esparrago' => 8,
                'varilla' => 6,
            ];
            $observaciones[] = 'forma alargada';
        } elseif ($forma === 'redonda') {
            $terminos += [
                'tuerca' => 14,
                'arandela' => 14,
                'rondana' => 12,
                'buje' => 8,
            ];
            $observaciones[] = 'forma corta o redonda';
        } elseif ($forma === 'media') {
            $terminos += [
                'conector' => 10,
                'abrazadera' => 10,
                'valvula' => 8,
                'perno' => 6,
                'tornillo' => 6,
            ];
            $observaciones[] = 'forma media';
        }

        if (($descriptor['calidad'] ?? '') !== 'ok') {
            $observaciones[] = 'lectura basica de imagen';
        }

        return [
            'descriptor' => $descriptor,
            'terminos' => $terminos,
            'observaciones' => $observaciones,
        ];
    }

    private function descriptorImagen(string $ruta, ?string $mime = null): array
    {
        $tamano = @getimagesize($ruta);
        $descriptor = [
            'calidad' => 'basica',
            'width' => $tamano[0] ?? null,
            'height' => $tamano[1] ?? null,
            'aspect_ratio' => isset($tamano[0], $tamano[1]) && $tamano[1] > 0
                ? round($tamano[0] / $tamano[1], 3)
                : null,
            'foreground_ratio' => null,
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

    private function compararDescriptores(array $foto, array $material): int
    {
        if (($foto['calidad'] ?? '') !== 'ok' || ($material['calidad'] ?? '') !== 'ok') {
            return 0;
        }

        $puntaje = 0;

        if (($foto['forma'] ?? null) && ($foto['forma'] ?? null) === ($material['forma'] ?? null)) {
            $puntaje += 14;
        }

        if (($foto['color'] ?? null) && ($foto['color'] ?? null) === ($material['color'] ?? null)) {
            $puntaje += 12;
        }

        if (($foto['aspect_ratio'] ?? null) && ($material['aspect_ratio'] ?? null)) {
            $diferencia = abs(log(max(0.01, $foto['aspect_ratio']) / max(0.01, $material['aspect_ratio'])));
            $puntaje += max(0, (int) round(12 - ($diferencia * 10)));
        }

        if (($foto['brightness'] ?? null) && ($material['brightness'] ?? null)) {
            $diferencia = abs($foto['brightness'] - $material['brightness']);
            $puntaje += max(0, (int) round(8 - ($diferencia / 24)));
        }

        return $puntaje;
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

    private function normalizarTexto(string $texto): string
    {
        return Str::of($texto)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9.]+/', ' ')
            ->squish()
            ->toString();
    }
}
