<?php

namespace Modules\CRM\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the module.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // 'Modules\CRM\Events\LeadCreated' => [
        //     'Modules\CRM\Listeners\NotifyAgentOfNewLead',
        //     'Modules\CRM\Listeners\ScoreNewLead',
        // ],
        // 'Modules\CRM\Events\DealClosed' => [
        //     'Modules\CRM\Listeners\CalculateCommission',
        //     'Modules\CRM\Listeners\SendClosingNotification',
        // ],
    ];

    /**
     * Register any events for your module.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
