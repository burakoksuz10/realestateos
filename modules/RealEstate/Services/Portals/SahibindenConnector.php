<?php

namespace Modules\RealEstate\Services\Portals;

use Modules\RealEstate\Models\Listing;

/**
 * Sahibinden.com portal connector.
 *
 * Sahibinden Sahibinden Pro API kullanıyor (sahibinden.com/sahibindenpro).
 * `services.sahibinden.api_key` + `services.sahibinden.account_id` config'lerine
 * dayanır.
 *
 * NOT: Sahibinden API gerçek dokümantasyonu kapalı bir program — kurumsal
 * sözleşme + onay gerekiyor. Bu connector spec'e en yakın tahmini yapı sunar;
 * üye olduğunuzda alacağınız dökümantasyona göre `endpoint()` ve
 * `transformListing()` ince ayar yapılabilir.
 */
class SahibindenConnector extends AbstractPortalConnector
{
    protected string $portalKey = 'sahibinden';

    public function name(): string
    {
        return 'Sahibinden.com';
    }

    public function isConfigured(): bool
    {
        return !empty(config('services.sahibinden.api_key'));
    }

    protected function endpoint(string $action, ?string $portalListingId = null): string
    {
        $base = rtrim(config('services.sahibinden.base_url', 'https://api.sahibinden.com/v1'), '/');

        return match ($action) {
            'publish'      => "{$base}/listings",
            'update'       => "{$base}/listings/{$portalListingId}",
            'delete'       => "{$base}/listings/{$portalListingId}",
            'fetch_stats'  => "{$base}/listings/{$portalListingId}/stats",
            default        => $base,
        };
    }

    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . config('services.sahibinden.api_key'),
            'X-Account-Id'  => config('services.sahibinden.account_id'),
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }

    protected function transformListing(Listing $listing): array
    {
        return [
            'category_id'     => $this->mapCategory($listing),
            'title'           => $listing->title,
            'description'     => $listing->ai_description ?: $listing->description,
            'price'           => (int) $listing->price,
            'currency'        => $listing->price_currency ?? 'TRY',
            'listing_type'    => $listing->listing_type === 'sale' ? 'sale' : 'rent',
            'reference_no'    => $listing->reference_no,
            'gross_sqm'       => $listing->gross_sqm,
            'net_sqm'         => $listing->net_sqm,
            'room_count'      => $listing->room_count,
            'living_room_count' => $listing->living_room_count,
            'bathroom_count'  => $listing->bathroom_count,
            'floor'           => $listing->floor_number,
            'total_floors'    => $listing->total_floors,
            'building_age'    => $listing->building_age,
            'heating'         => $listing->heating,
            'address' => [
                'city'         => $listing->city,
                'district'     => $listing->district,
                'neighborhood' => $listing->neighborhood,
                'lat'          => $listing->latitude,
                'lng'          => $listing->longitude,
            ],
            'photos' => $this->photoUrls($listing),
            'features' => is_array($listing->features) ? array_values($listing->features) : [],
        ];
    }

    protected function mapCategory(Listing $listing): string
    {
        // Sahibinden category mapping — gerçek dokümantasyonda ID'ler olur
        return match ($listing->type) {
            'apartment' => 'real-estate-residence-apartment',
            'house'     => 'real-estate-residence-house',
            'villa'     => 'real-estate-residence-villa',
            'office'    => 'real-estate-commercial-office',
            'shop'      => 'real-estate-commercial-shop',
            'land'      => 'real-estate-land',
            default     => 'real-estate-residence-apartment',
        };
    }

    protected function photoUrls(Listing $listing): array
    {
        if (!method_exists($listing, 'getMedia')) return [];
        return $listing->getMedia('photos')
            ->take(20)
            ->map(fn ($m) => $m->getUrl())
            ->all();
    }
}
