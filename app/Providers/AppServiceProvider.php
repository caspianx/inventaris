<?php

namespace App\Providers;

use App\Console\Commands\CleanOldPrintReceipts;
use App\Console\Commands\SimulateAllFeatures;
use App\Console\Commands\SimulateSalePrint;
use App\Models\StoreSetting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            SimulateSalePrint::class,
            SimulateAllFeatures::class,
            CleanOldPrintReceipts::class,
            \App\Console\Commands\FindDuplicateUserNames::class,
        ]);
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('receipts:cleanup --days=30')->daily();
        });

        View::composer('*', function ($view) {
            try {
                if ($this->isInstallationPhase()) {
                    return;
                }
                $view->with('storeSetting', StoreSetting::current());
            } catch (\Throwable $e) {
                // Silently skip if database is not ready
            }
        });
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
}
