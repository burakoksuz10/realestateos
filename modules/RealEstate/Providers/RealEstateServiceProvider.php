<?php

namespace Modules\RealEstate\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class RealEstateServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);

        $this->app->singleton(\Modules\RealEstate\Services\Portals\PortalManager::class, function ($app) {
            $manager = new \Modules\RealEstate\Services\Portals\PortalManager($app);
            $manager->register('sahibinden', \Modules\RealEstate\Services\Portals\SahibindenConnector::class);
            $manager->register('hepsiemlak', \Modules\RealEstate\Services\Portals\HepsiemlakConnector::class);
            $manager->register('emlakjet',   \Modules\RealEstate\Services\Portals\EmlakJetConnector::class);
            return $manager;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'realestate');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'realestate');
        
        $this->registerRoutes();
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
            ->prefix('api/realestate')
            ->name('api.realestate.')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
