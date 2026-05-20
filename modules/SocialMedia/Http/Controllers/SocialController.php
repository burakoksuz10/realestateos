<?php

namespace Modules\SocialMedia\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AI\Services\ContentService;
use Modules\AI\Services\FalAiService;
use Modules\RealEstate\Models\Listing;
use Modules\SocialMedia\Models\SocialPost;
use Modules\SocialMedia\Services\SocialCardService;

class SocialController extends Controller
{
    public function index(Request $request)
    {
        $posts = SocialPost::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('status');

        $stats = [
            'total' => SocialPost::where('user_id', $request->user()->id)->count(),
            'published' => SocialPost::where('user_id', $request->user()->id)->where('status', 'yayinlandi')->count(),
            'scheduled' => SocialPost::where('user_id', $request->user()->id)->where('status', 'planlandi')->count(),
            'draft' => SocialPost::where('user_id', $request->user()->id)->where('status', 'draft')->count(),
        ];

        // İlandan içerik / kart akışı için kısa listing seçici
        $listings = $this->userListings($request);

        return view('socialmedia::index', compact('posts', 'stats', 'listings'));
    }

    /**
     * Aylık takvim görünümü — planlanmış post'lar tarih grid'inde.
     */
    public function calendar(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $start = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $posts = SocialPost::where('user_id', $request->user()->id)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('scheduled_at', [$start, $end])
                    ->orWhereBetween('published_at', [$start, $end]);
            })
            ->orderBy('scheduled_at')
            ->get();

        return view('socialmedia::calendar', [
            'posts' => $posts,
            'month' => $month,
            'start' => $start,
            'end' => $end,
        ]);
    }

    protected function userListings(Request $request)
    {
        $officeId = $request->user()->office_id ?? null;
        $query = Listing::query()->latest('id')->limit(100);
        if ($officeId) {
            $query->where('office_id', $officeId);
        }
        return $query->get(['id', 'reference_no', 'title', 'city', 'district', 'price', 'price_currency'])
            ->map(fn ($l) => [
                'id' => $l->id,
                'label' => ($l->reference_no ? "[{$l->reference_no}] " : '') . ($l->title ?? 'İlan #' . $l->id),
                'location' => trim(($l->district ?? '') . ', ' . ($l->city ?? ''), ', '),
            ])
            ->values();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|in:instagram,facebook,both',
            'content_type' => 'required|in:post,story,reel',
            'caption' => 'nullable|string|max:2200',
            'media_url' => 'nullable|string',
            'status' => 'required|in:draft,planlandi',
            'scheduled_at' => 'nullable|date|required_if:status,planlandi',
        ]);

        $validated['user_id'] = $request->user()->id;
        $post = SocialPost::create($validated);

        return response()->json(['success' => true, 'post' => $post]);
    }

    public function update(Request $request, SocialPost $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'platform' => 'required|in:instagram,facebook,both',
            'content_type' => 'required|in:post,story,reel',
            'caption' => 'nullable|string|max:2200',
            'media_url' => 'nullable|string',
            'status' => 'required|in:draft,planlandi',
            'scheduled_at' => 'nullable|date',
        ]);

        $post->update($validated);

        return response()->json(['success' => true, 'post' => $post]);
    }

    public function destroy(SocialPost $post)
    {
        $this->authorize('delete', $post);
        $post->delete();
        return response()->json(['success' => true]);
    }

    public function publish(Request $request, SocialPost $post)
    {
        $this->authorize('update', $post);
        $post->update(['status' => 'yayinlandi', 'published_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Gönderi yayınlandı.']);
    }

    public function generateCaption(Request $request)
    {
        $request->validate([
            'platform' => 'required|string',
            'content_type' => 'required|string',
            'topic' => 'nullable|string',
            'tone' => 'nullable|string',
        ]);

        $platform = $request->input('platform', 'instagram');
        $topic = $request->input('topic', 'gayrimenkul');
        $tone = $request->input('tone', 'profesyonel');

        $caption = $this->callOpenAI(
            "Sen bir Türk gayrimenkul şirketinin sosyal medya uzmanısın. {$platform} için {$tone} tonda {$topic} hakkında Türkçe bir gönderi altyazısı yaz. Emoji kullan, hashtag ekle. Max 300 kelime.",
            'Kısa ve etkili bir sosyal medya altyazısı yaz.'
        );

        return response()->json(['success' => true, 'caption' => $caption]);
    }

    public function generatePlan(Request $request)
    {
        $request->validate([
            'month' => 'nullable|string',
            'platforms' => 'nullable|array',
            'tone' => 'nullable|string',
        ]);

        $month = $request->input('month', now()->format('F Y'));
        $tone = $request->input('tone', 'profesyonel');

        $plan = $this->callOpenAI(
            "Sen bir gayrimenkul şirketinin sosyal medya stratejistlerinsin. {$month} ayı için aylık sosyal medya içerik planı oluştur. {$tone} ton kullan. JSON formatında döndür: [{\"hafta\": 1, \"icerikler\": [{\"gun\": \"Pazartesi\", \"platform\": \"instagram\", \"tur\": \"post\", \"konu\": \"...\", \"caption\": \"...\"}]}]",
            'Aylık içerik planı oluştur.'
        );

        return response()->json(['success' => true, 'plan' => $plan]);
    }

    public function generateImage(Request $request)
    {
        $request->validate(['prompt' => 'required|string']);

        return response()->json([
            'success' => true,
            'image_url' => null,
            'message' => 'Görsel oluşturma özelliği için OpenAI DALL-E API anahtarı gereklidir.',
        ]);
    }

    /**
     * Markalı sosyal kart üret — ilandan.
     */
    public function generateCard(Request $request, SocialCardService $cards)
    {
        $data = $request->validate([
            'listing_id' => 'required|integer|exists:listings,id',
            'template' => 'required|in:' . implode(',', SocialCardService::availableTemplates()),
            'size' => 'required|in:' . implode(',', SocialCardService::availableSizes()),
        ]);

        $listing = Listing::findOrFail($data['listing_id']);

        $result = $cards->generate($listing, $data['template'], $data['size']);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * İlandan multiplatform içerik üret — ContentService kullanır.
     */
    public function generateFromListing(Request $request, ContentService $content)
    {
        $data = $request->validate([
            'listing_id' => 'required|integer|exists:listings,id',
            'kind' => 'required|in:social,reels',
        ]);

        $listing = Listing::findOrFail($data['listing_id']);

        try {
            $payload = $data['kind'] === 'reels'
                ? $content->generateReelsScript($listing)
                : $content->generateSocialContent($listing);

            return response()->json(['success' => true, 'data' => $payload]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Hashtag önerileri — bölge + ilan tipi bazlı.
     */
    public function suggestHashtags(Request $request)
    {
        $data = $request->validate([
            'listing_id' => 'nullable|integer|exists:listings,id',
            'city' => 'nullable|string|max:120',
            'district' => 'nullable|string|max:120',
            'type' => 'nullable|string|max:60',
            'count' => 'nullable|integer|min:5|max:40',
        ]);

        $city = $data['city'] ?? null;
        $district = $data['district'] ?? null;
        $type = $data['type'] ?? null;

        if (!empty($data['listing_id'])) {
            $listing = Listing::find($data['listing_id']);
            if ($listing) {
                $city = $city ?: $listing->city;
                $district = $district ?: $listing->district;
                $type = $type ?: ($listing->listing_type . '-' . $listing->type);
            }
        }

        $count = $data['count'] ?? 20;
        $prompt = "Türk gayrimenkul piyasası için Instagram ve TikTok hashtag önerileri üret. "
            . "Bölge: " . ($district ?? 'belirsiz') . ", " . ($city ?? 'belirsiz') . ". "
            . "İlan tipi: " . ($type ?? 'belirsiz') . ". "
            . "Tam {$count} hashtag, JSON dizi olarak {\"hashtags\":[\"#emlak\", ...]}. "
            . "Karışım: bölgesel, ilan tipi, genel emlak, yaşam tarzı, trend.";

        $raw = $this->callOpenAI(
            'Sen Türkiye emlak sektörü için sosyal medya hashtag uzmanısın. Cevap her zaman JSON.',
            $prompt,
            true
        );

        // JSON parse dene
        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        $tags = is_array($decoded['hashtags'] ?? null) ? $decoded['hashtags'] : [];

        // Fallback: tüm #word parse et
        if (!$tags && is_string($raw)) {
            preg_match_all('/#[\p{L}0-9_]+/u', $raw, $matches);
            $tags = array_values(array_unique($matches[0] ?? []));
        }

        return response()->json(['success' => true, 'hashtags' => $tags]);
    }

    /**
     * Fal.ai görsel iyileştirme — sky, twilight, declutter, staging, enhance.
     */
    public function enhanceImage(Request $request, FalAiService $fal)
    {
        $data = $request->validate([
            'image_url' => 'required|url',
            'operation' => 'required|in:sky_replacement,twilight,declutter,virtual_staging,enhance',
            'style_prompt' => 'nullable|string|max:500',
        ]);

        if (!$fal->isEnabled()) {
            return response()->json([
                'success' => false,
                'error' => 'Fal.ai entegrasyonu yapılandırılmamış. .env\'e FAL_API_KEY ekleyin.',
            ], 422);
        }

        $fal->withContext(
            $request->user()->office_id ?? null,
            $request->user()->id
        );

        $result = match ($data['operation']) {
            'sky_replacement' => $fal->skyReplacement($data['image_url']),
            'twilight' => $fal->twilight($data['image_url']),
            'declutter' => $fal->declutter($data['image_url']),
            'virtual_staging' => $fal->virtualStaging(
                $data['image_url'],
                $data['style_prompt'] ?? 'modern Scandinavian living room'
            ),
            'enhance' => $fal->enhance($data['image_url']),
        };

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    private function callOpenAI(string $systemPrompt, string $userMessage, bool $jsonMode = false): string
    {
        $apiKey = config('services.openai.api_key');

        if (!$apiKey) {
            return 'AI özelliği için OpenAI API anahtarı gereklidir. Lütfen .env dosyasına OPENAI_API_KEY ekleyin.';
        }

        try {
            $client = \OpenAI::client($apiKey);
            $params = [
                'model' => config('reos.ai.mini_model', 'gpt-4o-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'max_tokens' => 1000,
            ];
            if ($jsonMode) {
                $params['response_format'] = ['type' => 'json_object'];
            }
            $response = $client->chat()->create($params);
            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            return 'AI yanıtı alınamadı: ' . $e->getMessage();
        }
    }
}
