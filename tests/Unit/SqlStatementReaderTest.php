<?php

namespace Tests\Unit;

use App\Support\SqlStatementReader;
use RuntimeException;
use Tests\TestCase;

class SqlStatementReaderTest extends TestCase
{
    public function test_it_streams_statements_without_splitting_semicolons_inside_text(): void
    {
        $path = storage_path('framework/testing-sql-reader.sql');
        file_put_contents($path, <<<'SQL'
-- Comentario con punto y coma;
CREATE TABLE `prueba` (
    `id` bigint,
    `texto` text
);
INSERT INTO `prueba` VALUES (1, 'Texto; completo');
INSERT INTO `prueba` VALUES (2, 'Comilla ''doble'' y ; final');
# Otro comentario;
SET FOREIGN_KEY_CHECKS=1;
SQL);

        try {
            $statements = iterator_to_array(
                app(SqlStatementReader::class)->statements($path),
            );
        } finally {
            @unlink($path);
        }

        $this->assertCount(4, $statements);
        $this->assertStringContainsString('CREATE TABLE', $statements[1]);
        $this->assertStringContainsString("'Texto; completo'", $statements[2]);
        $this->assertStringContainsString("'doble'' y ; final'", $statements[3]);
        $this->assertStringContainsString('SET FOREIGN_KEY_CHECKS=1', $statements[4]);
    }

    public function test_it_rejects_an_abnormally_large_single_statement(): void
    {
        config(['backup.maximum_statement_bytes' => 32]);
        $path = storage_path('framework/testing-sql-reader-large.sql');
        file_put_contents($path, 'INSERT INTO prueba VALUES ('.str_repeat('1', 64).');');

        try {
            $this->expectException(RuntimeException::class);
            iterator_to_array(app(SqlStatementReader::class)->statements($path));
        } finally {
            @unlink($path);
        }
    }
}
