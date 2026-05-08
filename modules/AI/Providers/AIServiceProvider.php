<?php

namespace Modules\AI\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\AI\Services\AIService;
use Modules\AI\Services\ValuationService;
use Modules\AI\Services\ContentService;
use Modules\AI\Services\TranslationService;
use Modules\AI\Services\MatchingService;
use Modules\AI\Services\CopilotService;
use Modules\AI\Services\NewsService;

class AIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AIService::class, function ($app) {
            return new AIService();
        });

        $this->app->singleton(ValuationService::class, function ($app) {
            return new ValuationService($app->make(AIService::class));
        });

        $this->app->singleton(ContentService::class, function ($app) {
            return new ContentService($app->make(AIService::class));
        });

        $this->app->singleton(TranslationService::class, function ($app) {
            return new TranslationService($app->make(AIService::class));
        });

        $this->app->singleton(MatchingService::class, function ($app) {
            return new MatchingService($app->make(AIService::class));
        });

        $this->app->singleton(CopilotService::class, function ($app) {
            return new CopilotService($app->make(AIService::class));
        });

        $this->app->singleton(NewsService::class, function ($app) {
            return new NewsService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'ai');
        
        $this->registerRoutes();
    }

    /**
     * Register module routes
     */
    protected function registerRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('admin/ai')
            ->name('admin.ai.')
            ->group(__DIR__ . '/../Routes/web.php');

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/ai')
            ->name('api.ai.')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
