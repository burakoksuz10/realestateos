<?php

namespace App\Jobs\AI;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\AI\Services\AIService;
use Modules\AI\Services\ValuationService;
use Modules\RealEstate\Models\Listing;

class ValuateListingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(
        public int $listingId,
        public ?int $userId = null,
    ) {
    }

    public function handle(AIService $ai, ValuationService $valuation): void
    {
        $listing = Listing::find($this->listingId);
        if (!$listing) {
            Log::warning('ValuateListingJob: listing not found', ['id' => $this->listingId]);
            return;
        }

        $ai->withContext($listing->office_id, $this->userId, 'listing.valuation');

        $result = $valuation->valuate($listing);

        $listing->ai_valuation = $result;
        $listing->save();
    }
}
