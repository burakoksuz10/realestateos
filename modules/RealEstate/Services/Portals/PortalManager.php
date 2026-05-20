<?php

namespace Modules\RealEstate\Services\Portals;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Modules\RealEstate\Models\Listing;
use Modules\RealEstate\Models\PortalSyncLog;

/**
 * Portal connector registry + senkronizasyon orkestratörü.
 *
 * `RealEstateServiceProvider::register()` içinde singleton bind edilir,
 * 3 connector (sahibinden, hepsiemlak, emlakjet) önceden register edilir.
 *
 * Tüm sync çağrıları `PortalSyncLog` tablosuna kayıt yapar — hem başarı
 * hem başarısızlık (ki kullanıcı dashboard'dan görsün).
 */
class PortalManager
{
    /** @var array<string, PortalConnectorInterface> */
    protected array $connectors = [];

    public function __construct(protected Application $app) {}

    public function register(string $key, string|PortalConnectorInterface $connector): void
    {
        if (is_string($connector)) {
            $this->connectors[$key] = $this->app->make($connector);
        } else {
            $this->connectors[$key] = $connector;
        }
    }

    public function get(string $key): PortalConnectorInterface
    {
        if (!isset($this->connectors[$key])) {
            throw new \InvalidArgumentException("Portal not registered: {$key}");
        }
        return $this->connectors[$key];
    }

    public function has(string $key): bool
    {
        return isset($this->connectors[$key]);
    }

    /**
     * @return array<string, PortalConnectorInterface>
     */
    public function all(): array
    {
        return $this->connectors;
    }

    /**
     * İlanı bir portala yayınla (publish) veya zaten yayındaysa güncelle.
     */
    public function syncTo(Listing $listing, string $portalKey): array
    {
        $connector = $this->get($portalKey);

        // Mevcut portal_listing_id var mı? Varsa update, yoksa publish.
        $lastSuccess = PortalSyncLog::query()
            ->where('listing_id', $listing->id)
            ->where('portal', $portalKey)
            ->where('status', 'success')
            ->whereIn('action', ['publish', 'update'])
            ->whereNotNull('portal_listing_id')
            ->latest()
            ->first();

        $result = $lastSuccess
            ? $connector->update($listing, $lastSuccess->portal_listing_id)
            : $connector->publish($listing);

        $this->logResult($listing, $portalKey, $result);

        return $result;
    }

    /**
     * İlanı bir portaldan kaldır.
     */
    public function removeFrom(Listing $listing, string $portalKey): array
    {
        $connector = $this->get($portalKey);

        $lastSuccess = PortalSyncLog::query()
            ->where('listing_id', $listing->id)
            ->where('portal', $portalKey)
            ->where('status', 'success')
            ->whereIn('action', ['publish', 'update'])
            ->whereNotNull('portal_listing_id')
            ->latest()
            ->first();

        if (!$lastSuccess) {
            return [
                'success' => false,
                'action'  => 'delete',
                'message' => "Bu ilan {$connector->name()} portalında bulunamadı.",
            ];
        }

        $result = $connector->delete($listing, $lastSuccess->portal_listing_id);
        $this->logResult($listing, $portalKey, $result);
        return $result;
    }

    /**
     * Bir ilanı kayıtlı tüm portallara yayınla.
     */
    public function syncToAll(Listing $listing): array
    {
        $results = [];
        foreach ($this->connectors as $key => $connector) {
            if (!$connector->isConfigured()) {
                continue;
            }
            $results[$key] = $this->syncTo($listing, $key);
        }
        return $results;
    }

    /**
     * PortalSyncLog'a yazılan tek nokta.
     */
    protected function logResult(Listing $listing, string $portalKey, array $result): void
    {
        try {
            PortalSyncLog::create([
                'listing_id'        => $listing->id,
                'portal'            => $portalKey,
                'action'            => $result['action'] ?? 'unknown',
                'status'            => $result['success'] ? 'success' : 'failed',
                'portal_listing_id' => $result['portal_listing_id'] ?? null,
                'portal_url'        => $result['portal_url'] ?? null,
                'request_data'      => $result['request_data'] ?? null,
                'response_data'     => $result['response_data'] ?? null,
                'error_message'     => $result['error_message'] ?? null,
                'synced_at'         => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('PortalSyncLog write failed', [
                'listing' => $listing->id,
                'portal'  => $portalKey,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
