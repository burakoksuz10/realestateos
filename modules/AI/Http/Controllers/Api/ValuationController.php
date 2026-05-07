<?php

namespace Modules\AI\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RealEstate\Models\Listing;

class ValuationController extends Controller
{
    public function generate(Request $request, Listing $listing)
    {
        // Stub — wire to OpenAI when API key is configured
        $estimated = $listing->price ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'listing_id'   => $listing->id,
                'estimated'    => $estimated,
                'range_low'    => (int) ($estimated * 0.90),
                'range_high'   => (int) ($estimated * 1.10),
                'confidence'   => 'medium',
                'generated_at' => now()->toISOString(),
                'note'         => 'AI değerleme aktif değil. OpenAI API anahtarı ekleyin.',
            ],
        ]);
    }

    public function show(Listing $listing)
    {
        return response()->json([
            'success' => true,
            'data'    => ['listing_id' => $listing->id, 'valuation' => null],
        ]);
    }

    public function comparables(Listing $listing)
    {
        $comps = Listing::where('id', '!=', $listing->id)
            ->where('city', $listing->city)
            ->where('listing_type', $listing->listing_type)
            ->where('status', 'active')
            ->take(5)
            ->get(['id', 'title', 'price', 'net_sqm', 'city', 'district']);

        return response()->json(['success' => true, 'data' => $comps]);
    }

    public function trends(Listing $listing)
    {
        return response()->json([
            'success' => true,
            'data'    => ['city' => $listing->city, 'trends' => [], 'note' => 'Trend verisi henüz mevcut değil.'],
        ]);
    }
}
