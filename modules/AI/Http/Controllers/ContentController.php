<?php

namespace Modules\AI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RealEstate\Models\Listing;

class ContentController extends Controller
{
    public function index()
    {
        $listings = Listing::orderBy('created_at', 'desc')->paginate(20);
        return view('ai::content.index', compact('listings'));
    }

    public function generateDescription(Request $request, Listing $listing)
    {
        $description = "Bu muhteşem {$listing->type} {$listing->city}, {$listing->district} bölgesinde yer almaktadır. ";
        $description .= "{$listing->gross_sqm} m² brüt alana sahip olan mülk, ";
        $description .= "{$listing->room_count}+{$listing->living_room_count} oda düzenine sahiptir.";

        return response()->json(['description' => $description]);
    }

    public function generateSocial(Request $request, Listing $listing)
    {
        $social = [
            'instagram' => "🏠 {$listing->title}\n📍 {$listing->city}\n💰 " . number_format($listing->price, 0, ',', '.') . " TL\n\n#emlak #gayrimenkul #{$listing->city}",
            'facebook' => "{$listing->title} - {$listing->city}, {$listing->district}\n\nFiyat: " . number_format($listing->price, 0, ',', '.') . " TL",
            'twitter' => "🏠 {$listing->title} | {$listing->city} | " . number_format($listing->price, 0, ',', '.') . " TL #emlak",
        ];

        return response()->json($social);
    }

    public function generateAds(Request $request, Listing $listing)
    {
        $ads = [
            'headline' => "{$listing->city}'de {$listing->type} Fırsatı!",
            'description' => "{$listing->district} bölgesinde {$listing->gross_sqm} m² {$listing->type}. Hemen inceleyin!",
            'cta' => 'Detayları Gör',
        ];

        return response()->json($ads);
    }

    public function generateHeadlines(Request $request, Listing $listing)
    {
        $headlines = [
            "{$listing->city}'de Kaçırılmayacak Fırsat!",
            "{$listing->district}'de {$listing->type} Satılık",
            "{$listing->room_count}+{$listing->living_room_count} {$listing->type} - {$listing->city}",
        ];

        return response()->json(['headlines' => $headlines]);
    }

    public function improveText(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'style' => 'nullable|in:professional,casual,luxury',
        ]);

        // Placeholder - would use AI in production
        $improved = $validated['text'];

        return response()->json(['improved' => $improved]);
    }
}
