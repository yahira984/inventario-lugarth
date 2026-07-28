<?php

namespace Tests\Unit;

use App\Support\SqlBackupNormalizer;
use Tests\TestCase;

class SqlBackupNormalizerTest extends TestCase
{
    public function test_it_prepares_a_cross_version_mysql_backup_without_changing_its_data(): void
    {
        $source = storage_path('framework/testing-backup-source.sql');
        $destination = storage_path('framework/testing-backup-normalized.sql');
        file_put_contents($source, "\xEF\xBB\xBF".<<<'SQL'
-- MySQL dump
-- Server version	9.7.1
/*!999999\- enable the sandbox mode */
CREATE DATABASE `otra_base`;
USE `otra_base`;
SET @@GLOBAL.GTID_PURGED='abc';
CREATE TABLE `prueba` (`id` bigint, `texto` varchar(100)) COLLATE=utf8mb4_0900_ai_ci;
LOCK TABLES `prueba` WRITE;
INSERT INTO `prueba` VALUES (1, 'NO_AUTO_CREATE_USER, utf8mb4_0900_ai_ci y DEFINER=foo@bar se conservan');
UNLOCK TABLES;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vista_prueba` AS SELECT 1 AS `id` */;
SET SQL_MODE='NO_AUTO_CREATE_USER,STRICT_TRANS_TABLES';
SQL);

        try {
            $result = app(SqlBackupNormalizer::class)->normalize($source, $destination);
            $sql = file_get_contents($destination);
        } finally {
            @unlink($source);
            @unlink($destination);
        }

        $this->assertSame('9.7.1', $result['source_version']);
        $this->assertSame(1, $result['changes']['sandbox']);
        $this->assertSame(2, $result['changes']['database_selection']);
        $this->assertSame(1, $result['changes']['gtid']);
        $this->assertSame(2, $result['changes']['table_locks']);
        $this->assertSame(1, $result['changes']['collations']);
        $this->assertSame(1, $result['changes']['definers']);
        $this->assertStringNotContainsString('CREATE DATABASE', $sql);
        $this->assertStringNotContainsString('GTID_PURGED', $sql);
        $this->assertStringNotContainsString('DEFINER=`root`', $sql);
        $this->assertStringContainsString('COLLATE=utf8mb4_unicode_ci', $sql);
        $this->assertStringContainsString(
            "INSERT INTO `prueba` VALUES (1, 'NO_AUTO_CREATE_USER, utf8mb4_0900_ai_ci y DEFINER=foo@bar se conservan')",
            $sql,
        );
        $this->assertStringContainsString("SET SQL_MODE='STRICT_TRANS_TABLES'", $sql);
    }
}
