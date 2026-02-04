<?php

namespace Modules\Websites\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class WebsitesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'websites');
        
        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('admin/websites')
            ->name('admin.websites.')
            ->group(__DIR__ . '/../Routes/web.php');

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/websites')
            ->name('api.websites.')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
