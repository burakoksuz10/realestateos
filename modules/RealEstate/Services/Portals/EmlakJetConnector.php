<?php

namespace Modules\RealEstate\Services\Portals;

use Modules\RealEstate\Models\Listing;

/**
 * EmlakJet.com connector.
 *
 * EmlakJet Pro API. Konfigürasyon:
 * - `services.emlakjet.api_key`
 * - `services.emlakjet.account_id`
 */
class EmlakJetConnector extends AbstractPortalConnector
{
    protected string $portalKey = 'emlakjet';

    public function name(): string
    {
        return 'EmlakJet';
    }

    public function isConfigured(): bool
    {
        return !empty(config('services.emlakjet.api_key'));
    }

    protected function endpoint(string $action, ?string $portalListingId = null): string
    {
        $base = rtrim(config('services.emlakjet.base_url', 'https://api.emlakjet.com/v1'), '/');

        return match ($action) {
            'publish'      => "{$base}/ads",
            'update'       => "{$base}/ads/{$portalListingId}",
            'delete'       => "{$base}/ads/{$portalListingId}",
            'fetch_stats'  => "{$base}/ads/{$portalListingId}/stats",
            default        => $base,
        };
    }

    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . config('services.emlakjet.api_key'),
            'X-Account'     => config('services.emlakjet.account_id'),
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }

    protected function transformListing(Listing $listing): array
    {
        return [
            'ref'        => $listing->reference_no,
            'title'      => $listing->title,
            'desc'       => $listing->ai_description ?: $listing->description,
            'price'      => (float) $listing->price,
            'currency'   => $listing->price_currency ?? 'TRY',
            'sale_rent'  => $listing->listing_type === 'sale' ? 'sale' : 'rent',
            'type'       => $this->mapType($listing->type),
            'rooms'      => $listing->room_count,
            'salons'     => $listing->living_room_count ?? 1,
            'baths'      => $listing->bathroom_count,
            'gross'      => $listing->gross_sqm,
            'net'        => $listing->net_sqm,
            'floor'      => $listing->floor_number,
            'building_age' => $listing->building_age,
            'heat'       => $listing->heating,
            'address' => [
                'city'         => $listing->city,
                'district'     => $listing->district,
                'neighborhood' => $listing->neighborhood,
                'lat'          => $listing->latitude,
                'lng'          => $listing->longitude,
            ],
            'images'   => $this->photoUrls($listing),
            'features' => is_array($listing->features) ? array_values($listing->features) : [],
        ];
    }

    protected function mapType(?string $type): string
    {
        return match ($type) {
            'apartment' => 'daire',
            'house'     => 'mustakil-ev',
            'villa'     => 'villa',
            'office'    => 'ofis',
            'shop'      => 'dukkan',
            'land'      => 'arsa',
            default     => 'daire',
        };
    }

    protected function photoUrls(Listing $listing): array
    {
        if (!method_exists($listing, 'getMedia')) return [];
        return $listing->getMedia('photos')->take(20)->map(fn ($m) => $m->getUrl())->all();
    }
}
