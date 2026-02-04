<?php

namespace Modules\RealEstate\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the module.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        \Modules\RealEstate\Events\ListingCreated::class => [
            \Modules\RealEstate\Listeners\ProcessListingMedia::class,
        ],
        \Modules\RealEstate\Events\ListingPublished::class => [
            \Modules\RealEstate\Listeners\NotifyMatchingLeads::class,
        ],
    ];
}
