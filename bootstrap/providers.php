<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
    
    // Module Service Providers
    Modules\Core\Providers\CoreServiceProvider::class,
    Modules\RealEstate\Providers\RealEstateServiceProvider::class,
    Modules\CRM\Providers\CRMServiceProvider::class,
    Modules\AI\Providers\AIServiceProvider::class,
    Modules\Integrations\Providers\IntegrationsServiceProvider::class,
    Modules\Websites\Providers\WebsitesServiceProvider::class,
    Modules\BI\Providers\BIServiceProvider::class,
];
