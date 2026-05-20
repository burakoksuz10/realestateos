<?php

namespace Modules\AI\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\AI\GenerateListingDescriptionJob;
use Illuminate\Http\Request;
use Modules\AI\Services\AIService;
use Modules\AI\Services\ContentService;
use Modules\RealEstate\Models\Listing;

class ContentController extends Controller
{
    public function __construct(
        protected ContentService $content,
        protected AIService $ai,
    ) {
    }

    public function index()
    {
        $listings = Listing::orderBy('created_at', 'desc')->paginate(20);
        return view('ai::content.index', compact('listings'));
    }

    public function generateDescription(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'style' => 'nullable|in:professional,luxury,friendly,minimal',
            'languages' => 'nullable|array',
            'languages.*' => 'in:tr,en,ru,ar,de,fr',
            'async' => 'nullable|boolean',
        ]);

        $style = $validated['style'] ?? 'professional';
        $languages = $validated['languages'] ?? ['tr'];

        if ($validated['async'] ?? false) {
            GenerateListingDescriptionJob::dispatch(
                listingId: $listing->id,
                languages: $languages,
                style: $style,
                userId: auth()->id(),
            );
            return response()->json([
                'queued' => true,
                'message' => 'Açıklama üretimi kuyruğa alındı. Birkaç saniye içinde hazır olacak.',
            ]);
        }

        $this->ai->withContext($listing->office_id, auth()->id(), 'listing.description');

        $descriptions = [];
        foreach ($languages as $lang) {
            $descriptions[$lang] = $lang === 'tr'
                ? $this->content->generateDescription($listing, $style)
                : $this->translateFromTurkish($this->content->generateDescription($listing, $style), $lang);
        }

        return response()->json([
            'descriptions' => $descriptions,
            'description' => $descriptions['tr'] ?? reset($descriptions),
        ]);
    }

    public function generateSocial(Request $request, Listing $listing)
    {
        $this->ai->withContext($listing->office_id, auth()->id(), 'content.social');
        $social = $this->content->generateSocialContent($listing);
        return response()->json($social);
    }

    public function generateAds(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'variations' => 'nullable|integer|min:1|max:10',
        ]);
        $this->ai->withContext($listing->office_id, auth()->id(), 'content.ads');
        $ads = $this->content->generateAdCopy($listing, $validated['variations'] ?? 5);
        return response()->json($ads);
    }

    public function generateHeadlines(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'count' => 'nullable|integer|min:1|max:30',
        ]);
        $this->ai->withContext($listing->office_id, auth()->id(), 'content.headlines');
        $headlines = $this->content->generateHeadlines($listing, $validated['count'] ?? 10);
        return response()->json(['headlines' => $headlines]);
    }

    public function improveText(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:5000',
            'fix_grammar' => 'nullable|boolean',
            'make_engaging' => 'nullable|boolean',
            'add_keywords' => 'nullable|boolean',
            'add_details' => 'nullable|boolean',
        ]);

        $this->ai->withContext(auth()->user()?->office_id, auth()->id(), 'content.improve');

        $improved = $this->content->improveDescription($validated['text'], [
            'fix_grammar'   => $validated['fix_grammar'] ?? true,
            'make_engaging' => $validated['make_engaging'] ?? true,
            'add_keywords'  => $validated['add_keywords'] ?? false,
            'add_details'   => $validated['add_details'] ?? false,
        ]);

        return response()->json(['improved' => $improved]);
    }

    protected function translateFromTurkish(?string $tr, string $lang): ?string
    {
        if (!$tr) return null;
        $langMap = ['en' => 'English', 'ru' => 'Russian', 'ar' => 'Arabic', 'de' => 'German', 'fr' => 'French'];
        $target = $langMap[$lang] ?? 'English';
        return $this->ai->chat([
            ['role' => 'system', 'content' => "Translate the Turkish real estate listing description into {$target}. Keep style and facts. Output only the translation."],
            ['role' => 'user', 'content' => $tr],
        ]);
    }
}
