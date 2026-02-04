<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \Modules\RealEstate\Models\Listing::class => \Modules\RealEstate\Policies\ListingPolicy::class,
        \Modules\CRM\Models\Lead::class => \Modules\CRM\Policies\LeadPolicy::class,
        \Modules\CRM\Models\Deal::class => \Modules\CRM\Policies\DealPolicy::class,
        \Modules\CRM\Models\Contact::class => \Modules\CRM\Policies\ContactPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define gates for specific actions
        Gate::define('access-admin', function ($user) {
            return $user->hasAnyRole(['super-admin', 'admin', 'office-manager', 'agent']);
        });

        Gate::define('manage-offices', function ($user) {
            return $user->hasAnyRole(['super-admin', 'admin']);
        });

        Gate::define('manage-users', function ($user) {
            return $user->hasAnyRole(['super-admin', 'admin', 'office-manager']);
        });

        Gate::define('view-reports', function ($user) {
            return $user->hasAnyRole(['super-admin', 'admin', 'office-manager']);
        });

        Gate::define('manage-integrations', function ($user) {
            return $user->hasAnyRole(['super-admin', 'admin']);
        });

        Gate::define('access-ai-features', function ($user) {
            return $user->hasAnyRole(['super-admin', 'admin', 'office-manager', 'agent']);
        });

        Gate::define('manage-mls', function ($user) {
            return $user->hasAnyRole(['super-admin', 'admin', 'office-manager']);
        });
    }
}
