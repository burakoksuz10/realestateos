<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        
        // Lead Events
        \Modules\CRM\Events\LeadCreated::class => [
            \Modules\CRM\Listeners\AssignLeadToAgent::class,
            \Modules\CRM\Listeners\CalculateLeadScore::class,
            \Modules\AI\Listeners\GenerateLeadSuggestions::class,
            \Modules\BI\Listeners\TrackLeadEvent::class,
        ],
        
        \Modules\CRM\Events\LeadUpdated::class => [
            \Modules\CRM\Listeners\RecalculateLeadScore::class,
            \Modules\BI\Listeners\TrackLeadEvent::class,
        ],
        
        \Modules\CRM\Events\LeadConverted::class => [
            \Modules\CRM\Listeners\CreateDealFromLead::class,
            \Modules\BI\Listeners\TrackConversion::class,
        ],
        
        // Deal Events
        \Modules\CRM\Events\DealCreated::class => [
            \Modules\CRM\Listeners\NotifyDealAssignee::class,
            \Modules\BI\Listeners\TrackDealEvent::class,
        ],
        
        \Modules\CRM\Events\DealStageChanged::class => [
            \Modules\CRM\Listeners\NotifyDealStageChange::class,
            \Modules\BI\Listeners\TrackDealEvent::class,
        ],
        
        \Modules\CRM\Events\DealClosed::class => [
            \Modules\CRM\Listeners\CalculateCommission::class,
            \Modules\BI\Listeners\TrackDealClosure::class,
        ],
        
        // Listing Events
        \Modules\RealEstate\Events\ListingCreated::class => [
            \Modules\RealEstate\Listeners\ProcessListingMedia::class,
            \Modules\AI\Listeners\GenerateListingContent::class,
            \Modules\BI\Listeners\TrackListingEvent::class,
        ],
        
        \Modules\RealEstate\Events\ListingPublished::class => [
            \Modules\Integrations\Listeners\SyncToPortals::class,
            \Modules\CRM\Listeners\MatchListingToLeads::class,
            \Modules\BI\Listeners\TrackListingEvent::class,
        ],
        
        \Modules\RealEstate\Events\ListingViewed::class => [
            \Modules\BI\Listeners\TrackListingView::class,
        ],
        
        // Call Events
        \Modules\Integrations\Events\CallReceived::class => [
            \Modules\AI\Listeners\AnalyzeCall::class,
            \Modules\CRM\Listeners\LogCallActivity::class,
        ],
        
        \Modules\Integrations\Events\CallEnded::class => [
            \Modules\AI\Listeners\SummarizeCall::class,
            \Modules\CRM\Listeners\UpdateCallActivity::class,
        ],
        
        // WhatsApp Events
        \Modules\Integrations\Events\WhatsAppMessageReceived::class => [
            \Modules\CRM\Listeners\LogWhatsAppActivity::class,
            \Modules\AI\Listeners\AnalyzeWhatsAppIntent::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
