<?php

namespace Modules\AI\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\AI\ValuateListingJob;
use Illuminate\Http\Request;
use Modules\AI\Services\AIService;
use Modules\AI\Services\ValuationService;
use Modules\RealEstate\Models\Listing;

class ValuationController extends Controller
{
    public function __construct(
        protected ValuationService $valuation,
        protected AIService $ai,
    ) {
    }

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
        $async = $request->boolean('async');

        if ($async) {
            ValuateListingJob::dispatch($listing->id, auth()->id());
            return response()->json([
                'queued' => true,
                'message' => 'Değerleme analizi kuyruğa alındı.',
            ]);
        }

        $this->ai->withContext($listing->office_id, auth()->id(), 'listing.valuation');
        $result = $this->valuation->valuate($listing);

        $listing->ai_valuation = $result;
        $listing->save();

        return response()->json($result);
    }

    public function downloadReport(Listing $listing)
    {
        $valuation = $listing->ai_valuation;
        if (!$valuation) {
            return back()->with('warning', 'Önce bu ilan için AI değerleme oluşturun.');
        }
        // PDF report scaffold — Phase 6 will wire dompdf properly.
        return response()->json($valuation)->header('Content-Disposition', 'inline; filename="valuation-' . $listing->reference_no . '.json"');
    }
}
