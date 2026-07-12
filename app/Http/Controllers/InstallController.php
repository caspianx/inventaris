<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DatabaseSetupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class InstallController extends Controller
{
    public function show()
    {
        if (! $this->databaseConfigIsAvailable()) {
            return view('install', [
                'ready' => false,
                'hasTables' => false,
                'database_error' => 'Konfigurasi database belum lengkap. Isi form di bawah untuk melanjutkan instalasi.',
            ]);
        }

        try {
            if (! Schema::hasTable((new User())->getTable())) {
                return view('install', [
                    'ready' => false,
                    'hasTables' => false,
                ]);
            }

            // Pastikan kolom `role` ada sebelum melakukan query untuk menghindari
            // error ketika tabel users berasal dari instalasi lama dengan skema berbeda.
            $userTable = (new User())->getTable();
            if (Schema::hasColumn($userTable, 'role')) {
                try {
                    if (User::where('role', 'admin')->exists()) {
                        return redirect()->route('login');
                    }
                } catch (\Throwable $e) {
                    // Jika query gagal karena skema yang tidak terduga, anggap belum siap
                    // dan lanjutkan ke tampilan instalasi supaya migrasi bisa dijalankan.
                }
            }

            return view('install', [
                'ready' => true,
                'hasTables' => true,
            ]);
        } catch (\Throwable $e) {
            return view('install', [
                'ready' => false,
                'hasTables' => false,
                'database_error' => 'Tidak dapat terhubung ke database. Periksa konfigurasi database atau isi form di bawah.',
            ]);
        }
    }

    protected function databaseConfigIsAvailable(): bool
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (empty($config)) {
            return false;
        }

        if ($connection === 'sqlite') {
            return ! empty($config['database']);
        }

        return ! empty($config['database']) && ! empty($config['username']);
    }

    public function setup(Request $request)
    {
        $data = $request->validate([
            'db_connection' => ['nullable', 'string', 'in:mysql,sqlite,pgsql'],
            'db_host' => ['nullable', 'string', 'max:255'],
            'db_port' => ['nullable', 'string', 'max:10'],
            'db_database' => ['nullable', 'string', 'max:255'],
            'db_username' => ['nullable', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
        ]);

        $databaseSetupService = new DatabaseSetupService();

        if (! empty($data['db_connection'])) {
            $databaseSetupService->saveDatabaseConfig([
                'connection' => $data['db_connection'],
                'host' => $data['db_host'] ?? '127.0.0.1',
                'port' => $data['db_port'] ?? '3306',
                'database' => $data['db_database'] ?? '',
                'username' => $data['db_username'] ?? 'root',
                'password' => $data['db_password'] ?? '',
            ]);

            $this->applyDatabaseConfig($data);
        }

        $dbResult = $databaseSetupService->createDatabaseIfMissing();

        if (! ($dbResult['ok'] ?? false)) {
            return redirect()->route('install')->withErrors([
                'database' => $dbResult['error'] ?? 'Gagal membuat atau mengakses database. Periksa kredensial dan hak akses pengguna database.',
            ]);
        }

        $messages = [];
        if (($dbResult['status'] ?? null) === 'created') {
            $messages[] = 'Database berhasil dibuat.';
        } elseif (($dbResult['status'] ?? null) === 'exists') {
            $messages[] = 'Database sudah ada.';
        }

            // Jika database baru dibuat, tampilkan alert di halaman instalasi lalu
            // redirect ke menu migrasi setelah beberapa saat agar pengguna melihat pesan.
            return redirect()->route('install')->with([
                'status' => implode(' ', $messages),
                'redirect_to_migrate' => true,
            ]);
    }

    public function showMigrateExisting()
    {
        $migrationFiles = glob(database_path('migrations') . '/*.php');
        $items = [];

        $migrationsTableExists = Schema::hasTable('migrations');

        foreach ($migrationFiles as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $table = null;
            if (preg_match('/create_([a-z0-9_]+)_table/i', $name, $m)) {
                $table = $m[1];
            }

            $exists = $table ? Schema::hasTable($table) : false;
            $marked = false;
            if ($migrationsTableExists) {
                try {
                    $marked = DB::table('migrations')->where('migration', $name)->exists();
                } catch (\Throwable $e) {
                    $marked = false;
                }
            }

            $items[] = [
                'migration' => $name,
                'file' => $file,
                'table' => $table,
                'exists' => $exists,
                'marked' => $marked,
            ];
        }

        return view('install_migrate', ['items' => $items]);
    }

    public function markMigrations(Request $request)
    {
        $selected = $request->input('migrations', []);
        $inserted = 0;

        foreach ($selected as $name) {
            if (! DB::table('migrations')->where('migration', $name)->exists()) {
                DB::table('migrations')->insert([
                    'migration' => $name,
                    'batch' => 1,
                ]);
                $inserted++;
            }
        }

        if ($inserted > 0) {
            return redirect()->route('install.migrate')->with('status', "Berhasil menandai {$inserted} migrasi sebagai sudah dijalankan.");
        }

        return redirect()->route('install.migrate')->withErrors(['migrate' => 'Tidak ada migrasi baru yang ditandai.']);
    }

    public function runMigrations(Request $request)
    {
        // Ensure migrations table exists
        if (! Schema::hasTable('migrations')) {
            try {
                Artisan::call('migrate:install');
            } catch (\Throwable $e) {
                return redirect()->route('install.migrate')->withErrors(['migrate' => 'Gagal membuat table migrations: ' . $e->getMessage()]);
            }
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            $migrateOutput = Artisan::output();
        } catch (\Throwable $e) {
            return redirect()->route('install.migrate')->withErrors(['migrate' => 'Gagal menjalankan migrate: ' . $e->getMessage()]);
        }

        $message = 'Migrasi selesai (struktur tabel saja).';
        if (! empty($migrateOutput)) {
            $message .= ' Output: ' . trim($migrateOutput);
        }

        // After successful migrations, redirect back to install so admin creation form appears
        return redirect()->route('install')->with('status', $message);
    }

    protected function applyDatabaseConfig(array $data): void
    {
        $connection = $data['db_connection'];
        $config = config("database.connections.{$connection}", []);

        if ($connection === 'sqlite') {
            $config['database'] = $data['db_database'];
        } else {
            $config['host'] = $data['db_host'] ?? $config['host'] ?? '127.0.0.1';
            $config['port'] = $data['db_port'] ?? $config['port'] ?? '3306';
            $config['database'] = $data['db_database'] ?? $config['database'] ?? '';
            $config['username'] = $data['db_username'] ?? $config['username'] ?? 'root';
            $config['password'] = $data['db_password'] ?? $config['password'] ?? '';
        }

        Config::set('database.default', $connection);
        Config::set("database.connections.{$connection}", $config);
        DB::purge($connection);
        DB::reconnect($connection);
    }

    public function store(Request $request)
    {
        if (! Schema::hasTable((new User())->getTable())) {
            return redirect()->route('install')->withErrors([
                'database' => 'Tabel pengguna belum ada. Silakan jalankan php artisan migrate terlebih dahulu.',
            ]);
        }

        $userTable = (new User())->getTable();
        if (Schema::hasColumn($userTable, 'role')) {
            try {
                if (User::where('role', 'admin')->exists()) {
                    return redirect()->route('login');
                }
            } catch (\Throwable $e) {
                // ignore and continue to allow manual admin creation
            }
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'admin',
        ]);

        return redirect()->route('login')->with('status', 'Admin berhasil dibuat. Silakan login.');
    }
}
