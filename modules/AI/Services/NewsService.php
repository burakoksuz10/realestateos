<?php

namespace Modules\AI\Services;

use App\Models\NewsArticle;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NewsService
{
    private const RSS_FEEDS = [
        'https://news.google.com/rss/search?q=emlak+gayrimenkul+Türkiye&hl=tr&gl=TR&ceid=TR:tr',
        'https://news.google.com/rss/search?q=konut+fiyatları+Türkiye&hl=tr&gl=TR&ceid=TR:tr',
        'https://news.google.com/rss/search?q=inşaat+sektörü+Türkiye&hl=tr&gl=TR&ceid=TR:tr',
        'https://news.google.com/rss/search?q=kira+artışı+Türkiye&hl=tr&gl=TR&ceid=TR:tr',
    ];

    // Turkish stop words to ignore when comparing titles
    private const STOP_WORDS = [
        've', 'ile', 'bu', 'bir', 'için', 'de', 'da', 'mi', 'mu', 'mı', 'mü',
        'ne', 'o', 'bu', 'şu', 'olan', 'olan', 'gibi', 'kadar', 'daha', 'en',
        'çok', 'az', 'her', 'ya', 'ya', 'ama', 'fakat', 'ancak', 'veya',
        'the', 'a', 'an', 'of', 'in', 'on', 'at', 'to', 'is', 'are',
    ];

    // Two titles are "same story" if their normalized similarity exceeds this
    private const SIMILARITY_THRESHOLD = 60;

    public function fetch(): array
    {
        $apiKey = Setting::get('openai_api_key');
        if (!$apiKey) {
            Log::warning('NewsService: OpenAI API key not configured.');
        }

        // Load existing titles from the last 72 hours for dedup comparison
        $recentTitles = NewsArticle::where('published_at', '>=', now()->subHours(72))
            ->pluck('title')
            ->toArray();

        $newCount  = 0;
        $skipCount = 0;
        // Titles seen in this run (across feeds), to deduplicate within the batch
        $seenThisRun = [];

        foreach (self::RSS_FEEDS as $feedUrl) {
            try {
                $items = $this->parseRss($feedUrl);

                foreach ($items as $item) {
                    // 1. Exact URL dedup
                    if (NewsArticle::where('url', $item['url'])->exists()) {
                        continue;
                    }

                    // 2. Title similarity dedup (same story from different sources)
                    if ($this->isTooSimilar($item['title'], $recentTitles) ||
                        $this->isTooSimilar($item['title'], $seenThisRun)) {
                        $skipCount++;
                        continue;
                    }

                    $enriched = $apiKey
                        ? $this->enrichWithAI($item, $apiKey)
                        : $item;

                    NewsArticle::create($enriched);

                    $recentTitles[]  = $item['title'];
                    $seenThisRun[]   = $item['title'];
                    $newCount++;
                }
            } catch (\Exception $e) {
                Log::error("NewsService: Failed to fetch feed {$feedUrl}: " . $e->getMessage());
            }
        }

        Log::info("NewsService: Fetched {$newCount} new, skipped {$skipCount} duplicates.");
        return ['new' => $newCount, 'skipped' => $skipCount];
    }

    /**
     * Returns true when $title is too similar to any title in $existing.
     * Uses word-level Jaccard similarity so Turkish multibyte chars are handled correctly.
     */
    private function isTooSimilar(string $title, array $existing): bool
    {
        foreach ($existing as $other) {
            if ($this->wordJaccard($title, $other) * 100 >= self::SIMILARITY_THRESHOLD) {
                return true;
            }
        }
        return false;
    }

    /**
     * Containment similarity: what fraction of the shorter title's words
     * appear in the longer title. Robust against verbose RSS titles.
     */
    private function wordJaccard(string $a, string $b): float
    {
        $wa = array_unique($this->titleWords($a));
        $wb = array_unique($this->titleWords($b));
        if (!$wa || !$wb) return 0.0;

        // Use the shorter set as the reference (containment check)
        if (count($wa) > count($wb)) {
            [$wa, $wb] = [$wb, $wa];
        }

        $covered = count(array_intersect($wa, $wb));
        return $covered / count($wa);
    }

    private function titleWords(string $title): array
    {
        // Strip only trailing " - Source Name" at the end
        $title = preg_replace('/\s[-–]\s[^-–]+$/', '', $title);
        $title = mb_strtolower($title, 'UTF-8');
        $title = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $title);
        $words = preg_split('/\s+/u', trim($title), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_filter(
            $words,
            fn($w) => mb_strlen($w, 'UTF-8') > 2 && !in_array($w, self::STOP_WORDS)
        ));
    }

    private function parseRss(string $url): array
    {
        $response = Http::timeout(15)->get($url);
        if (!$response->successful()) {
            throw new \Exception("HTTP {$response->status()} fetching RSS");
        }

        $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$xml) {
            throw new \Exception("Failed to parse RSS XML");
        }

        $items   = [];
        $channel = $xml->channel ?? $xml;

        foreach ($channel->item as $item) {
            $link    = (string) ($item->link ?? '');
            $source  = (string) ($item->source ?? parse_url($link, PHP_URL_HOST) ?? 'Bilinmiyor');
            $pubDate = (string) ($item->pubDate ?? '');

            $items[] = [
                'title'        => html_entity_decode(strip_tags((string) $item->title), ENT_QUOTES, 'UTF-8'),
                'url'          => $link,
                'summary'      => html_entity_decode(strip_tags((string) ($item->description ?? '')), ENT_QUOTES, 'UTF-8'),
                'source'       => $source,
                'source_url'   => $link,
                'image_url'    => null, // fetched separately via og:image
                'category'     => 'genel',
                'sentiment'    => 'neutral',
                'tags'         => [],
                'published_at' => $pubDate ? Carbon::parse($pubDate) : now(),
            ];
        }

        return array_slice($items, 0, 10);
    }

    private function enrichWithAI(array $article, string $apiKey): array
    {
        try {
            $model = Setting::get('openai_model', 'gpt-4o-mini');

            $payload = [
                'model'    => $model,
                'messages' => [
                    [
                        'role'    => 'system',
                        'content' => 'Sen bir Türk emlak sektörü uzmanısın. Verilen haber başlığı ve özetini analiz et ve JSON döndür. Alanlar: ai_summary (max 2 cümle Türkçe özet), category (piyasa|yatirim|konut|ticari|mevzuat|teknoloji|genel), sentiment (positive|negative|neutral), tags (max 5 Türkçe etiket dizisi). Sadece JSON döndür.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => "Başlık: {$article['title']}\nÖzet: {$article['summary']}",
                    ],
                ],
                'max_tokens'      => 300,
                'temperature'     => 0.3,
                'response_format' => ['type' => 'json_object'],
            ];

            $response = Http::withToken($apiKey)
                ->timeout(20)
                ->post('https://api.openai.com/v1/chat/completions', $payload);

            if ($response->successful()) {
                $data = json_decode($response->json('choices.0.message.content'), true);
                if ($data) {
                    $article['ai_summary'] = $data['ai_summary'] ?? null;
                    $article['category']   = $data['category']   ?? 'genel';
                    $article['sentiment']  = $data['sentiment']  ?? 'neutral';
                    $article['tags']       = $data['tags']        ?? [];
                }
            }
        } catch (\Exception $e) {
            Log::warning('NewsService AI enrichment failed: ' . $e->getMessage());
        }

        return $article;
    }

    private function extractImage(string $html): ?string
    {
        preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $html, $matches);
        return $matches[1] ?? null;
    }
}
