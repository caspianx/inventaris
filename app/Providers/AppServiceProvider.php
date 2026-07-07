<?php

namespace App\Providers;

use App\Console\Commands\SimulateAllFeatures;
use App\Console\Commands\SimulateSalePrint;
use App\Models\StoreSetting;
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
            \App\Console\Commands\FindDuplicateUserNames::class,
        ]);
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('*', function ($view) {
            $view->with('storeSetting', StoreSetting::current());
        });
    }
}
