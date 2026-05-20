<?php

namespace Modules\RealEstate\Services\Portals;

use Modules\RealEstate\Models\Listing;

/**
 * Portal entegrasyonu için ortak arayüz.
 *
 * Her portal connector (Sahibinden, Hepsiemlak, Emlakjet, ...)
 * bu arayüzü implement eder. PortalManager bunları çözer, controller
 * portal adından bağımsız iş yapar.
 *
 * Standart dönüş: ['success' => bool, 'message' => string, 'data' => array,
 *                  'portal_listing_id' => ?string, 'portal_url' => ?string]
 */
interface PortalConnectorInterface
{
    public function name(): string;

    public function isConfigured(): bool;

    public function publish(Listing $listing): array;

    public function update(Listing $listing, string $portalListingId): array;

    public function delete(Listing $listing, string $portalListingId): array;

    public function fetchStats(Listing $listing, string $portalListingId): array;
}
