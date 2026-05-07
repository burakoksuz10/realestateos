<?php

namespace Modules\AI\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Lead;
use Modules\RealEstate\Models\Listing;

class MatchingController extends Controller
{
    public function matchingListings(Request $request, Lead $lead)
    {
        $listings = Listing::where('status', 'active')
            ->when($lead->property_type, fn($q) => $q->where('category', $lead->property_type))
            ->when($lead->budget_max, fn($q) => $q->where('price', '<=', $lead->budget_max))
            ->when($lead->budget_min, fn($q) => $q->where('price', '>=', $lead->budget_min))
            ->with('media')
            ->take(10)
            ->get();

        return response()->json(['success' => true, 'data' => $listings]);
    }

    public function matchingLeads(Request $request, Listing $listing)
    {
        $leads = Lead::where('status', '!=', 'lost')
            ->where(function ($q) use ($listing) {
                $q->whereNull('budget_max')->orWhere('budget_max', '>=', $listing->price);
            })
            ->with('contact')
            ->take(10)
            ->get();

        return response()->json(['success' => true, 'data' => $leads]);
    }

    public function similarListings(Request $request, Listing $listing)
    {
        $similar = Listing::where('id', '!=', $listing->id)
            ->where('city', $listing->city)
            ->where('listing_type', $listing->listing_type)
            ->where('status', 'active')
            ->whereBetween('price', [$listing->price * 0.8, $listing->price * 1.2])
            ->with('media')
            ->take(6)
            ->get();

        return response()->json(['success' => true, 'data' => $similar]);
    }

    public function semanticSearch(Request $request)
    {
        // Stub — semantic search requires embeddings/vector DB
        return response()->json([
            'success' => true,
            'data'    => [],
            'note'    => 'Semantik arama aktif değil. OpenAI API anahtarını ekleyin.',
        ]);
    }
}
