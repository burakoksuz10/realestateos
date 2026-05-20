<?php

namespace Modules\Integrations\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class IntegrationsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register connectors
        $this->app->singleton(\Modules\Integrations\Connectors\PortalConnector::class);
        $this->app->singleton(\Modules\Integrations\Connectors\SMSConnector::class);
        $this->app->singleton(\Modules\Integrations\Connectors\WhatsAppConnector::class);
        $this->app->singleton(\Modules\Integrations\Connectors\CallConnector::class);
        $this->app->singleton(\Modules\Integrations\Connectors\PaymentConnector::class);

        // Unified Inbox kanal kayıt merkezi
        $this->app->singleton(\Modules\Integrations\Channels\ChannelManager::class, function ($app) {
            $manager = new \Modules\Integrations\Channels\ChannelManager($app);
            $manager->register('telegram', \Modules\Integrations\Channels\TelegramChannel::class);
            $manager->register('whatsapp', \Modules\Integrations\Channels\WhatsAppChannel::class);
            $manager->register('sms', \Modules\Integrations\Channels\SmsChannel::class);
            $manager->register('email', \Modules\Integrations\Channels\EmailChannel::class);
            return $manager;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'integrations');
        
        $this->registerRoutes();
    }

    /**
     * Register module routes
     */
    protected function registerRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('admin/integrations')
            ->name('admin.integrations.')
            ->group(__DIR__ . '/../Routes/web.php');

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/integrations')
            ->name('api.integrations.')
            ->group(__DIR__ . '/../Routes/api.php');

        // Webhook routes (no auth)
        Route::middleware(['api'])
            ->prefix('webhooks')
            ->name('webhooks.')
            ->group(__DIR__ . '/../Routes/webhooks.php');
    }
}
