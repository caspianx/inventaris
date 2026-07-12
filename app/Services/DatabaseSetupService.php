<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PDO;
use RuntimeException;

class DatabaseSetupService
{
    public function saveDatabaseConfig(array $config, ?string $envPath = null): bool
    {
        $envPath = $envPath ?? base_path('.env');

        if (! file_exists($envPath)) {
            return false;
        }

        $contents = file_get_contents($envPath);
        $replacements = [
            'DB_CONNECTION' => $config['connection'] ?? 'mysql',
            'DB_HOST' => $config['host'] ?? '127.0.0.1',
            'DB_PORT' => $config['port'] ?? '3306',
            'DB_DATABASE' => $config['database'] ?? '',
            'DB_USERNAME' => $config['username'] ?? 'root',
            'DB_PASSWORD' => $config['password'] ?? '',
        ];

        foreach ($replacements as $key => $value) {
            $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';
            if (preg_match($pattern, $contents)) {
                $contents = preg_replace($pattern, $key . '=' . $value, $contents);
            } else {
                $contents .= PHP_EOL . $key . '=' . $value;
            }
        }

        return file_put_contents($envPath, $contents) !== false;
    }

    public function ensureDatabaseExists(): bool
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (empty($config)) {
            return true;
        }

        if ($connection === 'sqlite') {
            return $this->ensureSqliteDatabaseExists($config);
        }

        if (in_array($connection, ['mysql', 'pgsql'], true)) {
            return $this->ensureServerDatabaseExists($connection, $config);
        }

        return true;
    }

    /**
     * Try to create the database if missing and return a detailed result.
     * Returns array with keys:
     *  - ok: bool
     *  - status: 'created'|'exists' (when ok=true)
     *  - error: string (when ok=false)
     */
    public function createDatabaseIfMissing(): array
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (empty($config)) {
            return ['ok' => false, 'error' => 'Konfigurasi koneksi database tidak lengkap.'];
        }

        if ($connection === 'sqlite') {
            $databasePath = $config['database'] ?? null;

            if (empty($databasePath) || $databasePath === ':memory:') {
                return ['ok' => false, 'error' => 'Nama database SQLite tidak diatur.'];
            }

            if (file_exists($databasePath)) {
                return ['ok' => true, 'status' => 'exists'];
            }

            try {
                $directory = dirname($databasePath);
                if (! is_dir($directory)) {
                    if (! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                        return ['ok' => false, 'error' => "Gagal membuat direktori database: {$directory}"];
                    }
                }

                if (! touch($databasePath)) {
                    return ['ok' => false, 'error' => "Gagal membuat file database: {$databasePath}"];
                }

                return ['ok' => true, 'status' => 'created'];
            } catch (\Throwable $e) {
                return ['ok' => false, 'error' => $e->getMessage()];
            }
        }

        if (in_array($connection, ['mysql', 'pgsql'], true)) {
            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? null;
            $database = $config['database'] ?? null;
            $username = $config['username'] ?? null;
            $password = $config['password'] ?? null;

            if (empty($database) || empty($username)) {
                return ['ok' => false, 'error' => 'Nama database atau username tidak diisi.'];
            }

            $dsn = $this->buildDsn($connection, $host, $port);

            try {
                $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

                if ($connection === 'mysql') {
                    // check if database exists
                    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . addslashes($database) . "'");
                    $exists = (bool) $stmt->fetchColumn();

                    if ($exists) {
                        return ['ok' => true, 'status' => 'exists'];
                    }

                    $pdo->exec("CREATE DATABASE `{$database}`");
                    return ['ok' => true, 'status' => 'created'];
                }

                // For pgsql, we'll attempt create database if not exists via simple check
                if ($connection === 'pgsql') {
                    $stmt = $pdo->query("SELECT 1 FROM pg_database WHERE datname='" . addslashes($database) . "'");
                    $exists = (bool) $stmt->fetchColumn();
                    if ($exists) {
                        return ['ok' => true, 'status' => 'exists'];
                    }

                    $pdo->exec("CREATE DATABASE \"{$database}\"");
                    return ['ok' => true, 'status' => 'created'];
                }

                return ['ok' => false, 'error' => 'Driver database tidak didukung untuk pembuatan otomatis.'];
            } catch (\Throwable $e) {
                return ['ok' => false, 'error' => $e->getMessage()];
            }
        }

        return ['ok' => false, 'error' => 'Driver database tidak didukung.'];
    }

    protected function ensureSqliteDatabaseExists(array $config): bool
    {
        $databasePath = $config['database'] ?? null;

        if (empty($databasePath) || $databasePath === ':memory:') {
            return true;
        }

        $directory = dirname($databasePath);

        if (! is_dir($directory)) {
            if (! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                throw new RuntimeException("Gagal membuat direktori database: {$directory}");
            }
        }

        if (! file_exists($databasePath) && ! touch($databasePath)) {
            throw new RuntimeException("Gagal membuat file database: {$databasePath}");
        }

        try {
            $pdo = new PDO('sqlite:' . $databasePath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo = null;

            return true;
        } catch (\Throwable $e) {
            Log::error('Gagal memastikan database SQLite tersedia', ['exception' => $e->getMessage()]);

            return false;
        }
    }

    protected function ensureServerDatabaseExists(string $connection, array $config): bool
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? null;
        $database = $config['database'] ?? null;
        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;

        if (empty($database) || empty($username)) {
            return true;
        }

        $dsn = $this->buildDsn($connection, $host, $port);

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            if ($connection === 'mysql') {
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}`");
            } else {
                $pdo->exec("SELECT 'CREATE DATABASE {$database}' WHERE 0");
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Gagal membuat database server otomatis', [
                'connection' => $connection,
                'database' => $database,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function buildDsn(string $connection, string $host, ?int $port): string
    {
        if ($connection === 'mysql') {
            $dsn = "mysql:host={$host}";
            if ($port) {
                $dsn .= ";port={$port}";
            }

            return $dsn;
        }

        if ($connection === 'pgsql') {
            $dsn = "pgsql:host={$host}";
            if ($port) {
                $dsn .= ";port={$port}";
            }

            return $dsn;
        }

        return '';
    }
}
