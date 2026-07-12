<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class HandleInstallationPhase
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jika aplikasi sedang menjalankan perintah artisan, lewati pemeriksaan.
        if (app()->runningInConsole()) {
            return $next($request);
        }

        // Jika route instalasi sudah dipanggil atau konfigurasi awal belum lengkap,
        // pakai mode instalasi.
        if ($this->isInstallationRoute($request) || $this->isInstallationPhase()) {
            config([
                'session.driver' => 'cookie',
            ]);

            $this->refreshSessionBindings();
        }

        // Jika konfigurasi terlihat lengkap tetapi koneksi DB gagal (database belum dibuat),
        // redirect ke halaman instalasi supaya user dapat membuat database.
        if (! $this->isInstallationRoute($request) && ! $this->isInstallationPhase()) {
            if (! $this->databaseIsAccessible()) {
                // Redirect with a query flag so the install page can show an immediate alert
                return redirect()->route('install', ['db_unavailable' => 1]);
            }
        }

        return $next($request);
    }

    protected function refreshSessionBindings(): void
    {
        $app = app();

        if ($app->resolved(SessionManager::class) || $app->resolved('session')) {
            $manager = $app->make(SessionManager::class);
            $manager->setDefaultDriver('cookie');
            $manager->forgetDrivers();
        }

        if ($app->resolved('session.store')) {
            $app->forgetInstance('session.store');
        }

        if ($app->resolved(StartSession::class)) {
            $app->forgetInstance(StartSession::class);
        }
    }

    protected function isInstallationPhase(): bool
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (empty($config)) {
            return true;
        }

        if ($connection === 'sqlite') {
            return empty($config['database']);
        }

        return empty($config['database']) || empty($config['username']);
    }

    protected function isInstallationRoute(Request $request): bool
    {
        return str_starts_with($request->path(), 'install');
    }

    protected function databaseIsAccessible(): bool
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (empty($config)) {
            return false;
        }

        if ($connection === 'sqlite') {
            $db = $config['database'] ?? null;
            if (empty($db) || $db === ':memory:') {
                return false;
            }

            return file_exists($db);
        }

        try {
            // mencoba mendapatkan PDO untuk memastikan kredensial dan database valid
            DB::connection($connection)->getPdo();

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
