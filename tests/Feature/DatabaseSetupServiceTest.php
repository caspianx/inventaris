<?php

namespace Tests\Feature;

use App\Services\DatabaseSetupService;
use Tests\TestCase;

class DatabaseSetupServiceTest extends TestCase
{
    public function test_it_creates_missing_sqlite_database_file(): void
    {
        $databasePath = database_path('temp-test.sqlite');

        if (file_exists($databasePath)) {
            @unlink($databasePath);
        }

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $databasePath,
        ]);

        $service = new DatabaseSetupService();

        $this->assertTrue($service->ensureDatabaseExists());
        $this->assertFileExists($databasePath);
    }

    public function test_it_updates_env_file_with_database_configuration(): void
    {
        $envPath = base_path('.env.testing');

        if (file_exists($envPath)) {
            @unlink($envPath);
        }

        file_put_contents($envPath, "APP_NAME=Laravel\n");

        $service = new DatabaseSetupService();

        $result = $service->saveDatabaseConfig([
            'connection' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'inventory_test',
            'username' => 'root',
            'password' => '',
        ], $envPath);

        $this->assertTrue($result);
        $this->assertStringContainsString('DB_CONNECTION=mysql', file_get_contents($envPath));
        $this->assertStringContainsString('DB_DATABASE=inventory_test', file_get_contents($envPath));
        $this->assertStringContainsString('DB_USERNAME=root', file_get_contents($envPath));
    }

    public function test_install_page_is_accessible_when_database_config_is_missing(): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => '',
        ]);

        $response = $this->get('/install');

        $response->assertStatus(200);
        $response->assertSee('Konfigurasi Database');
    }
}
