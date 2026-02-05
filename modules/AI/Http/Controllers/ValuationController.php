<?php

namespace Modules\AI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RealEstate\Models\Listing;

class ValuationController extends Controller
{
    public function index()
    {
        $listings = Listing::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('ai::valuation.index', compact('listings'));
    }

    public function show(Listing $listing)
    {
        return view('ai::valuation.show', compact('listing'));
    }

    public function generate(Request $request, Listing $listing)
    {
        // AI valuation placeholder
        $valuation = [
            'estimated_value' => $listing->price * 1.05,
            'min_value' => $listing->price * 0.95,
            'max_value' => $listing->price * 1.15,
            'confidence' => 85,
            'factors' => [
                'location' => 'Konum değeri yüksek',
                'size' => 'Ortalama metrekare fiyatı',
                'condition' => 'İyi durumda',
            ],
        ];

        return response()->json($valuation);
    }

    public function downloadReport(Listing $listing)
    {
        return back()->with('info', 'Değerleme raporu özelliği yakında eklenecek.');
    }
}
