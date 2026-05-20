<?php

namespace Modules\RealEstate\Services\Portals;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\RealEstate\Models\Listing;

/**
 * Portal connector'ları için ortak iş mantığı.
 *
 * Concrete sınıflar yalnız `transformListing()` (kendi payload formatı)
 * ve `endpoint()` (URL'ler) + `headers()` (auth) override eder.
 *
 * `isConfigured()` false dönerse `publish/update/delete` çağrıları
 * 'unconfigured' status'üyle log'lanır, gerçek HTTP gitmez (dev mode).
 */
abstract class AbstractPortalConnector implements PortalConnectorInterface
{
    protected string $portalKey;

    /**
     * Portal-specific data layout.
     *
     * @return array{title:string,description:string,price:int,...}
     */
    abstract protected function transformListing(Listing $listing): array;

    /**
     * Çağrılan endpoint URL'i (action: publish/update/delete/fetch_stats).
     */
    abstract protected function endpoint(string $action, ?string $portalListingId = null): string;

    /**
     * Yetkilendirme header'ları.
     */
    abstract protected function headers(): array;

    public function publish(Listing $listing): array
    {
        if (!$this->isConfigured()) {
            return $this->unconfigured('publish');
        }

        try {
            $payload = $this->transformListing($listing);
            $url = $this->endpoint('publish');

            $response = Http::withHeaders($this->headers())
                ->timeout(30)
                ->post($url, $payload);

            return $this->parseResponse($response, 'publish', $payload);
        } catch (\Throwable $e) {
            return $this->errorResult('publish', $e);
        }
    }

    public function update(Listing $listing, string $portalListingId): array
    {
        if (!$this->isConfigured()) {
            return $this->unconfigured('update');
        }

        try {
            $payload = $this->transformListing($listing);
            $url = $this->endpoint('update', $portalListingId);

            $response = Http::withHeaders($this->headers())
                ->timeout(30)
                ->put($url, $payload);

            return $this->parseResponse($response, 'update', $payload, $portalListingId);
        } catch (\Throwable $e) {
            return $this->errorResult('update', $e);
        }
    }

    public function delete(Listing $listing, string $portalListingId): array
    {
        if (!$this->isConfigured()) {
            return $this->unconfigured('delete');
        }

        try {
            $url = $this->endpoint('delete', $portalListingId);

            $response = Http::withHeaders($this->headers())
                ->timeout(30)
                ->delete($url);

            return $this->parseResponse($response, 'delete', [], $portalListingId);
        } catch (\Throwable $e) {
            return $this->errorResult('delete', $e);
        }
    }

    public function fetchStats(Listing $listing, string $portalListingId): array
    {
        if (!$this->isConfigured()) {
            return $this->unconfigured('fetch_stats');
        }

        try {
            $url = $this->endpoint('fetch_stats', $portalListingId);

            $response = Http::withHeaders($this->headers())
                ->timeout(20)
                ->get($url);

            return $this->parseResponse($response, 'fetch_stats', [], $portalListingId);
        } catch (\Throwable $e) {
            return $this->errorResult('fetch_stats', $e);
        }
    }

    protected function parseResponse(
        \Illuminate\Http\Client\Response $response,
        string $action,
        array $payload = [],
        ?string $portalListingId = null
    ): array {
        $data = $response->json() ?? [];

        if ($response->successful()) {
            return [
                'success' => true,
                'action'  => $action,
                'message' => $data['message'] ?? 'OK',
                'portal_listing_id' => $data['id'] ?? $data['listing_id'] ?? $portalListingId,
                'portal_url' => $data['url'] ?? $data['public_url'] ?? null,
                'request_data' => $payload,
                'response_data' => $data,
            ];
        }

        Log::warning("Portal {$this->portalKey} {$action} failed", [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return [
            'success' => false,
            'action'  => $action,
            'message' => $data['error'] ?? $data['message'] ?? "HTTP {$response->status()}",
            'portal_listing_id' => $portalListingId,
            'portal_url' => null,
            'request_data' => $payload,
            'response_data' => $data,
            'error_message' => 'HTTP ' . $response->status() . ': ' . substr($response->body(), 0, 500),
        ];
    }

    protected function errorResult(string $action, \Throwable $e): array
    {
        Log::error("Portal {$this->portalKey} {$action} exception", [
            'error' => $e->getMessage(),
        ]);

        return [
            'success' => false,
            'action'  => $action,
            'message' => $e->getMessage(),
            'error_message' => $e->getMessage(),
        ];
    }

    protected function unconfigured(string $action): array
    {
        return [
            'success' => false,
            'action'  => $action,
            'message' => "{$this->name()} portalı için API anahtarı yapılandırılmamış.",
            'error_message' => 'unconfigured',
        ];
    }
}
