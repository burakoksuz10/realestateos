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
        $this->registerObservers();
        $this->registerCommands();
        $this->registerSchedules();
    }

    protected function registerObservers(): void
    {
        // Telegram observes CRM model changes directly (not via events),
        // which sidesteps the half-wired event classes in modules/CRM.
        \Modules\CRM\Models\Lead::observe(\Modules\Telegram\Observers\LeadObserver::class);
        \Modules\CRM\Models\Deal::observe(\Modules\Telegram\Observers\DealObserver::class);
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Telegram\Console\Commands\SendMorningBriefing::class,
                \Modules\Telegram\Console\Commands\SendTaskReminders::class,
            ]);
        }
    }

    protected function registerSchedules(): void
    {
        $this->callAfterResolving(\Illuminate\Console\Scheduling\Schedule::class, function (\Illuminate\Console\Scheduling\Schedule $schedule) {
            $schedule->command('telegram:morning-briefing')
                ->dailyAt('08:30')
                ->timezone(config('app.timezone', 'Europe/Istanbul'))
                ->withoutOverlapping();

            $schedule->command('telegram:task-reminders')
                ->everyFiveMinutes()
                ->withoutOverlapping();
        });
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
