<?php

namespace Modules\RealEstate\Services\Portals;

use Modules\RealEstate\Models\Listing;

/**
 * Hepsiemlak.com connector.
 *
 * Hepsiemlak Partner API ile çalışır (kurumsal). Konfigürasyon:
 * - `services.hepsiemlak.api_key`
 * - `services.hepsiemlak.partner_id`
 */
class HepsiemlakConnector extends AbstractPortalConnector
{
    protected string $portalKey = 'hepsiemlak';

    public function name(): string
    {
        return 'Hepsiemlak';
    }

    public function isConfigured(): bool
    {
        return !empty(config('services.hepsiemlak.api_key'));
    }

    protected function endpoint(string $action, ?string $portalListingId = null): string
    {
        $base = rtrim(config('services.hepsiemlak.base_url', 'https://api.hepsiemlak.com/v2'), '/');

        return match ($action) {
            'publish'      => "{$base}/listings",
            'update'       => "{$base}/listings/{$portalListingId}",
            'delete'       => "{$base}/listings/{$portalListingId}",
            'fetch_stats'  => "{$base}/listings/{$portalListingId}/analytics",
            default        => $base,
        };
    }

    protected function headers(): array
    {
        return [
            'X-Api-Key'    => config('services.hepsiemlak.api_key'),
            'X-Partner-Id' => config('services.hepsiemlak.partner_id'),
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function transformListing(Listing $listing): array
    {
        return [
            'externalRef'  => $listing->reference_no,
            'title'        => $listing->title,
            'description'  => $listing->ai_description ?: $listing->description,
            'price'        => (float) $listing->price,
            'currency'     => $listing->price_currency ?? 'TRY',
            'forSale'      => $listing->listing_type === 'sale',
            'propertyType' => $this->mapType($listing->type),
            'attributes' => [
                'grossArea'    => $listing->gross_sqm,
                'netArea'      => $listing->net_sqm,
                'rooms'        => $listing->room_count,
                'bathrooms'    => $listing->bathroom_count,
                'floor'        => $listing->floor_number,
                'totalFloors'  => $listing->total_floors,
                'buildingAge'  => $listing->building_age,
                'heatingType'  => $listing->heating,
                'facade'       => $listing->facade,
            ],
            'location' => [
                'city'         => $listing->city,
                'district'     => $listing->district,
                'neighborhood' => $listing->neighborhood,
                'latitude'     => $listing->latitude,
                'longitude'    => $listing->longitude,
            ],
            'images'   => $this->photoUrls($listing),
            'features' => is_array($listing->features) ? array_values($listing->features) : [],
        ];
    }

    protected function mapType(?string $type): string
    {
        return match ($type) {
            'apartment' => 'APARTMENT',
            'house'     => 'HOUSE',
            'villa'     => 'VILLA',
            'office'    => 'OFFICE',
            'shop'      => 'SHOP',
            'land'      => 'LAND',
            default     => 'APARTMENT',
        };
    }

    protected function photoUrls(Listing $listing): array
    {
        if (!method_exists($listing, 'getMedia')) return [];
        return $listing->getMedia('photos')->take(20)->map(fn ($m) => $m->getUrl())->all();
    }
}
