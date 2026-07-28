<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\DatabaseBackupManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_backup_history_and_engine_status(): void
    {
        $admin = $this->admin();
        $manager = Mockery::mock(DatabaseBackupManager::class);
        $manager->shouldReceive('purgeStaleChunkUploads')->once();
        $manager->shouldReceive('backups')->once()->andReturn(collect([
            [
                'path' => 'backups/respaldo_bd_20260728_090000.sql',
                'name' => 'respaldo_bd_20260728_090000.sql',
                'size' => 12582912,
                'size_label' => '12.0 MB',
                'modified_at' => 1785247200,
                'modified_label' => '28/07/2026 09:00',
            ],
        ]));
        $manager->shouldReceive('capabilities')->once()->andReturn([
            'native' => true,
            'engine' => 'MySQL nativo (9.7.1)',
            'database' => 'inventario',
            'server_version' => '9.7.1',
        ]);
        $this->app->instance(DatabaseBackupManager::class, $manager);

        $this->actingAs($admin)
            ->get(route('admin.backups.index'))
            ->assertOk()
            ->assertSee('Motor rápido disponible')
            ->assertSee('respaldo_bd_20260728_090000.sql')
            ->assertSee('12.0 MB')
            ->assertSee('Escribe RESTAURAR');
    }

    public function test_admin_can_create_backup_and_receives_download_url(): void
    {
        $admin = $this->admin();
        $manager = Mockery::mock(DatabaseBackupManager::class);
        $manager->shouldReceive('create')->once()->andReturn([
            'path' => 'backups/respaldo_bd_20260728_090000_abcd.sql',
            'name' => 'respaldo_bd_20260728_090000_abcd.sql',
            'size' => 11534336,
            'size_label' => '11.0 MB',
            'method' => 'nativo',
        ]);
        $this->app->instance(DatabaseBackupManager::class, $manager);

        $this->actingAs($admin)
            ->postJson(route('admin.backups.store'))
            ->assertCreated()
            ->assertJsonPath('backup.method', 'nativo')
            ->assertJsonPath('backup.size_label', '11.0 MB')
            ->assertJsonPath(
                'download_url',
                route('admin.backups.download', 'respaldo_bd_20260728_090000_abcd.sql'),
            );

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'modulo' => 'Base de datos',
            'accion' => 'Respaldo',
        ]);
    }

    public function test_restore_creates_safety_backup_and_returns_clear_result(): void
    {
        Storage::fake('local');
        $admin = $this->admin();
        $manager = Mockery::mock(DatabaseBackupManager::class);
        $manager->shouldReceive('temporaryRestorePath')
            ->once()
            ->with('sql')
            ->andReturn('backups/tmp/restaurar-prueba.sql');
        $manager->shouldReceive('assertValidSql')
            ->once()
            ->with(Mockery::on(fn (string $path): bool => is_file($path)));
        $manager->shouldReceive('create')
            ->once()
            ->with('antes_de_restaurar')
            ->andReturn([
                'path' => 'backups/antes_de_restaurar_20260728_090000.sql',
                'name' => 'antes_de_restaurar_20260728_090000.sql',
                'size' => 1024,
                'size_label' => '1.0 KB',
                'method' => 'nativo',
            ]);
        $manager->shouldReceive('restore')
            ->once()
            ->with(Mockery::on(fn (string $path): bool => is_file($path)))
            ->andReturn([
                'method' => 'nativo',
                'statements' => null,
                'migrations' => 'Nothing to migrate.',
                'source_version' => '8.0.39',
                'compatibility_fixes' => ['table_locks' => 2],
            ]);
        $this->app->instance(DatabaseBackupManager::class, $manager);

        $file = UploadedFile::fake()->createWithContent(
            'respaldo-antiguo.sql',
            "CREATE TABLE `prueba` (`id` bigint);\n",
        );

        $this->actingAs($admin)
            ->postJson(route('admin.backups.restore'), [
                'backup_sql' => $file,
                'confirmation' => 'RESTAURAR',
            ])
            ->assertOk()
            ->assertJsonPath(
                'safety_backup.name',
                'antes_de_restaurar_20260728_090000.sql',
            )
            ->assertJsonPath('redirect_url', route('admin.backups.index'));

        Storage::disk('local')->assertMissing('backups/tmp/restaurar-prueba.sql');
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'modulo' => 'Base de datos',
            'accion' => 'Restauración',
        ]);
    }

    public function test_failed_restore_recovers_the_previous_database_and_reports_the_reason(): void
    {
        Storage::fake('local');
        $admin = $this->admin();
        $inputPath = Storage::disk('local')->path('backups/tmp/restaurar-prueba.sql');
        $safetyPath = Storage::disk('local')->path('backups/antes_de_restaurar.sql');
        Storage::disk('local')->put('backups/antes_de_restaurar.sql', 'CREATE TABLE `segura` (`id` bigint);');

        $manager = Mockery::mock(DatabaseBackupManager::class);
        $manager->shouldReceive('temporaryRestorePath')
            ->once()
            ->with('sql')
            ->andReturn('backups/tmp/restaurar-prueba.sql');
        $manager->shouldReceive('assertValidSql')->once()->with($inputPath);
        $manager->shouldReceive('create')
            ->once()
            ->with('antes_de_restaurar')
            ->andReturn([
                'path' => 'backups/antes_de_restaurar.sql',
                'name' => 'antes_de_restaurar.sql',
                'size' => 1024,
                'size_label' => '1.0 KB',
                'method' => 'nativo',
            ]);
        $manager->shouldReceive('restore')
            ->once()
            ->with($inputPath)
            ->andThrow(new \RuntimeException('Collation desconocida.'));
        $manager->shouldReceive('backupPath')
            ->once()
            ->with('antes_de_restaurar.sql')
            ->andReturn($safetyPath);
        $manager->shouldReceive('restore')
            ->once()
            ->with($safetyPath)
            ->andReturn([
                'method' => 'nativo',
                'statements' => null,
                'migrations' => 'Nothing to migrate.',
                'source_version' => '8.0',
                'compatibility_fixes' => [],
            ]);
        $this->app->instance(DatabaseBackupManager::class, $manager);

        $file = UploadedFile::fake()->createWithContent(
            'respaldo-incompatible.sql',
            "CREATE TABLE `prueba` (`id` bigint);\n",
        );

        $this->actingAs($admin)
            ->postJson(route('admin.backups.restore'), [
                'backup_sql' => $file,
                'confirmation' => 'RESTAURAR',
            ])
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'El archivo no pudo aplicarse, pero la base anterior quedó recuperada correctamente. '
                .'Motivo: Collation desconocida.',
            );

        Storage::disk('local')->assertMissing('backups/tmp/restaurar-prueba.sql');
    }

    public function test_large_restore_can_arrive_in_small_chunks_and_be_reassembled(): void
    {
        Storage::fake('local');
        $admin = $this->admin();
        $uploadId = str_repeat('a', 32);
        $firstPart = "-- Respaldo\nCREATE TABLE `prueba` (`id` bigint);\n";
        $secondPart = "INSERT INTO `prueba` VALUES (1);\n";
        $totalSize = strlen($firstPart.$secondPart);

        foreach ([$firstPart, $secondPart] as $index => $contents) {
            $this->actingAs($admin)
                ->post(route('admin.backups.restore.chunk'), [
                    'backup_chunk' => UploadedFile::fake()->createWithContent(
                        "chunk-{$index}.part",
                        $contents,
                    ),
                    'upload_id' => $uploadId,
                    'chunk_index' => $index,
                    'total_chunks' => 2,
                    'backup_name' => 'respaldo-grande.sql',
                    'total_size' => $totalSize,
                ], ['Accept' => 'application/json'])
                ->assertCreated()
                ->assertJsonPath('uploaded_chunks', $index + 1);
        }

        $manager = app(DatabaseBackupManager::class);
        $relativePath = $manager->assembleChunkedUpload(
            $admin->id,
            $uploadId,
            'respaldo-grande.sql',
        );

        $this->assertSame(
            $firstPart.$secondPart,
            Storage::disk('local')->get($relativePath),
        );

        $manager->discardChunkedUpload($admin->id, $uploadId);
        Storage::disk('local')->delete($relativePath);
    }

    public function test_restore_rejects_wrong_extension_and_missing_confirmation(): void
    {
        Storage::fake('local');
        $admin = $this->admin();
        $manager = Mockery::mock(DatabaseBackupManager::class);
        $this->app->instance(DatabaseBackupManager::class, $manager);

        $this->actingAs($admin)
            ->postJson(route('admin.backups.restore'), [
                'backup_sql' => UploadedFile::fake()->createWithContent('datos.csv', 'id,name'),
                'confirmation' => 'SI',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['confirmation']);
    }

    public function test_non_admin_cannot_manage_backups(): void
    {
        $warehouseUser = User::factory()->create([
            'role' => 'almacenista',
            'approved_at' => now(),
        ]);

        $this->actingAs($warehouseUser)
            ->postJson(route('admin.backups.store'))
            ->assertForbidden();

        $this->actingAs($warehouseUser)
            ->postJson(route('admin.backups.restore'))
            ->assertForbidden();
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'administrador',
            'approved_at' => now(),
        ]);
    }
}
