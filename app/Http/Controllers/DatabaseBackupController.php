<?php

namespace App\Http\Controllers;

use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseBackupController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        $backups = collect(Storage::disk('local')->files('backups'))
            ->filter(fn ($file) => str_ends_with($file, '.sql'))
            ->sortDesc()
            ->values();

        return view('admin.backups.index', compact('backups'));
    }

    public function store(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        Storage::disk('local')->makeDirectory('backups');

        $filename = 'backups/respaldo_bd_' . now()->format('Ymd_His') . '.sql';
        $path = Storage::disk('local')->path($filename);
        file_put_contents($path, $this->generarSql());

        AuditLogger::registrar('Base de datos', 'Respaldo', 'Genero una copia completa de la base de datos.', [
            'archivo' => $filename,
        ], $request);

        return response()->download($path);
    }

    public function restore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        $request->validate([
            'backup_sql' => ['required', 'file', 'mimes:sql,txt', 'max:51200'],
        ], [
            'backup_sql.required' => 'Selecciona un archivo .sql para restaurar.',
            'backup_sql.max' => 'El respaldo no debe pesar mas de 50 MB.',
        ]);

        $sql = file_get_contents($request->file('backup_sql')->getRealPath());

        DB::unprepared('SET FOREIGN_KEY_CHECKS=0;');
        DB::unprepared($sql);
        DB::unprepared('SET FOREIGN_KEY_CHECKS=1;');

        AuditLogger::registrar('Base de datos', 'Restauracion', 'Restauro la base de datos desde un archivo SQL.', [
            'archivo' => $request->file('backup_sql')->getClientOriginalName(),
        ], $request);

        return back()->with('success', 'Base de datos restaurada correctamente.');
    }

    private function generarSql(): string
    {
        $database = config('database.connections.mysql.database');
        $tables = collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0]);

        $sql = "-- Respaldo completo de {$database}\n";
        $sql .= '-- Fecha: ' . now()->format('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $create = (array) DB::selectOne("SHOW CREATE TABLE `{$table}`");
            $createSql = $create['Create Table'] ?? array_values($create)[1] ?? '';

            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createSql . ";\n\n";

            DB::table($table)->orderByRaw('1')->chunk(500, function ($rows) use (&$sql, $table) {
                foreach ($rows as $row) {
                    $data = (array) $row;
                    $columns = collect(array_keys($data))->map(fn ($column) => "`{$column}`")->implode(', ');
                    $values = collect(array_values($data))->map(fn ($value) => $this->sqlValue($value))->implode(', ');
                    $sql .= "INSERT INTO `{$table}` ({$columns}) VALUES ({$values});\n";
                }
            });

            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }

    private function sqlValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return DB::getPdo()->quote((string) $value);
    }
}
