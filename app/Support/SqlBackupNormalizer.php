<?php

namespace App\Support;

use RuntimeException;

class SqlBackupNormalizer
{
    /**
     * @return array{source_version: ?string, changes: array<string, int>}
     */
    public function normalize(string $sourcePath, string $destinationPath): array
    {
        $source = fopen($sourcePath, 'rb');
        $destination = fopen($destinationPath, 'wb');

        if ($source === false || $destination === false) {
            if (is_resource($source)) {
                fclose($source);
            }

            if (is_resource($destination)) {
                fclose($destination);
            }

            throw new RuntimeException('No se pudo preparar una copia compatible del respaldo.');
        }

        $changes = [];
        $sourceVersion = null;
        $firstLine = true;
        $skipUntilSemicolon = false;
        $insideDefinition = false;

        try {
            while (($line = fgets($source)) !== false) {
                if ($firstLine) {
                    $line = ltrim($line, "\xEF\xBB\xBF");
                    $firstLine = false;
                }

                if ($sourceVersion === null
                    && preg_match('/^--\s*Server version\s+(.+)$/i', trim($line), $match)) {
                    $sourceVersion = trim($match[1]);
                }

                if ($skipUntilSemicolon) {
                    if (str_contains($line, ';')) {
                        $skipUntilSemicolon = false;
                    }

                    continue;
                }

                $trimmed = ltrim($line);
                $lower = strtolower($trimmed);

                if (str_contains($lower, 'enable the sandbox mode')) {
                    $this->increment($changes, 'sandbox');

                    continue;
                }

                if (preg_match('/^(?:create\s+database|use\s+`?)/i', $trimmed)) {
                    $this->increment($changes, 'database_selection');

                    if (! str_contains($line, ';')) {
                        $skipUntilSemicolon = true;
                    }

                    continue;
                }

                if (preg_match('/^set\s+@@global\.gtid_purged/i', $trimmed)) {
                    $this->increment($changes, 'gtid');

                    if (! str_contains($line, ';')) {
                        $skipUntilSemicolon = true;
                    }

                    continue;
                }

                if (preg_match('/^(?:lock\s+tables|unlock\s+tables)\b/i', $trimmed)) {
                    $this->increment($changes, 'table_locks');

                    continue;
                }

                $isDataLine = (bool) preg_match('/^(?:insert|replace)\b/i', $trimmed);

                if (! $isDataLine
                    && preg_match('/(?:^|\/\*!\d+\s+)(?:create|alter)\b/i', $trimmed)) {
                    $insideDefinition = true;
                }

                if ($insideDefinition
                    || preg_match('/\bSET\b.*\bCOLLATION/i', $line)) {
                    $line = preg_replace_callback(
                        '/utf8mb4_(?:0900|uca1400)[a-z0-9_]*/i',
                        function (array $match) use (&$changes): string {
                            $this->increment($changes, 'collations');
                            $value = strtolower($match[0]);

                            return str_ends_with($value, '_bin') || str_ends_with($value, '_cs')
                                ? 'utf8mb4_bin'
                                : 'utf8mb4_unicode_ci';
                        },
                        $line,
                    ) ?? $line;

                    $line = preg_replace_callback(
                        '/\bdefiner\s*=\s*(?:`[^`]*`|[^@\s]+)@(?:`[^`]*`|[^\s*]+)/i',
                        function () use (&$changes): string {
                            $this->increment($changes, 'definers');

                            return '';
                        },
                        $line,
                    ) ?? $line;
                }

                if (stripos($line, 'NO_AUTO_CREATE_USER') !== false
                    && preg_match('/\bSET\s+(?:@@(?:SESSION\.)?)?SQL_MODE\s*=/i', $line)) {
                    $this->increment($changes, 'sql_modes');
                    $line = str_ireplace(
                        [',NO_AUTO_CREATE_USER', 'NO_AUTO_CREATE_USER,', 'NO_AUTO_CREATE_USER'],
                        '',
                        $line,
                    );
                }

                $this->write($destination, $line);

                if ($insideDefinition && preg_match('/;\s*(?:--.*)?$/', trim($line))) {
                    $insideDefinition = false;
                }
            }

            if (! feof($source)) {
                throw new RuntimeException('No se pudo leer completamente el respaldo.');
            }
        } finally {
            fclose($source);
            fclose($destination);
        }

        clearstatcache(true, $destinationPath);

        if (! is_file($destinationPath) || (int) filesize($destinationPath) < 20) {
            throw new RuntimeException('La copia compatible del respaldo quedó vacía.');
        }

        return [
            'source_version' => $sourceVersion,
            'changes' => $changes,
        ];
    }

    /**
     * @param  array<string, int>  $changes
     */
    private function increment(array &$changes, string $key): void
    {
        $changes[$key] = ($changes[$key] ?? 0) + 1;
    }

    /**
     * @param  resource  $handle
     */
    private function write($handle, string $contents): void
    {
        $remaining = $contents;

        while ($remaining !== '') {
            $written = fwrite($handle, $remaining);

            if ($written === false || $written === 0) {
                throw new RuntimeException('No se pudo escribir la copia compatible del respaldo.');
            }

            $remaining = substr($remaining, $written);
        }
    }
}
