<?php

namespace App\Jobs\AI;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\AI\Services\AIService;
use Modules\AI\Services\ContentService;
use Modules\RealEstate\Models\Listing;

class GenerateListingDescriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(
        public int $listingId,
        public array $languages = ['tr'],
        public string $style = 'professional',
        public ?int $userId = null,
    ) {
    }

    public function handle(AIService $ai, ContentService $content): void
    {
        $listing = Listing::find($this->listingId);
        if (!$listing) {
            Log::warning('GenerateListingDescriptionJob: listing not found', ['id' => $this->listingId]);
            return;
        }

        $ai->withContext($listing->office_id, $this->userId, 'listing.description');

        $descriptions = [];
        foreach ($this->languages as $lang) {
            $desc = $this->generateForLanguage($content, $listing, $lang, $this->style);
            if ($desc) {
                $descriptions[$lang] = $desc;
            }
        }

        if (empty($descriptions)) {
            Log::info('GenerateListingDescriptionJob: no descriptions generated', ['listing_id' => $listing->id]);
            return;
        }

        // Title is JSON multi-language; description currently single column. We persist multi-lang JSON
        // into ai_description for now, and overwrite the description column with TR by default.
        $listing->ai_description = $descriptions;
        if (!empty($descriptions['tr'])) {
            $listing->description = $descriptions['tr'];
        }
        $listing->save();
    }

    protected function generateForLanguage(ContentService $content, Listing $listing, string $lang, string $style): ?string
    {
        // ContentService::generateDescription supports a style param and writes in Turkish.
        // For other languages we delegate to a customized chat.
        if ($lang === 'tr') {
            return $content->generateDescription($listing, $style);
        }
        return $this->generateOtherLanguage($content, $listing, $lang, $style);
    }

    protected function generateOtherLanguage(ContentService $content, Listing $listing, string $lang, string $style): ?string
    {
        $langMap = [
            'en' => 'English',
            'ru' => 'Russian',
            'ar' => 'Arabic',
            'de' => 'German',
            'fr' => 'French',
        ];

        $target = $langMap[$lang] ?? 'English';

        $tr = $content->generateDescription($listing, $style);
        if (!$tr) return null;

        // Translate via the underlying AIService through ContentService->ai
        $ai = app(\Modules\AI\Services\AIService::class);
        $translated = $ai->chat([
            ['role' => 'system', 'content' => "You are a professional real estate copywriter. Translate the Turkish listing description into {$target}. Preserve all numeric facts, keep style {$style}, output the {$target} text only — no preamble."],
            ['role' => 'user', 'content' => $tr],
        ]);

        return $translated ?: null;
    }
}
