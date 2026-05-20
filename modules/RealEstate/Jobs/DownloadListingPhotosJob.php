<?php

namespace Modules\RealEstate\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\RealEstate\Models\Listing;

/**
 * Portal import'unda yakalanan fotoğraf URL'lerini indirir, Spatie
 * MediaLibrary'nin "photos" collection'ına ekler.
 *
 * Asenkron — kullanıcı ilan kaydetti, hemen UI'ya döndü, fotoğraflar
 * arka planda iniyor.
 */
class DownloadListingPhotosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 2;

    public function __construct(
        public int $listingId,
        public array $photoUrls,
    ) {}

    public function handle(): void
    {
        $listing = Listing::find($this->listingId);
        if (!$listing) return;

        $imported = 0;
        foreach (array_slice($this->photoUrls, 0, 25) as $url) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; RE-OS bot)'])
                    ->get($url);

                if (!$response->successful()) continue;

                $contentType = $response->header('Content-Type', '');
                if (!str_starts_with($contentType, 'image/')) continue;

                $extension = match (true) {
                    str_contains($contentType, 'jpeg') => 'jpg',
                    str_contains($contentType, 'png')  => 'png',
                    str_contains($contentType, 'webp') => 'webp',
                    default                            => 'jpg',
                };

                $tmpPath = tempnam(sys_get_temp_dir(), 'listing_photo_') . '.' . $extension;
                file_put_contents($tmpPath, $response->body());

                $listing->addMedia($tmpPath)
                    ->toMediaCollection('photos');

                $imported++;
            } catch (\Throwable $e) {
                Log::warning('DownloadListingPhotosJob: photo failed', [
                    'listing' => $this->listingId,
                    'url'     => $url,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        Log::info("Listing #{$this->listingId} import — {$imported} foto indirildi.");
    }
}
