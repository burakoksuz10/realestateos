<?php

namespace Modules\RealEstate\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Modules\AI\Services\ContentService;
use Modules\RealEstate\Models\Listing;

/**
 * AI destekli ilan broşürü üretici.
 *
 * Toplar:
 * - İlan verisi + ofis markası + danışman bilgisi
 * - Fotoğraflar (Spatie MediaLibrary'den)
 * - AI iyileştirilmiş açıklama (yoksa üretir)
 * - Statik harita URL'i (lat/long varsa)
 *
 * dompdf ile A4 PDF döndürür.
 */
class BrochureService
{
    public function __construct(protected ContentService $content) {}

    /**
     * Bir ilan için PDF üret.
     *
     * @param  bool  $regenerateDescription AI açıklamasını ezerek tekrar üret
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generate(Listing $listing, bool $regenerateDescription = false): \Barryvdh\DomPDF\PDF
    {
        $listing->loadMissing(['office', 'agent']);

        // AI açıklama yoksa veya zorla yenileme istendiyse üret
        if ($regenerateDescription || empty($listing->ai_description)) {
            try {
                $aiDescription = $this->content->generateDescription($listing, 'professional');
                if (!empty($aiDescription) && is_string($aiDescription)) {
                    $listing->update(['ai_description' => $aiDescription]);
                }
            } catch (\Throwable $e) {
                // AI patlarsa orijinal description'la devam
            }
        }

        $photos = $this->collectPhotos($listing, max: 6);
        $office = $listing->office;
        $agent = $listing->agent;

        $data = [
            'listing'  => $listing,
            'office'   => $office,
            'agent'    => $agent,
            'photos'   => $photos,
            'mapUrl'   => $this->buildStaticMapUrl($listing),
            'features' => $this->collectFeatures($listing),
            'priceFormatted' => $this->formatPrice($listing),
            'description' => $listing->ai_description ?: $listing->description,
            'generatedAt' => now(),
        ];

        return Pdf::loadView('realestate::listings.brochure', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont'          => 'DejaVu Sans',
            ]);
    }

    /**
     * Spatie MediaLibrary'den fotoğraf URL'lerini topla.
     */
    protected function collectPhotos(Listing $listing, int $max = 6): array
    {
        if (!method_exists($listing, 'getMedia')) {
            return [];
        }

        $media = $listing->getMedia('photos');
        if ($media->isEmpty()) {
            // Default media collection fallback
            $media = $listing->getMedia();
        }

        return $media
            ->take($max)
            ->map(function ($m) {
                // dompdf için absolut filesystem path kullan (image embed)
                $path = $m->getPath();
                if (file_exists($path)) {
                    return [
                        'path' => $path,
                        'url'  => $m->getUrl(),
                    ];
                }
                return ['path' => null, 'url' => $m->getUrl()];
            })
            ->filter(fn ($p) => $p['path'] || $p['url'])
            ->values()
            ->all();
    }

    /**
     * features kolonu çeşitli formatlarda olabilir (array, JSON string).
     */
    protected function collectFeatures(Listing $listing): array
    {
        $raw = $listing->features ?? [];
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($raw)) return [];

        // Boolean/array map'leri düz array'e indir
        $out = [];
        foreach ($raw as $k => $v) {
            if (is_string($v)) {
                $out[] = $v;
            } elseif (is_bool($v) && $v) {
                $out[] = is_string($k) ? $k : (string) $k;
            } elseif (is_array($v)) {
                foreach ($v as $vv) if (is_string($vv)) $out[] = $vv;
            }
        }
        return array_slice(array_unique($out), 0, 24);
    }

    protected function formatPrice(Listing $listing): string
    {
        $currency = $listing->price_currency ?? 'TRY';
        $symbol = match ($currency) {
            'TRY', 'TL' => '₺',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => $currency . ' ',
        };
        $price = (float) ($listing->price ?? 0);
        return $symbol . number_format($price, 0, ',', '.');
    }

    protected function buildStaticMapUrl(Listing $listing): ?string
    {
        $lat = $listing->latitude;
        $lng = $listing->longitude;
        if (!$lat || !$lng) return null;

        $key = config('services.google.maps_api_key') ?: config('services.googlemaps.key') ?: null;
        if (!$key) {
            // Anahtar yoksa OpenStreetMap statik harita servisi
            return "https://staticmap.openstreetmap.de/staticmap.php?center={$lat},{$lng}&zoom=15&size=600x300&markers={$lat},{$lng},red-pushpin";
        }

        return "https://maps.googleapis.com/maps/api/staticmap"
            . "?center={$lat},{$lng}"
            . "&zoom=15&size=600x300&scale=2"
            . "&markers=color:red%7C{$lat},{$lng}"
            . "&key={$key}";
    }
}
