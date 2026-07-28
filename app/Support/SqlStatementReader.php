<?php

namespace App\Support;

use Generator;
use RuntimeException;

class SqlStatementReader
{
    /**
     * @return Generator<int, string>
     */
    public function statements(string $path): Generator
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('No se pudo abrir el archivo SQL.');
        }

        $buffer = '';
        $quote = null;
        $escaped = false;
        $lineComment = false;
        $blockComment = false;
        $statementNumber = 0;
        $maximumBytes = (int) config('backup.maximum_statement_bytes', 64 * 1024 * 1024);

        try {
            while (! feof($handle)) {
                $chunk = fread($handle, 65536);

                if ($chunk === false) {
                    throw new RuntimeException('No se pudo leer completamente el archivo SQL.');
                }

                $length = strlen($chunk);

                for ($index = 0; $index < $length; $index++) {
                    $character = $chunk[$index];
                    $next = $index + 1 < $length ? $chunk[$index + 1] : null;
                    $buffer .= $character;

                    if (strlen($buffer) > $maximumBytes) {
                        throw new RuntimeException(
                            'El archivo contiene una instrucción SQL demasiado grande para el modo compatible.',
                        );
                    }

                    if ($lineComment) {
                        if ($character === "\n") {
                            $lineComment = false;
                        }

                        continue;
                    }

                    if ($blockComment) {
                        if ($character === '*' && $next === '/') {
                            $buffer .= '/';
                            $index++;
                            $blockComment = false;
                        }

                        continue;
                    }

                    if ($quote !== null) {
                        if ($escaped) {
                            $escaped = false;

                            continue;
                        }

                        if ($character === '\\') {
                            $escaped = true;

                            continue;
                        }

                        if ($character === $quote) {
                            if ($next === $quote) {
                                $buffer .= $next;
                                $index++;
                            } else {
                                $quote = null;
                            }
                        }

                        continue;
                    }

                    if ($character === '-' && $next === '-') {
                        $buffer .= '-';
                        $index++;
                        $lineComment = true;

                        continue;
                    }

                    if ($character === '#') {
                        $lineComment = true;

                        continue;
                    }

                    if ($character === '/' && $next === '*') {
                        $buffer .= '*';
                        $index++;
                        $blockComment = true;

                        continue;
                    }

                    if (in_array($character, ["'", '"', '`'], true)) {
                        $quote = $character;

                        continue;
                    }

                    if ($character !== ';') {
                        continue;
                    }

                    $statement = $this->normalize($buffer);
                    $buffer = '';

                    if ($statement === '') {
                        continue;
                    }

                    $statementNumber++;
                    yield $statementNumber => $statement;
                }
            }

            $statement = $this->normalize($buffer);

            if ($statement !== '') {
                yield ++$statementNumber => $statement;
            }
        } finally {
            fclose($handle);
        }
    }

    private function normalize(string $statement): string
    {
        return trim(ltrim($statement, "\xEF\xBB\xBF"));
    }
}
