<?php

namespace Modules\SocialMedia\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\SocialMedia\Models\SocialPost;

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

        return view('socialmedia::index', compact('posts', 'stats'));
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

    private function callOpenAI(string $systemPrompt, string $userMessage): string
    {
        $apiKey = config('services.openai.api_key');

        if (!$apiKey) {
            return 'AI özelliği için OpenAI API anahtarı gereklidir. Lütfen .env dosyasına OPENAI_API_KEY ekleyin.';
        }

        try {
            $client = \OpenAI::client($apiKey);
            $response = $client->chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'max_tokens' => 1000,
            ]);
            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            return 'AI yanıtı alınamadı: ' . $e->getMessage();
        }
    }
}
