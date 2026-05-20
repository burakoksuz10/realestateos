<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind a configured OpenAI client as a singleton.
        // Office-level overrides happen inside services (AIService) by re-resolving.
        $this->app->singleton(\OpenAI\Client::class, function ($app) {
            $apiKey = config('reos.ai.openai_key') ?: 'sk-placeholder-not-configured';
            $org    = config('reos.ai.openai_organization') ?: null;

            return \OpenAI::client($apiKey, $org);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Prevent lazy loading in development
        Model::preventLazyLoading(!$this->app->isProduction());
        
        // Prevent silently discarding attributes
        Model::preventSilentlyDiscardingAttributes(!$this->app->isProduction());
        
        // Implicitly grant "Super Admin" role all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });
    }
}
