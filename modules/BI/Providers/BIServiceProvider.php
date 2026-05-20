<?php

namespace Modules\BI\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class BIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'bi');
        
        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('admin/reports')
            ->name('admin.reports.')
            ->group(__DIR__ . '/../Routes/web.php');

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/bi')
            ->name('api.bi.')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
