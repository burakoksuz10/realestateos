<?php

namespace Modules\AI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RealEstate\Models\Listing;

class TranslationController extends Controller
{
    public function translate(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'from' => 'required|string|size:2',
            'to' => 'required|string|size:2',
        ]);

        // Placeholder - would use AI translation in production
        $translated = $validated['text'];

        return response()->json(['translated' => $translated]);
    }

    public function translateListing(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'to' => 'required|string|size:2',
        ]);

        // Placeholder
        return response()->json([
            'title' => $listing->title,
            'description' => $listing->description,
        ]);
    }

    public function detectLanguage(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string',
        ]);

        // Placeholder
        return response()->json(['language' => 'tr']);
    }
}
