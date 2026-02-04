<?php

namespace Modules\Integrations\Connectors;

use Modules\RealEstate\Models\Listing;
use Modules\RealEstate\Models\PortalSyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PortalConnector
{
    protected array $portals = [
        'sahibinden' => SahibindenConnector::class,
        'hepsiemlak' => HepsiemlakConnector::class,
        'emlakjet' => EmlakjetConnector::class,
    ];

    /**
     * Sync listing to portal
     */
    public function sync(Listing $listing, string $portal): PortalSyncLog
    {
        $connector = $this->getConnector($portal);
        
        $log = PortalSyncLog::create([
            'listing_id' => $listing->id,
            'portal' => $portal,
            'action' => $listing->portal_ids[$portal] ?? null ? 'update' : 'create',
            'status' => 'pending',
        ]);

        try {
            $result = $connector->sync($listing);
            
            $log->update([
                'status' => 'success',
                'portal_listing_id' => $result['portal_id'] ?? null,
                'response_data' => $result,
                'synced_at' => now(),
            ]);

            // Update listing with portal ID
            $portalIds = $listing->portal_ids ?? [];
            $portalIds[$portal] = $result['portal_id'] ?? null;
            $listing->update([
                'portal_ids' => $portalIds,
                'last_synced_at' => now(),
            ]);

        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            
            Log::error("Portal sync failed: {$portal}", [
                'listing_id' => $listing->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $log;
    }

    /**
     * Sync listing to all enabled portals
     */
    public function syncAll(Listing $listing): array
    {
        $results = [];
        
        foreach ($this->portals as $portal => $connector) {
            if (config("reos.portals.{$portal}.enabled")) {
                $results[$portal] = $this->sync($listing, $portal);
            }
        }

        return $results;
    }

    /**
     * Remove listing from portal
     */
    public function remove(Listing $listing, string $portal): bool
    {
        $connector = $this->getConnector($portal);
        $portalId = $listing->portal_ids[$portal] ?? null;

        if (!$portalId) {
            return false;
        }

        try {
            $connector->delete($portalId);
            
            $portalIds = $listing->portal_ids ?? [];
            unset($portalIds[$portal]);
            $listing->update(['portal_ids' => $portalIds]);

            PortalSyncLog::create([
                'listing_id' => $listing->id,
                'portal' => $portal,
                'action' => 'delete',
                'status' => 'success',
                'synced_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Portal remove failed: {$portal}", [
                'listing_id' => $listing->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get connector instance
     */
    protected function getConnector(string $portal): BasePortalConnector
    {
        $class = $this->portals[$portal] ?? null;
        
        if (!$class) {
            throw new \InvalidArgumentException("Unknown portal: {$portal}");
        }

        return app($class);
    }
}

abstract class BasePortalConnector
{
    abstract public function sync(Listing $listing): array;
    abstract public function delete(string $portalId): bool;
    abstract public function getStatus(string $portalId): array;
    
    protected function transformListing(Listing $listing): array
    {
        return [
            'title' => $listing->title,
            'description' => $listing->description,
            'price' => $listing->price,
            'currency' => $listing->price_currency,
            'type' => $listing->type,
            'category' => $listing->category,
            'listing_type' => $listing->listing_type,
            'city' => $listing->city,
            'district' => $listing->district,
            'neighborhood' => $listing->neighborhood,
            'address' => $listing->address,
            'latitude' => $listing->latitude,
            'longitude' => $listing->longitude,
            'gross_sqm' => $listing->gross_sqm,
            'net_sqm' => $listing->net_sqm,
            'room_count' => $listing->room_count,
            'living_room_count' => $listing->living_room_count,
            'bathroom_count' => $listing->bathroom_count,
            'floor_number' => $listing->floor_number,
            'total_floors' => $listing->total_floors,
            'building_age' => $listing->building_age,
            'heating_type' => $listing->heating_type,
            'is_furnished' => $listing->is_furnished,
            'features' => $listing->features,
            'photos' => $listing->getMedia('photos')->map(fn($m) => $m->getUrl())->toArray(),
        ];
    }
}

class SahibindenConnector extends BasePortalConnector
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.sahibinden.url', 'https://api.sahibinden.com');
        $this->apiKey = config('services.sahibinden.api_key');
    }

    public function sync(Listing $listing): array
    {
        $data = $this->transformListing($listing);
        
        // Map to Sahibinden format
        $payload = $this->mapToSahibindenFormat($data);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
        ])->post("{$this->baseUrl}/listings", $payload);

        if (!$response->successful()) {
            throw new \Exception($response->body());
        }

        return $response->json();
    }

    public function delete(string $portalId): bool
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
        ])->delete("{$this->baseUrl}/listings/{$portalId}");

        return $response->successful();
    }

    public function getStatus(string $portalId): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
        ])->get("{$this->baseUrl}/listings/{$portalId}");

        return $response->json();
    }

    protected function mapToSahibindenFormat(array $data): array
    {
        // Map internal format to Sahibinden API format
        return $data; // Placeholder - would need actual mapping
    }
}

class HepsiemlakConnector extends BasePortalConnector
{
    public function sync(Listing $listing): array
    {
        // Implementation for Hepsiemlak
        return ['portal_id' => 'HE-' . uniqid()];
    }

    public function delete(string $portalId): bool
    {
        return true;
    }

    public function getStatus(string $portalId): array
    {
        return ['status' => 'active'];
    }
}

class EmlakjetConnector extends BasePortalConnector
{
    public function sync(Listing $listing): array
    {
        // Implementation for Emlakjet
        return ['portal_id' => 'EJ-' . uniqid()];
    }

    public function delete(string $portalId): bool
    {
        return true;
    }

    public function getStatus(string $portalId): array
    {
        return ['status' => 'active'];
    }
}
