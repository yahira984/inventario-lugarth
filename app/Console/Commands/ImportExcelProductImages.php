<?php

namespace App\Console\Commands;

use App\Models\Material;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportExcelProductImages extends Command
{
    protected $signature = 'lugarth:import-excel-images
        {manifest=storage/app/imports/lugarth_2025_imagenes_manifest.json : Archivo JSON generado desde el Excel}
        {--replace : Reemplaza fotos existentes en los productos}';

    protected $description = 'Asigna a cada material las fotografias extraidas del Excel de LUGARTH.';

    public function handle(): int
    {
        $manifestPath = base_path($this->argument('manifest'));

        if (! File::exists($manifestPath)) {
            $this->error("No encontre el manifiesto: {$manifestPath}");

            return self::FAILURE;
        }

        $manifest = json_decode(File::get($manifestPath), true);

        if (! is_array($manifest) || ! isset($manifest['images']) || ! is_array($manifest['images'])) {
            $this->error('El manifiesto de imagenes no tiene el formato esperado.');

            return self::FAILURE;
        }

        $resultado = [
            'actualizados' => [],
            'omitidos_con_foto' => [],
            'sin_coincidencia' => [],
            'ambiguos' => [],
            'archivo_no_encontrado' => [],
        ];

        foreach ($manifest['images'] as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $relativePath = trim((string) ($entry['fotografia'] ?? ''));
            $absoluteImage = storage_path('app/public/'.$relativePath);

            if ($relativePath === '' || ! File::exists($absoluteImage)) {
                $resultado['archivo_no_encontrado'][] = $this->entrySummary($entry);
                continue;
            }

            $match = $this->findMaterial($entry);

            if ($match['status'] === 'none') {
                $resultado['sin_coincidencia'][] = $this->entrySummary($entry);
                continue;
            }

            if ($match['status'] === 'ambiguous') {
                $resultado['ambiguos'][] = $this->entrySummary($entry);
                continue;
            }

            /** @var Material $material */
            $material = $match['material'];

            if (! $this->option('replace') && filled($material->fotografia)) {
                $resultado['omitidos_con_foto'][] = [
                    'id' => $material->id,
                    'actual' => $material->fotografia,
                    ...$this->entrySummary($entry),
                ];
                continue;
            }

            $material->forceFill(['fotografia' => $relativePath])->save();

            $resultado['actualizados'][] = [
                'id' => $material->id,
                'foto' => $relativePath,
                ...$this->entrySummary($entry),
            ];
        }

        $resultPath = storage_path('app/imports/lugarth_2025_imagenes_resultado.json');
        File::ensureDirectoryExists(dirname($resultPath));
        File::put($resultPath, json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info('Importacion de fotografias terminada.');
        $this->line('Actualizados: '.count($resultado['actualizados']));
        $this->line('Omitidos porque ya tenian foto: '.count($resultado['omitidos_con_foto']));
        $this->line('Sin coincidencia: '.count($resultado['sin_coincidencia']));
        $this->line('Coincidencias ambiguas: '.count($resultado['ambiguos']));
        $this->line('Archivos faltantes: '.count($resultado['archivo_no_encontrado']));
        $this->line("Detalle: {$resultPath}");

        return self::SUCCESS;
    }

    /**
     * @return array{status: 'one'|'none'|'ambiguous', material?: Material}
     */
    private function findMaterial(array $entry): array
    {
        $categoria = trim((string) ($entry['categoria'] ?? ''));
        $numeroParte = trim((string) ($entry['numero_parte'] ?? ''));
        $descripcion = trim((string) ($entry['descripcion'] ?? ''));

        $attempts = [];

        if ($categoria !== '' && $numeroParte !== '' && $descripcion !== '') {
            $attempts[] = Material::query()
                ->where('categoria', $categoria)
                ->where('numero_parte', $numeroParte)
                ->where('descripcion', $descripcion);
        }

        if ($categoria !== '' && $numeroParte !== '') {
            $attempts[] = Material::query()
                ->where('categoria', $categoria)
                ->where('numero_parte', $numeroParte);
        }

        if ($categoria !== '' && $descripcion !== '') {
            $attempts[] = Material::query()
                ->where('categoria', $categoria)
                ->where('descripcion', $descripcion);
        }

        if ($numeroParte !== '' && $descripcion !== '') {
            $attempts[] = Material::query()
                ->where('numero_parte', $numeroParte)
                ->where('descripcion', $descripcion);
        }

        foreach ($attempts as $query) {
            $matches = $query->limit(2)->get();

            if ($matches->count() === 1) {
                return ['status' => 'one', 'material' => $matches->first()];
            }

            if ($matches->count() > 1) {
                return ['status' => 'ambiguous'];
            }
        }

        return ['status' => 'none'];
    }

    /**
     * @return array<string, mixed>
     */
    private function entrySummary(array $entry): array
    {
        return [
            'categoria' => $entry['categoria'] ?? null,
            'numero_parte' => $entry['numero_parte'] ?? null,
            'descripcion' => $entry['descripcion'] ?? null,
            'excel_row' => $entry['excel_row'] ?? null,
            'fotografia' => $entry['fotografia'] ?? null,
        ];
    }
}
