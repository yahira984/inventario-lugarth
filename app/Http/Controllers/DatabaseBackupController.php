<?php

namespace App\Http\Controllers;

use App\Support\AuditLogger;
use App\Support\DatabaseBackupManager;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Process\Exceptions\ProcessTimedOutException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class DatabaseBackupController extends Controller
{
    public function index(Request $request, DatabaseBackupManager $backups): View
    {
        $this->ensureAdmin($request);
        $backups->purgeStaleChunkUploads();

        return view('admin.backups.index', [
            'backups' => $backups->backups(),
            'capabilities' => $backups->capabilities(),
            'maximumUploadMegabytes' => (int) floor(
                (int) config('backup.maximum_upload_kilobytes', 204800) / 1024,
            ),
        ]);
    }

    public function uploadRestoreChunk(
        Request $request,
        DatabaseBackupManager $backups,
    ): JsonResponse {
        $this->ensureAdmin($request);

        $maximumBytes = (int) config('backup.maximum_upload_kilobytes', 204800) * 1024;
        $validated = $request->validate([
            'backup_chunk' => ['required', 'file', 'max:5120'],
            'upload_id' => ['required', 'string', 'regex:/^[a-f0-9]{32}$/'],
            'chunk_index' => ['required', 'integer', 'min:0', 'max:99'],
            'total_chunks' => ['required', 'integer', 'min:1', 'max:100'],
            'backup_name' => ['required', 'string', 'max:255', 'regex:/\.(sql|txt)$/i'],
            'total_size' => ['required', 'integer', 'min:1', "max:{$maximumBytes}"],
        ], [
            'backup_chunk.uploaded' => 'No se pudo recibir una parte del respaldo.',
            'backup_chunk.max' => 'Una parte del respaldo superó el límite permitido.',
            'upload_id.regex' => 'El identificador de la carga no es válido.',
            'backup_name.regex' => 'Selecciona un archivo .sql o .txt.',
            'total_size.max' => 'El respaldo supera el tamaño máximo permitido.',
        ]);

        if ((int) $validated['chunk_index'] >= (int) $validated['total_chunks']) {
            throw ValidationException::withMessages([
                'chunk_index' => 'El número de bloque recibido no es válido.',
            ]);
        }

        try {
            $result = $backups->storeUploadChunk(
                $validated['backup_chunk'],
                (int) $request->user()->id,
                $validated['upload_id'],
                (int) $validated['chunk_index'],
                (int) $validated['total_chunks'],
                $validated['backup_name'],
                (int) $validated['total_size'],
            );

            return response()->json($result, 201);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => $this->friendlyError(
                    $exception,
                    'No se pudo recibir el respaldo.',
                ),
            ], 422);
        }
    }

    public function store(
        Request $request,
        DatabaseBackupManager $backups,
    ): JsonResponse|RedirectResponse {
        $this->ensureAdmin($request);

        try {
            $result = $this->withOperationLock(
                fn (): array => $backups->create(),
            );

            AuditLogger::registrar(
                'Base de datos',
                'Respaldo',
                'Generó una copia completa de la base de datos.',
                [
                    'archivo' => $result['name'],
                    'tamano_bytes' => $result['size'],
                    'metodo' => $result['method'],
                ],
                $request,
            );

            $message = "Respaldo creado correctamente ({$result['size_label']}).";

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'backup' => $result,
                    'download_url' => route('admin.backups.download', $result['name']),
                ], 201);
            }

            return back()->with('success', $message);
        } catch (Throwable $exception) {
            report($exception);

            return $this->operationError(
                $request,
                $this->friendlyError($exception, 'No se pudo crear el respaldo.'),
            );
        }
    }

    public function download(
        Request $request,
        string $backup,
        DatabaseBackupManager $backups,
    ): BinaryFileResponse {
        $this->ensureAdmin($request);

        $path = $backups->backupPath($backup);
        abort_unless($path, 404, 'El respaldo solicitado ya no existe.');

        return response()->download($path, $backup, [
            'Content-Type' => 'application/sql',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function restore(
        Request $request,
        DatabaseBackupManager $backups,
    ): JsonResponse|RedirectResponse {
        $this->ensureAdmin($request);
        $userId = (int) $request->user()->id;

        $maximumKilobytes = (int) config('backup.maximum_upload_kilobytes', 204800);
        $validated = $request->validate([
            'backup_sql' => ['nullable', 'file', "max:{$maximumKilobytes}", 'required_without:upload_token'],
            'upload_token' => ['nullable', 'string', 'regex:/^[a-f0-9]{32}$/', 'required_without:backup_sql'],
            'backup_name' => ['nullable', 'string', 'max:255', 'regex:/\.(sql|txt)$/i', 'required_with:upload_token'],
            'confirmation' => ['required', 'in:RESTAURAR'],
        ], [
            'backup_sql.required_without' => 'Selecciona un archivo .sql para restaurar.',
            'backup_sql.uploaded' => 'El archivo no pudo cargarse. Revisa su tamaño y vuelve a intentarlo.',
            'backup_sql.max' => 'El respaldo supera el tamaño máximo permitido.',
            'upload_token.required_without' => 'No se recibió el respaldo que deseas restaurar.',
            'upload_token.regex' => 'La carga temporal no es válida.',
            'backup_name.required_with' => 'No se recibió el nombre del respaldo.',
            'backup_name.regex' => 'Selecciona un archivo .sql o .txt.',
            'confirmation.required' => 'Escribe RESTAURAR para confirmar la operación.',
            'confirmation.in' => 'La confirmación debe decir exactamente RESTAURAR.',
        ]);

        $uploadedFile = $validated['backup_sql'] ?? null;
        $uploadToken = $validated['upload_token'] ?? null;
        $originalName = $uploadedFile?->getClientOriginalName()
            ?? (string) ($validated['backup_name'] ?? '');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (! in_array($extension, ['sql', 'txt'], true)) {
            throw ValidationException::withMessages([
                'backup_sql' => 'Selecciona un archivo con extensión .sql o .txt.',
            ]);
        }

        $disk = Storage::disk('local');
        $temporaryPath = null;

        try {
            if ($uploadToken) {
                $temporaryPath = $backups->assembleChunkedUpload(
                    $userId,
                    $uploadToken,
                    $originalName,
                );
            } else {
                $temporaryPath = $backups->temporaryRestorePath($extension);
                $stored = $disk->putFileAs(
                    dirname($temporaryPath),
                    $uploadedFile,
                    basename($temporaryPath),
                );

                if (! $stored) {
                    throw new RuntimeException(
                        'No se pudo preparar el archivo para restaurarlo.',
                    );
                }
            }

            $absolutePath = $disk->path($temporaryPath);
            $backups->assertValidSql($absolutePath);

            $operation = $this->withOperationLock(function () use ($backups, $absolutePath): array {
                return $this->whileInMaintenance(function () use ($backups, $absolutePath): array {
                    $safetyBackup = $backups->create('antes_de_restaurar');

                    try {
                        $restore = $backups->restore($absolutePath);
                    } catch (Throwable $restoreException) {
                        $safetyPath = $backups->backupPath($safetyBackup['name']);

                        if (! $safetyPath) {
                            throw new RuntimeException(
                                'La restauración falló y no se encontró la copia de seguridad automática. '
                                .'No realices más cambios y conserva el archivo original. Motivo: '
                                .Str::limit($restoreException->getMessage(), 500),
                                previous: $restoreException,
                            );
                        }

                        try {
                            $backups->restore($safetyPath);
                        } catch (Throwable $rollbackException) {
                            report($rollbackException);

                            throw new RuntimeException(
                                'La restauración falló y tampoco fue posible recuperar automáticamente '
                                .'la base anterior. Conserva el respaldo '
                                .$safetyBackup['name'].' y revisa el servicio de MySQL. Motivo original: '
                                .Str::limit($restoreException->getMessage(), 400),
                                previous: $restoreException,
                            );
                        }

                        throw new RuntimeException(
                            'El archivo no pudo aplicarse, pero la base anterior quedó recuperada '
                            .'correctamente. Motivo: '
                            .Str::limit($restoreException->getMessage(), 500),
                            previous: $restoreException,
                        );
                    }

                    return compact('safetyBackup', 'restore');
                });
            });

            try {
                AuditLogger::registrar(
                    'Base de datos',
                    'Restauración',
                    'Restauró la base de datos desde un archivo SQL y creó un respaldo de seguridad previo.',
                    [
                        'archivo_origen' => $originalName,
                        'respaldo_seguridad' => $operation['safetyBackup']['name'],
                        'metodo' => $operation['restore']['method'],
                        'version_origen' => $operation['restore']['source_version'],
                        'ajustes_compatibilidad' => $operation['restore']['compatibility_fixes'],
                    ],
                    $request,
                );
            } catch (Throwable $auditException) {
                // Al reemplazar toda la base, el usuario o la tabla de auditoría pueden cambiar.
                report($auditException);
            }

            $adjustmentCount = array_sum($operation['restore']['compatibility_fixes']);
            $compatibilityMessage = $adjustmentCount > 0
                ? " Se aplicaron {$adjustmentCount} ajustes de compatibilidad entre equipos."
                : '';
            $message = 'Base de datos restaurada correctamente. '
                ."Se guardó {$operation['safetyBackup']['name']} antes de reemplazarla."
                .$compatibilityMessage;

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'safety_backup' => $operation['safetyBackup'],
                    'redirect_url' => route('admin.backups.index'),
                ]);
            }

            return redirect()
                ->route('admin.backups.index')
                ->with('success', $message);
        } catch (Throwable $exception) {
            report($exception);

            return $this->operationError(
                $request,
                $this->friendlyError($exception, 'No se pudo restaurar la base de datos.'),
            );
        } finally {
            if ($temporaryPath) {
                $disk->delete($temporaryPath);
            }

            if ($uploadToken) {
                $backups->discardChunkedUpload(
                    $userId,
                    $uploadToken,
                );
            }
        }
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);
    }

    private function withOperationLock(callable $operation): mixed
    {
        try {
            return Cache::store('file')
                ->lock('database-backup-operation', 1200)
                ->block(1, $operation);
        } catch (LockTimeoutException) {
            throw new RuntimeException(
                'Ya hay un respaldo o una restauración en proceso. Espera a que termine.',
            );
        }
    }

    private function whileInMaintenance(callable $operation): mixed
    {
        if (app()->environment('testing')) {
            return $operation();
        }

        $wasAlreadyDown = app()->isDownForMaintenance();

        if (! $wasAlreadyDown) {
            Artisan::call('down', [
                '--retry' => 15,
                '--refresh' => 15,
            ]);
        }

        try {
            return $operation();
        } finally {
            if (! $wasAlreadyDown) {
                Artisan::call('up');
            }
        }
    }

    private function operationError(
        Request $request,
        string $message,
        int $status = 422,
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->withInput()->with('error', $message);
    }

    private function friendlyError(Throwable $exception, string $fallback): string
    {
        if ($exception instanceof ProcessTimedOutException) {
            return 'MySQL tardó más de lo permitido. La base anterior se recuperó automáticamente. '
                .'Cierra programas pesados y vuelve a intentarlo.';
        }

        if ($exception instanceof QueryException) {
            $message = strtolower($exception->getMessage());

            if (str_contains($message, '[2002]') || str_contains($message, 'connection refused')) {
                return 'No se pudo conectar con MySQL. Verifica que el servicio esté encendido '
                    .'y que DB_HOST y DB_PORT sean correctos en el archivo .env.';
            }

            if (str_contains($message, '[1045]') || str_contains($message, 'access denied')) {
                return 'MySQL rechazó el usuario o la contraseña configurados en el archivo .env.';
            }

            if (str_contains($message, '[1049]') || str_contains($message, 'unknown database')) {
                return 'La base configurada en DB_DATABASE no existe en esta computadora.';
            }

            if (str_contains($message, 'unknown collation')) {
                return 'El respaldo usa una configuración de texto que esta versión de MySQL no reconoce.';
            }
        }

        if ($exception instanceof RuntimeException
            || $exception instanceof ValidationException) {
            return $exception->getMessage();
        }

        return $fallback.' Revisa el registro de errores para conocer el detalle.';
    }
}
