<?php

namespace Modules\Telegram\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Telegram\Services\TelegramService;

class TelegramServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TelegramService::class, function ($app) {
            return new TelegramService();
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'telegram');

        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        // Admin UI
        Route::middleware(['web', 'auth'])
            ->prefix('admin/telegram')
            ->name('admin.telegram.')
            ->group(__DIR__ . '/../Routes/web.php');

        // Public webhook (no auth — Telegram posts here)
        Route::middleware(['api'])
            ->prefix('api/telegram')
            ->name('api.telegram.')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
