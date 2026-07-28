<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class DatabaseBackupManager
{
    /**
     * @var array{dump: string, client: string, label: string}|null|false
     */
    private array|false|null $resolvedClients = false;

    public function __construct(private readonly SqlStatementReader $statementReader) {}

    /**
     * @return Collection<int, array{
     *     path: string,
     *     name: string,
     *     size: int,
     *     size_label: string,
     *     modified_at: int,
     *     modified_label: string
     * }>
     */
    public function backups(): Collection
    {
        $disk = Storage::disk('local');
        $directory = (string) config('backup.directory', 'backups');

        return collect($disk->files($directory))
            ->filter(fn (string $path): bool => str_ends_with(strtolower($path), '.sql'))
            ->map(function (string $path) use ($disk): array {
                $size = (int) $disk->size($path);
                $modifiedAt = (int) $disk->lastModified($path);

                return [
                    'path' => $path,
                    'name' => basename($path),
                    'size' => $size,
                    'size_label' => $this->formatBytes($size),
                    'modified_at' => $modifiedAt,
                    'modified_label' => date('d/m/Y H:i', $modifiedAt),
                ];
            })
            ->sortByDesc('modified_at')
            ->values();
    }

    /**
     * @return array{
     *     native: bool,
     *     engine: string,
     *     database: string,
     *     server_version: string
     * }
     */
    public function capabilities(): array
    {
        $clients = $this->clients();

        return [
            'native' => $clients !== null,
            'engine' => $clients['label'] ?? 'Modo compatible de Laravel',
            'database' => (string) $this->connectionConfig()['database'],
            'server_version' => $this->serverVersion(),
        ];
    }

    /**
     * @return array{
     *     path: string,
     *     name: string,
     *     size: int,
     *     size_label: string,
     *     method: string
     * }
     */
    public function create(string $prefix = 'respaldo_bd'): array
    {
        $this->ensureMysqlConnection();

        $disk = Storage::disk('local');
        $directory = (string) config('backup.directory', 'backups');
        $disk->makeDirectory($directory);

        $safePrefix = Str::slug($prefix, '_') ?: 'respaldo_bd';
        $name = $safePrefix.'_'.now()->format('Ymd_His').'_'.Str::lower(Str::random(4)).'.sql';
        $relativePath = $directory.'/'.$name;
        $absolutePath = $disk->path($relativePath);
        $clients = $this->clients();

        try {
            if ($clients) {
                $this->createWithNativeClient($absolutePath, $clients['dump']);
                $method = 'nativo';
            } else {
                $this->createPortable($absolutePath);
                $method = 'compatible';
            }

            clearstatcache(true, $absolutePath);
            $size = is_file($absolutePath) ? (int) filesize($absolutePath) : 0;

            if ($size < 100) {
                throw new RuntimeException('El respaldo generado está vacío o incompleto.');
            }

            return [
                'path' => $relativePath,
                'name' => $name,
                'size' => $size,
                'size_label' => $this->formatBytes($size),
                'method' => $method,
            ];
        } catch (Throwable $exception) {
            $disk->delete($relativePath);

            throw $exception;
        }
    }

    /**
     * @return array{method: string, statements: ?int, migrations: string}
     */
    public function restore(string $absolutePath): array
    {
        $this->ensureMysqlConnection();
        $this->assertValidSql($absolutePath);
        $clients = $this->clients();

        if ($clients) {
            $this->restoreWithNativeClient($absolutePath, $clients['client']);
            $method = 'nativo';
            $statements = null;
        } else {
            $statements = $this->restorePortable($absolutePath);
            $method = 'compatible';
        }

        $connection = (string) config('database.default');
        DB::purge($connection);

        $exitCode = Artisan::call('migrate', [
            '--force' => true,
        ]);
        $migrationOutput = trim(Artisan::output());

        if ($exitCode !== 0) {
            throw new RuntimeException(
                'La información fue restaurada, pero no se pudieron actualizar las migraciones. '
                .'Ejecuta php artisan migrate --force. '.$migrationOutput,
            );
        }

        DB::purge($connection);

        return [
            'method' => $method,
            'statements' => $statements,
            'migrations' => $migrationOutput,
        ];
    }

    public function assertValidSql(string $path): void
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('No se pudo leer el archivo seleccionado.');
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('No se pudo abrir el archivo seleccionado.');
        }

        try {
            $sample = fread($handle, 131072);
        } finally {
            fclose($handle);
        }

        if ($sample === false || trim($sample) === '') {
            throw new RuntimeException('El archivo SQL está vacío.');
        }

        if (! preg_match(
            '/\b(CREATE\s+TABLE|INSERT\s+INTO|DROP\s+TABLE|SET\s+FOREIGN_KEY_CHECKS|MySQL\s+dump)\b/i',
            $sample,
        )) {
            throw new RuntimeException(
                'El archivo no parece ser un respaldo SQL válido del sistema.',
            );
        }
    }

    public function temporaryRestorePath(string $extension = 'sql'): string
    {
        $disk = Storage::disk('local');
        $directory = (string) config('backup.temporary_directory', 'backups/tmp');
        $disk->makeDirectory($directory);

        return $directory.'/restaurar-'.Str::lower(Str::random(24)).'.'.$extension;
    }

    /**
     * @return array{uploaded_chunks: int, total_chunks: int}
     */
    public function storeUploadChunk(
        UploadedFile $chunk,
        int $userId,
        string $uploadId,
        int $chunkIndex,
        int $totalChunks,
        string $originalName,
        int $totalSize,
    ): array {
        $disk = Storage::disk('local');
        $directory = $this->chunkDirectory($userId, $uploadId);
        $metadataPath = $directory.'/metadata.json';
        $metadata = [
            'upload_id' => $uploadId,
            'user_id' => $userId,
            'original_name' => basename($originalName),
            'total_chunks' => $totalChunks,
            'total_size' => $totalSize,
            'updated_at' => now()->timestamp,
        ];

        if ($disk->exists($metadataPath)) {
            $existing = json_decode((string) $disk->get($metadataPath), true);

            if (! is_array($existing)
                || (int) ($existing['user_id'] ?? 0) !== $userId
                || (int) ($existing['total_chunks'] ?? 0) !== $totalChunks
                || (int) ($existing['total_size'] ?? 0) !== $totalSize
                || (string) ($existing['original_name'] ?? '') !== basename($originalName)) {
                throw new RuntimeException(
                    'Los bloques recibidos no pertenecen al mismo archivo. Inicia nuevamente la carga.',
                );
            }
        }

        $disk->makeDirectory($directory);
        $stored = $disk->putFileAs(
            $directory,
            $chunk,
            sprintf('chunk-%06d.part', $chunkIndex),
        );

        if (! $stored || ! $disk->put($metadataPath, json_encode($metadata, JSON_THROW_ON_ERROR))) {
            throw new RuntimeException('No se pudo guardar una parte del respaldo.');
        }

        $uploadedChunks = collect($disk->files($directory))
            ->filter(fn (string $path): bool => str_ends_with($path, '.part'))
            ->count();

        return [
            'uploaded_chunks' => $uploadedChunks,
            'total_chunks' => $totalChunks,
        ];
    }

    public function assembleChunkedUpload(
        int $userId,
        string $uploadId,
        string $originalName,
    ): string {
        $disk = Storage::disk('local');
        $directory = $this->chunkDirectory($userId, $uploadId);
        $metadataPath = $directory.'/metadata.json';

        if (! $disk->exists($metadataPath)) {
            throw new RuntimeException('La carga temporal ya no existe. Selecciona nuevamente el respaldo.');
        }

        $metadata = json_decode((string) $disk->get($metadataPath), true);

        if (! is_array($metadata)
            || (int) ($metadata['user_id'] ?? 0) !== $userId
            || (string) ($metadata['original_name'] ?? '') !== basename($originalName)) {
            throw new RuntimeException('La carga temporal no coincide con el archivo seleccionado.');
        }

        $totalChunks = (int) ($metadata['total_chunks'] ?? 0);
        $expectedSize = (int) ($metadata['total_size'] ?? 0);
        $maximumBytes = (int) config('backup.maximum_upload_kilobytes', 204800) * 1024;

        if ($totalChunks < 1 || $expectedSize < 1 || $expectedSize > $maximumBytes) {
            throw new RuntimeException('Los datos de la carga temporal no son válidos.');
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $relativePath = $this->temporaryRestorePath($extension);
        $output = fopen($disk->path($relativePath), 'wb');

        if ($output === false) {
            throw new RuntimeException('No se pudo preparar el respaldo completo.');
        }

        $writtenBytes = 0;

        try {
            for ($index = 0; $index < $totalChunks; $index++) {
                $chunkPath = $directory.'/'.sprintf('chunk-%06d.part', $index);

                if (! $disk->exists($chunkPath)) {
                    throw new RuntimeException(
                        'Falta una parte del respaldo. Vuelve a seleccionar el archivo.',
                    );
                }

                $input = fopen($disk->path($chunkPath), 'rb');

                if ($input === false) {
                    throw new RuntimeException('No se pudo leer una parte del respaldo.');
                }

                try {
                    $copied = stream_copy_to_stream($input, $output);
                } finally {
                    fclose($input);
                }

                if ($copied === false) {
                    throw new RuntimeException('No se pudo unir completamente el respaldo.');
                }

                $writtenBytes += $copied;
            }
        } catch (Throwable $exception) {
            fclose($output);
            $disk->delete($relativePath);

            throw $exception;
        }

        fclose($output);

        if ($writtenBytes !== $expectedSize) {
            $disk->delete($relativePath);

            throw new RuntimeException(
                'El respaldo recibido está incompleto. Selecciónalo nuevamente.',
            );
        }

        return $relativePath;
    }

    public function discardChunkedUpload(int $userId, string $uploadId): void
    {
        Storage::disk('local')->deleteDirectory(
            $this->chunkDirectory($userId, $uploadId),
        );
    }

    public function purgeStaleChunkUploads(): void
    {
        $disk = Storage::disk('local');
        $root = (string) config('backup.temporary_directory', 'backups/tmp').'/chunks';
        $cutoff = now()->subHours(2)->timestamp;

        foreach ($disk->allFiles($root) as $path) {
            if (! str_ends_with($path, '/metadata.json')
                || $disk->lastModified($path) >= $cutoff) {
                continue;
            }

            $disk->deleteDirectory(dirname($path));
        }
    }

    public function backupPath(string $name): ?string
    {
        if ($name !== basename($name) || ! preg_match('/^[A-Za-z0-9_.-]+\.sql$/', $name)) {
            return null;
        }

        $disk = Storage::disk('local');
        $relativePath = (string) config('backup.directory', 'backups').'/'.$name;

        return $disk->exists($relativePath) ? $disk->path($relativePath) : null;
    }

    private function createWithNativeClient(string $path, string $binary): void
    {
        $arguments = [
            $binary,
            ...$this->connectionArguments(),
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--routines',
            '--events',
            '--triggers',
            '--hex-blob',
        ];

        if (! str_contains(strtolower(basename($binary)), 'mariadb')) {
            $arguments[] = '--no-tablespaces';
            $arguments[] = '--set-gtid-purged=OFF';
        }

        $arguments[] = '--result-file='.$this->mysqlPath($path);
        $arguments[] = (string) $this->connectionConfig()['database'];

        $result = Process::path(base_path())
            ->env($this->processEnvironment())
            ->timeout((int) config('backup.process_timeout_seconds', 900))
            ->run($arguments);

        if ($result->failed()) {
            throw new RuntimeException(
                'MySQL no pudo crear el respaldo. '.$this->processDetail($result->errorOutput()),
            );
        }
    }

    private function restoreWithNativeClient(string $path, string $binary): void
    {
        $stream = fopen($path, 'rb');

        if ($stream === false) {
            throw new RuntimeException('No se pudo abrir el respaldo para restaurarlo.');
        }

        try {
            $result = Process::path(base_path())
                ->env($this->processEnvironment())
                ->input($stream)
                ->timeout((int) config('backup.process_timeout_seconds', 900))
                ->run([
                    $binary,
                    ...$this->connectionArguments(),
                    '--binary-mode',
                    '--max_allowed_packet=512M',
                    (string) $this->connectionConfig()['database'],
                ]);
        } finally {
            fclose($stream);
        }

        if ($result->failed()) {
            throw new RuntimeException(
                'MySQL no pudo restaurar el respaldo. '.$this->processDetail($result->errorOutput()),
            );
        }
    }

    private function createPortable(string $path): void
    {
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new RuntimeException('No se pudo crear el archivo de respaldo.');
        }

        $database = (string) $this->connectionConfig()['database'];

        try {
            $this->write($handle, "-- Respaldo completo de {$database}\n");
            $this->write($handle, '-- Fecha: '.now()->format('Y-m-d H:i:s')."\n");
            $this->write($handle, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

            $tables = collect(DB::select('SHOW TABLES'))
                ->map(fn (object $row): string => (string) array_values((array) $row)[0]);

            foreach ($tables as $table) {
                $safeTable = str_replace('`', '``', $table);
                $create = (array) DB::selectOne("SHOW CREATE TABLE `{$safeTable}`");
                $createSql = $create['Create Table'] ?? array_values($create)[1] ?? '';

                $this->write($handle, "DROP TABLE IF EXISTS `{$safeTable}`;\n");
                $this->write($handle, $createSql.";\n\n");

                foreach (DB::table($table)->orderByRaw('1')->cursor() as $row) {
                    $data = (array) $row;
                    $columns = collect(array_keys($data))
                        ->map(fn (string $column): string => '`'.str_replace('`', '``', $column).'`')
                        ->implode(', ');
                    $values = collect(array_values($data))
                        ->map(fn (mixed $value): string => $this->sqlValue($value))
                        ->implode(', ');

                    $this->write(
                        $handle,
                        "INSERT INTO `{$safeTable}` ({$columns}) VALUES ({$values});\n",
                    );
                }

                $this->write($handle, "\n");
            }

            $this->write($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        } finally {
            fclose($handle);
        }
    }

    private function restorePortable(string $path): int
    {
        $executed = 0;
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($this->statementReader->statements($path) as $number => $statement) {
                if (preg_match('/^\s*DELIMITER\b/im', $statement)) {
                    throw new RuntimeException(
                        'Este respaldo usa rutinas avanzadas y requiere el cliente nativo de MySQL.',
                    );
                }

                try {
                    DB::unprepared($statement);
                    $executed++;
                } catch (Throwable $exception) {
                    throw new RuntimeException(
                        "La restauración se detuvo en la instrucción SQL {$number}: "
                        .Str::limit(preg_replace('/\s+/', ' ', $exception->getMessage()), 500),
                        previous: $exception,
                    );
                }
            }
        } finally {
            try {
                DB::unprepared('SET FOREIGN_KEY_CHECKS=1');
            } catch (Throwable) {
                // The restored connection may have been replaced; it is purged afterwards.
            }
        }

        return $executed;
    }

    /**
     * @return array<int, string>
     */
    private function connectionArguments(): array
    {
        $config = $this->connectionConfig();
        $arguments = [
            '--protocol=TCP',
            '--host='.(string) ($config['host'] ?? '127.0.0.1'),
            '--port='.(string) ($config['port'] ?? 3306),
            '--user='.(string) ($config['username'] ?? ''),
            '--default-character-set=utf8mb4',
        ];

        if (! empty($config['unix_socket'])) {
            $arguments[] = '--socket='.(string) $config['unix_socket'];
        }

        return $arguments;
    }

    /**
     * @return array<string, string>
     */
    private function processEnvironment(): array
    {
        $temporaryDirectory = Storage::disk('local')->path(
            (string) config('backup.temporary_directory', 'backups/tmp'),
        );

        if (! is_dir($temporaryDirectory)) {
            mkdir($temporaryDirectory, 0775, true);
        }

        return [
            'MYSQL_PWD' => (string) ($this->connectionConfig()['password'] ?? ''),
            'TMP' => $temporaryDirectory,
            'TEMP' => $temporaryDirectory,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function connectionConfig(): array
    {
        $connection = (string) config('database.default');

        return (array) config("database.connections.{$connection}", []);
    }

    private function ensureMysqlConnection(): void
    {
        $driver = (string) ($this->connectionConfig()['driver'] ?? '');

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException(
                'El módulo de respaldos está configurado para MySQL o MariaDB.',
            );
        }
    }

    /**
     * @return array{dump: string, client: string, label: string}|null
     */
    private function clients(): ?array
    {
        if ($this->resolvedClients !== false) {
            return $this->resolvedClients;
        }

        $explicitDump = config('backup.dump_binary');
        $explicitClient = config('backup.client_binary');

        if (is_string($explicitDump) && is_file($explicitDump)
            && is_string($explicitClient) && is_file($explicitClient)) {
            return $this->resolvedClients = [
                'dump' => $explicitDump,
                'client' => $explicitClient,
                'label' => 'Cliente MySQL configurado',
            ];
        }

        foreach ($this->binaryDirectories() as $directory) {
            foreach ($this->binaryNamePairs() as [$dumpName, $clientName, $label]) {
                $dump = $directory.DIRECTORY_SEPARATOR.$dumpName;
                $client = $directory.DIRECTORY_SEPARATOR.$clientName;

                if (is_file($dump) && is_file($client)) {
                    return $this->resolvedClients = [
                        'dump' => $dump,
                        'client' => $client,
                        'label' => $label.' ('.basename(dirname($directory)).')',
                    ];
                }
            }
        }

        foreach ($this->binaryNamePairs() as [$dumpName, $clientName, $label]) {
            if ($this->binaryResponds($dumpName) && $this->binaryResponds($clientName)) {
                return $this->resolvedClients = [
                    'dump' => $dumpName,
                    'client' => $clientName,
                    'label' => $label.' del sistema',
                ];
            }
        }

        $this->resolvedClients = null;

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function binaryDirectories(): array
    {
        $configured = config('backup.binary_directory');
        $localAppData = getenv('LOCALAPPDATA') ?: null;
        $programFiles = getenv('ProgramFiles') ?: 'C:\\Program Files';
        $patterns = array_filter([
            is_string($configured) ? $configured : null,
            $localAppData
                ? $localAppData.'/com.tinyapp.DBngin/Binaries/mysql/*/bin'
                : null,
            $localAppData
                ? $localAppData.'/com.tinyapp.DBngin/Binaries/mariadb/*/bin'
                : null,
            $programFiles.'/MySQL/MySQL Server */bin',
            $programFiles.'/MariaDB */bin',
            '/usr/local/mysql/bin',
            '/usr/local/bin',
            '/usr/bin',
            '/opt/homebrew/bin',
        ]);
        $directories = [];

        foreach ($patterns as $pattern) {
            if (strpbrk($pattern, '*?[') !== false) {
                $matches = glob($pattern, GLOB_ONLYDIR) ?: [];
                natsort($matches);
                $matches = array_reverse($matches);
                $directories = [...$directories, ...$matches];
            } elseif (is_dir($pattern)) {
                $directories[] = $pattern;
            }
        }

        $serverIsMariaDb = str_contains(strtolower($this->serverVersion()), 'mariadb');

        usort($directories, function (string $first, string $second) use ($serverIsMariaDb): int {
            $needle = $serverIsMariaDb ? 'mariadb' : 'mysql';

            return (int) ! str_contains(strtolower($first), $needle)
                <=> (int) ! str_contains(strtolower($second), $needle);
        });

        return array_values(array_unique($directories));
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: string}>
     */
    private function binaryNamePairs(): array
    {
        $suffix = PHP_OS_FAMILY === 'Windows' ? '.exe' : '';
        $mariaDbFirst = str_contains(strtolower($this->serverVersion()), 'mariadb');
        $mysql = ['mysqldump'.$suffix, 'mysql'.$suffix, 'MySQL nativo'];
        $mariaDb = ['mariadb-dump'.$suffix, 'mariadb'.$suffix, 'MariaDB nativo'];

        return $mariaDbFirst ? [$mariaDb, $mysql] : [$mysql, $mariaDb];
    }

    private function binaryResponds(string $binary): bool
    {
        try {
            return Process::env($this->processEnvironment())
                ->timeout(5)
                ->run([$binary, '--version'])
                ->successful();
        } catch (Throwable) {
            return false;
        }
    }

    private function serverVersion(): string
    {
        try {
            $result = (array) DB::selectOne('SELECT VERSION() AS version');

            return (string) ($result['version'] ?? 'MySQL');
        } catch (Throwable) {
            return 'MySQL';
        }
    }

    private function processDetail(string $error): string
    {
        $detail = trim(preg_replace('/\s+/', ' ', $error));

        return $detail === ''
            ? 'El proceso terminó sin entregar un detalle.'
            : 'Detalle: '.Str::limit($detail, 800);
    }

    private function write($handle, string $contents): void
    {
        $remaining = $contents;

        while ($remaining !== '') {
            $written = fwrite($handle, $remaining);

            if ($written === false || $written === 0) {
                throw new RuntimeException('No se pudo escribir completamente el respaldo.');
            }

            $remaining = substr($remaining, $written);
        }
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

    private function mysqlPath(string $path): string
    {
        return PHP_OS_FAMILY === 'Windows' ? str_replace('\\', '/', $path) : $path;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return "{$bytes} B";
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / (1024 * 1024), 1).' MB';
    }

    private function chunkDirectory(int $userId, string $uploadId): string
    {
        return (string) config('backup.temporary_directory', 'backups/tmp')
            ."/chunks/{$userId}/{$uploadId}";
    }
}
