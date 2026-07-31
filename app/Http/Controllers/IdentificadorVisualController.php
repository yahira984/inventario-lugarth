<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialPhoto;
use App\Models\VisualSearchFeedback;
use App\Support\VisualEmbeddingService;
use App\Support\VisualImageDescriptor;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Throwable;

class IdentificadorVisualController extends Controller
{
    private const PUNTAJE_MINIMO_CLASICO = 88;

    private const PUNTAJE_MINIMO_IA = 68;

    public function __construct(
        private readonly VisualImageDescriptor $visualDescriptor,
        private readonly VisualEmbeddingService $visualAi
    ) {}

    public function create()
    {
        $this->scheduleIncrementalIndexRepair();

        return view('materiales.identificador_visual', [
            'resultados' => collect(),
            'analisis' => null,
            'preview' => null,
            'previews' => [],
            'busquedaRealizada' => false,
            'iaActiva' => $this->visualAi->isReady(),
            'motorWarning' => null,
            'searchSignature' => null,
            'visualDiagnostics' => $this->visualDiagnostics(),
        ]);
    }

    public function search(Request $request)
    {
        $this->scheduleIncrementalIndexRepair();

        $datos = $request->validate([
            'fotografias' => ['required', 'array', 'size:2'],
            'fotografias.*' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'dimensions:max_width=8000,max_height=8000',
                'max:6144',
            ],
        ], [
            'fotografias.required' => 'Toma o selecciona dos fotografias de la misma pieza.',
            'fotografias.size' => 'Necesitamos exactamente dos fotografias de la misma pieza, desde angulos distintos.',
            'fotografias.*.image' => 'Cada archivo debe ser una imagen.',
            'fotografias.*.mimes' => 'Las imagenes deben ser JPG, JPEG, PNG o WEBP.',
            'fotografias.*.dimensions' => 'Una imagen es demasiado grande. Usa fotos de hasta 8000 x 8000 pixeles.',
            'fotografias.*.max' => 'Cada imagen debe pesar menos de 6 MB.',
        ]);

        $archivos = collect($datos['fotografias'])->values();
        $descriptors = $archivos
            ->map(fn ($archivo): array => $this->visualDescriptor->fromPath($archivo->getRealPath()))
            ->all();
        $embeddings = [];
        $motorWarning = null;

        if ($this->visualAi->isReady()) {
            try {
                if (function_exists('set_time_limit')) {
                    @set_time_limit(240);
                }

                foreach ($archivos as $archivo) {
                    $embeddings[] = $this->visualAi->fromPath($archivo->getRealPath());
                }
            } catch (Throwable $exception) {
                $embeddings = [];
                Log::warning('No se pudo usar CLIP + DINOv2 en el identificador visual.', [
                    'error' => $exception->getMessage(),
                    'diagnostico' => $this->visualAi->diagnostics(),
                ]);
                $motorWarning = 'El motor local está instalado, pero el análisis no pudo ejecutarse. Detalle: '
                    .mb_strimwidth($exception->getMessage(), 0, 280, '...');
            }
        } else {
            Log::warning('El identificador visual no tiene disponible el motor inteligente.', [
                'diagnostico' => $this->visualAi->diagnostics(),
            ]);
            $diagnostics = $this->visualAi->diagnostics();
            $missing = collect([
                ! $diagnostics['transformers'] ? 'dependencias de JavaScript' : null,
                ! $diagnostics['semantic_model'] ? 'modelo CLIP' : null,
                ! $diagnostics['detail_model'] ? 'modelo DINOv2' : null,
                $diagnostics['node_binaries'] === [] ? 'Node.js accesible desde Laravel' : null,
            ])->filter()->implode(', ');
            $motorWarning = 'El motor local no está completo. Falta: '.($missing ?: 'componente sin identificar').'.';
        }

        $previews = $archivos
            ->map(fn ($archivo) => $this->previewDataUri($archivo->getRealPath(), $archivo->getMimeType()))
            ->filter()
            ->values()
            ->all();
        $searchSignature = hash(
            'sha256',
            implode('|', collect($descriptors)->pluck('sha1')->filter()->all())
        );

        return view('materiales.identificador_visual', [
            'resultados' => $this->buscarMateriales($descriptors, $embeddings),
            'analisis' => [
                'descriptor' => $descriptors[0],
                'observaciones' => collect($descriptors)
                    ->flatMap(fn (array $descriptor): array => $this->observacionesDescriptor($descriptor))
                    ->unique()
                    ->values()
                    ->all(),
                'terminos' => [],
                'motor' => $embeddings !== [] ? 'IA visual local con 2 angulos' : 'Comparación exacta limitada',
            ],
            'preview' => $previews[0] ?? null,
            'previews' => $previews,
            'busquedaRealizada' => true,
            'iaActiva' => $embeddings !== [],
            'motorWarning' => $motorWarning,
            'searchSignature' => $searchSignature,
            'visualDiagnostics' => $this->visualDiagnostics(),
        ]);
    }

    public function feedback(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'suggested_material_id' => ['required', 'integer', 'exists:materials,id'],
            'selected_material_id' => ['nullable', 'integer', 'exists:materials,id'],
            'query_signature' => ['nullable', 'string', 'max:64'],
            'was_correct' => ['required', 'boolean'],
            'confidence' => ['nullable', 'numeric', 'between:0,100'],
        ]);

        VisualSearchFeedback::create([
            'user_id' => $request->user()?->id,
            'suggested_material_id' => $data['suggested_material_id'],
            'selected_material_id' => $data['selected_material_id'] ?? null,
            'query_signature' => $data['query_signature'] ?? null,
            'was_correct' => $data['was_correct'],
            'confidence' => isset($data['confidence']) ? ((float) $data['confidence'] / 100) : null,
            'context' => ['source' => 'visual_identifier'],
        ]);

        return response()->json([
            'ok' => true,
            'message' => $data['was_correct']
                ? 'Gracias. Esta confirmacion ayudara a ordenar mejor futuras busquedas.'
                : 'Gracias. La coincidencia dudosa quedo registrada para reducir falsos positivos.',
        ]);
    }

    public function repairIndex(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        $materials = Material::query()
            ->where('es_plantilla_equipo', false)
            ->whereNotNull('fotografia')
            ->where('fotografia', '<>', '')
            ->where(function ($query): void {
                $query->whereNull('visual_descriptor')
                    ->orWhere('visual_descriptor->ai->version', '<>', VisualEmbeddingService::VERSION);
            })
            ->limit(20)
            ->get();
        $photos = MaterialPhoto::query()
            ->where(function ($query): void {
                $query->whereNull('visual_descriptor')
                    ->orWhere('visual_descriptor->ai->version', '<>', VisualEmbeddingService::VERSION);
            })
            ->limit(max(0, 20 - $materials->count()))
            ->get();
        $indexed = 0;

        foreach ($materials as $material) {
            $indexed += $this->visualAi->indexMaterial($material) ? 1 : 0;
        }
        foreach ($photos as $photo) {
            $indexed += $this->visualAi->indexPhoto($photo) ? 1 : 0;
        }

        return back()->with(
            'success',
            $indexed > 0
                ? "Indice visual reparado: {$indexed} imagenes procesadas. Puedes repetir si aun quedan pendientes."
                : 'El indice visual ya esta actualizado o el motor local no esta disponible.'
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $descriptoresFoto
     * @param  array<int, array<string, mixed>>  $embeddingsFoto
     */
    private function buscarMateriales(array $descriptoresFoto, array $embeddingsFoto = []): Collection
    {
        $materials = Material::query()
            ->select([
                'id',
                'categoria',
                'almacen',
                'numero_parte',
                'descripcion',
                'apodo',
                'marca',
                'stock',
                'fotografia',
                'visual_descriptor',
                'visual_descriptor_signature',
            ])
            ->with('photos:id,material_id,path,visual_descriptor,visual_descriptor_signature')
            ->withCount([
                'visualFeedback as visual_correct_count' => fn ($query) => $query->where('was_correct', true),
                'visualFeedback as visual_incorrect_count' => fn ($query) => $query->where('was_correct', false),
            ])
            ->where('es_plantilla_equipo', false)
            ->whereNotNull('fotografia')
            ->where('fotografia', '<>', '')
            ->get();

        $usarAi = count($embeddingsFoto) === count($descriptoresFoto)
            && $embeddingsFoto !== []
            && $materials->contains(
            fn (Material $material) => $this->materialHasAiEmbedding($material)
                || $material->photos->contains(
                    fn (MaterialPhoto $photo) => data_get($photo->visual_descriptor, 'ai.version') === VisualEmbeddingService::VERSION
                )
        );

        $comparados = $materials
            ->map(function (Material $material) use ($descriptoresFoto, $embeddingsFoto, $usarAi) {
                $descriptorCandidates = collect([$material->visual_descriptor])
                    ->concat($material->photos->pluck('visual_descriptor'))
                    ->filter(fn ($descriptor): bool => is_array($descriptor) && $descriptor !== [])
                    ->values();
                $descriptorMaterial = $descriptorCandidates->first()
                    ?: $this->visualDescriptor->forMaterial($material);
                $descriptorCandidates = $descriptorCandidates
                    ->push($descriptorMaterial)
                    ->unique(fn (array $descriptor): string => (string) (
                        $descriptor['sha1'] ?? spl_object_id((object) $descriptor)
                    ))
                    ->values();

                $matchesByAngle = collect($descriptoresFoto)
                    ->map(fn (array $descriptorFoto, int $index): array => $this->bestCandidateScore(
                        $descriptorFoto,
                        $usarAi ? ($embeddingsFoto[$index] ?? null) : null,
                        $descriptorCandidates,
                        $usarAi
                    ));
                $scores = $matchesByAngle->pluck(0)->map(fn ($score): int => (int) $score);
                $averageScore = (float) $scores->average();
                $minimumAngleScore = (int) ($scores->min() ?? 0);
                $puntaje = (int) round(($averageScore * 0.72) + ($minimumAngleScore * 0.28));
                $motivos = $matchesByAngle
                    ->flatMap(fn (array $match): array => $match[1])
                    ->unique()
                    ->values()
                    ->all();

                if ($usarAi) {
                    $material->similitud_ia = round(
                        (float) $matchesByAngle->avg(fn (array $match): float => (float) $match[2]),
                        4
                    );
                    $material->similitud_detalle = round(
                        (float) $matchesByAngle->avg(fn (array $match): float => (float) $match[3]),
                        4
                    );
                }
                if ($scores->count() === 2 && $minimumAngleScore >= 62) {
                    $motivos[] = 'confirmada desde dos angulos';
                    $puntaje = min(99, $puntaje + 3);
                }

                $feedbackTotal = (int) $material->visual_correct_count + (int) $material->visual_incorrect_count;
                if ($feedbackTotal >= 2 && $puntaje < 100) {
                    $ratio = (int) $material->visual_correct_count / $feedbackTotal;
                    $adjustment = (int) round(($ratio - 0.5) * 10);
                    $puntaje = max(0, min(99, $puntaje + $adjustment));
                    $motivos[] = $adjustment >= 0
                        ? 'confirmada por usuarios'
                        : 'penalizada por retroalimentacion';
                }

                $material->puntaje_visual = $puntaje;
                $material->motivos_visual = $motivos;
                $material->motor_visual = $usarAi ? 'ia' : 'clasico';

                return $material;
            });

        $minimumScore = $usarAi
            ? self::PUNTAJE_MINIMO_IA
            : self::PUNTAJE_MINIMO_CLASICO;
        $comparados = $this->applyCategoryConsensus($comparados, $minimumScore, $usarAi);
        $resultados = $comparados
            ->filter(fn (Material $material) => $material->puntaje_visual >= $minimumScore)
            ->sortByDesc('puntaje_visual')
            ->values();

        if ($resultados->isNotEmpty()) {
            if ($usarAi) {
                $bestScore = (int) $resultados->first()->puntaje_visual;
                $secondScore = (int) ($resultados->get(1)?->puntaje_visual ?? 0);

                if ($bestScore < 74 && ($bestScore - $secondScore) < 5) {
                    return collect();
                }
            }

            $margen = $usarAi ? 7 : 4;
            $corteRelativo = max($minimumScore, (int) $resultados->max('puntaje_visual') - $margen);
            $resultados = $resultados
                ->filter(fn (Material $material) => $material->puntaje_visual >= $corteRelativo)
                ->values();
        }

        return $this->expandirVariantesMismaPieza($resultados)
            ->sortByDesc('puntaje_visual')
            ->take(3)
            ->values();
    }

    private function applyCategoryConsensus(
        Collection $materials,
        int $minimumScore,
        bool $useAi
    ): Collection {
        if (! $useAi || $materials->count() < 3) {
            return $materials;
        }

        $bestScore = (int) $materials->max('puntaje_visual');
        $nearby = $materials
            ->filter(fn (Material $material): bool => $material->puntaje_visual >= max(
                $minimumScore - 2,
                $bestScore - 6
            ))
            ->filter(fn (Material $material): bool => filled($material->categoria));
        $categories = $nearby
            ->groupBy(fn (Material $material): string => $this->normalizarTexto($material->categoria))
            ->map->count()
            ->sortDesc();
        $dominantCategory = (string) ($categories->keys()->first() ?? '');
        $dominantCount = (int) ($categories->first() ?? 0);
        $secondCount = (int) ($categories->values()->get(1) ?? 0);

        if ($dominantCategory === '' || $dominantCount < 2 || $dominantCount <= $secondCount) {
            return $materials;
        }

        return $materials->map(function (Material $material) use ($dominantCategory): Material {
            if ($this->normalizarTexto($material->categoria) === $dominantCategory) {
                $material->puntaje_visual = min(99, (int) $material->puntaje_visual + 3);
                $material->motivos_visual = collect($material->motivos_visual)
                    ->push('categoria respaldada por varias referencias')
                    ->unique()
                    ->values()
                    ->all();
            } else {
                $material->puntaje_visual = max(0, (int) $material->puntaje_visual - 1);
            }

            return $material;
        });
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $candidates
     * @return array{0:int,1:array<int,string>,2:float,3:float}
     */
    private function bestCandidateScore(
        array $photoDescriptor,
        ?array $photoEmbedding,
        Collection $candidates,
        bool $useAi
    ): array {
        $matches = $candidates->map(function (array $candidate) use (
            $photoDescriptor,
            $photoEmbedding,
            $useAi
        ): ?array {
            if ($this->sameImage($photoDescriptor, $candidate)) {
                return [100, ['imagen exacta'], 1.0, 1.0];
            }

            if (! $useAi || ! $photoEmbedding) {
                [$score, $reasons] = $this->compararDescriptores($photoDescriptor, $candidate);

                return [(int) $score, $reasons, 0.0, 0.0];
            }

            $semantic = data_get($candidate, 'ai.embedding');
            $detail = data_get($candidate, 'ai.detail_embedding');
            if (! is_array($semantic) || $semantic === []) {
                return null;
            }

            $semanticSimilarity = $this->visualAi->cosineSimilarity(
                $photoEmbedding['embedding'] ?? [],
                $semantic
            );
            $detailSimilarity = $this->visualAi->cosineSimilarity(
                $photoEmbedding['detail_embedding'] ?? [],
                is_array($detail) ? $detail : []
            );
            $score = $this->aiScore($semanticSimilarity, $detailSimilarity);
            $reasons = $this->aiReasons($semanticSimilarity, $detailSimilarity);

            if (($photoDescriptor['color'] ?? null)
                && ($photoDescriptor['color'] ?? null) === ($candidate['color'] ?? null)) {
                $score = min(99, $score + 2);
                $reasons[] = 'color dominante compatible';
            }
            if (($photoDescriptor['forma'] ?? null)
                && ($photoDescriptor['forma'] ?? null) === ($candidate['forma'] ?? null)) {
                $score = min(99, $score + 3);
                $reasons[] = 'forma general compatible';
            }

            return [
                $score,
                array_values(array_unique($reasons)),
                $semanticSimilarity,
                $detailSimilarity,
            ];
        })->filter()->sortByDesc(fn (array $match): int => $match[0])->values();

        return $matches->first()
            ?? [0, [$useAi ? 'pendiente de indexar con IA' : 'foto no comparable'], 0.0, 0.0];
    }

    private function materialHasAiEmbedding(Material $material): bool
    {
        return data_get($material->visual_descriptor, 'ai.version') === VisualEmbeddingService::VERSION
            && is_array(data_get($material->visual_descriptor, 'ai.embedding'));
    }

    private function sameImage(array $first, array $second): bool
    {
        return filled($first['sha1'] ?? null)
            && ($first['sha1'] ?? null) === ($second['sha1'] ?? null);
    }

    private function aiScore(float $semanticSimilarity, float $detailSimilarity): int
    {
        if ($semanticSimilarity < 0.58 || $detailSimilarity < 0.30) {
            return 0;
        }

        $semantic = max(0, min(1, ($semanticSimilarity - 0.55) / 0.35));
        $detail = max(0, min(1, ($detailSimilarity - 0.30) / 0.55));
        $confidence = ($semantic * 0.42) + ($detail * 0.58);

        return $confidence < 0.50 ? 0 : (int) round(min(99, $confidence * 100));
    }

    /**
     * @return array<int, string>
     */
    private function aiReasons(float $semanticSimilarity, float $detailSimilarity): array
    {
        $reasons = [];

        if ($semanticSimilarity >= 0.82) {
            $reasons[] = 'IA: coincidencia visual muy fuerte';
        } elseif ($semanticSimilarity >= 0.75) {
            $reasons[] = 'IA: rasgos de la pieza muy similares';
        } elseif ($semanticSimilarity >= 0.68) {
            $reasons[] = 'IA: pieza visualmente parecida';
        }

        if ($detailSimilarity >= 0.70) {
            $reasons[] = 'DINOv2 confirma detalles y estructura';
        }

        return $reasons;
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
        } elseif ($puntaje >= self::PUNTAJE_MINIMO_CLASICO) {
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
        if ($mejorPuntaje >= self::PUNTAJE_MINIMO_CLASICO) {
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

    private function visualDiagnostics(): array
    {
        $engine = $this->visualAi->diagnostics();
        $materialPhotos = Material::query()
            ->where('es_plantilla_equipo', false)
            ->whereNotNull('fotografia')
            ->where('fotografia', '<>', '')
            ->count();
        $referencePhotos = MaterialPhoto::query()->count();
        $indexedMaterials = Material::query()
            ->where('visual_descriptor->ai->version', VisualEmbeddingService::VERSION)
            ->count();
        $indexedReferences = MaterialPhoto::query()
            ->where('visual_descriptor->ai->version', VisualEmbeddingService::VERSION)
            ->count();

        return [
            ...$engine,
            'images' => $materialPhotos + $referencePhotos,
            'indexed' => $indexedMaterials + $indexedReferences,
            'pending' => max(0, ($materialPhotos + $referencePhotos) - ($indexedMaterials + $indexedReferences)),
        ];
    }

    private function scheduleIncrementalIndexRepair(): void
    {
        if (! $this->visualAi->isReady()) {
            return;
        }

        app()->terminating(function (): void {
            try {
                if (function_exists('set_time_limit')) {
                    @set_time_limit(180);
                }

                $photos = MaterialPhoto::query()
                    ->where(function ($query): void {
                        $query->whereNull('visual_descriptor')
                            ->orWhere('visual_descriptor->ai->version', '<>', VisualEmbeddingService::VERSION);
                    })
                    ->limit(2)
                    ->get();

                foreach ($photos as $photo) {
                    $this->visualAi->indexPhoto($photo);
                }

                if ($photos->count() >= 2) {
                    return;
                }

                Material::query()
                    ->where('es_plantilla_equipo', false)
                    ->whereNotNull('fotografia')
                    ->where('fotografia', '<>', '')
                    ->where(function ($query): void {
                        $query->whereNull('visual_descriptor')
                            ->orWhere('visual_descriptor->ai->version', '<>', VisualEmbeddingService::VERSION);
                    })
                    ->limit(2 - $photos->count())
                    ->get()
                    ->each(fn (Material $material) => $this->visualAi->indexMaterial($material));
            } catch (Throwable $exception) {
                Log::warning('La reparacion incremental del indice visual no pudo completarse.', [
                    'error' => $exception->getMessage(),
                ]);
            }
        });
    }
}
