<?php

namespace Modules\VoiceAgent\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\VoiceAgent\Http\Middleware\VerifyAgentToken;

class VoiceAgentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations'));
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'voice-agent');

        $this->registerMiddleware();
        $this->registerRoutes();
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('voice-agent-auth', VerifyAgentToken::class);
    }

    protected function registerRoutes(): void
    {
        // Admin UI
        Route::middleware(['web', 'auth'])
            ->prefix('admin/ai/voice-agent')
            ->name('admin.voice-agent.')
            ->group(__DIR__ . '/../Routes/web.php');

        // API — ElevenLabs Agent tool calls + webhook
        Route::middleware(['api', 'voice-agent-auth'])
            ->prefix('api/voice-agent')
            ->name('api.voice-agent.')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
