<?php

namespace Modules\CRM\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class CRMServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'crm');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'crm');

        $this->registerRoutes();
        $this->registerCommands();
        $this->registerSchedules();
        $this->registerListeners();
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\CRM\Console\Commands\TickCampaignsCommand::class,
                \Modules\CRM\Console\Commands\AlertStalledDealsCommand::class,
            ]);
        }
    }

    protected function registerSchedules(): void
    {
        $this->callAfterResolving(\Illuminate\Console\Scheduling\Schedule::class, function (\Illuminate\Console\Scheduling\Schedule $schedule) {
            $schedule->command('campaigns:tick')
                ->everyFiveMinutes()
                ->withoutOverlapping();

            $schedule->command('deals:stalled --days=14')
                ->dailyAt('09:00')
                ->timezone(config('app.timezone', 'Europe/Istanbul'))
                ->withoutOverlapping();
        });
    }

    protected function registerListeners(): void
    {
        \Modules\CRM\Models\Lead::observe(\Modules\CRM\Observers\LeadCampaignObserver::class);
        \Modules\CRM\Models\Deal::observe(\Modules\CRM\Observers\DealStageObserver::class);
    }

    /**
     * Register module routes
     */
    protected function registerRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('admin')
            ->name('admin.')
            ->group(__DIR__ . '/../Routes/web.php');

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/crm')
            ->name('api.crm.')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
