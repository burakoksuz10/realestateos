<?php

namespace Modules\RealEstate\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\AI\Services\AIService;
use Modules\RealEstate\Models\Listing;

/**
 * Emlak portallarındaki ilan URL'inden veri çekip RE-OS'a ilan oluşturur.
 *
 * Akış:
 *   1. URL'den host belirle (sahibinden / hepsiemlak / emlakjet)
 *   2. HTML'i scrape et (User-Agent ile)
 *   3. AI'ya gönder, structured JSON iste
 *   4. Listing fillable'larına eşle, draft olarak döndür
 *   5. Fotoğraf URL'leri ayrı liste — sonra job ile indirilir
 *
 * Tek public metot: `preview(string $url)` ve `import(string $url, ?int $officeId, ?int $agentId)`
 */
class ListingImportService
{
    /** Desteklenen portallar */
    const SUPPORTED_HOSTS = [
        'sahibinden.com'   => 'sahibinden',
        'www.sahibinden.com' => 'sahibinden',
        'hepsiemlak.com'   => 'hepsiemlak',
        'www.hepsiemlak.com' => 'hepsiemlak',
        'emlakjet.com'     => 'emlakjet',
        'www.emlakjet.com' => 'emlakjet',
    ];

    public function __construct(protected AIService $ai) {}

    /**
     * URL'den ilan verisi çıkar, kaydetmeden döndür (önizleme).
     */
    public function preview(string $url): array
    {
        $host = $this->detectHost($url);
        if (!$host) {
            return [
                'success' => false,
                'message' => 'Desteklenmeyen URL. Sahibinden, Hepsiemlak veya EmlakJet linki gerek.',
            ];
        }

        $html = $this->fetchPage($url);
        if (!$html) {
            return [
                'success' => false,
                'message' => 'Sayfa indirilemedi. Link doğru mu? Bazı portallar bot trafiğini engelleyebilir.',
            ];
        }

        $data = $this->parseWithAI($html, $host, $url);
        if (!$data) {
            return [
                'success' => false,
                'message' => 'AI sayfayı çözümleyemedi. Manuel girişe geç ya da farklı bir URL dene.',
            ];
        }

        return [
            'success' => true,
            'source'  => $host,
            'url'     => $url,
            'data'    => $data,
        ];
    }

    /**
     * Önizleme verisini Listing olarak kaydet (draft).
     */
    public function import(array $data, ?int $officeId = null, ?int $agentId = null): Listing
    {
        $payload = array_intersect_key($data, array_flip([
            'title', 'description', 'type', 'category', 'listing_type',
            'price', 'price_currency',
            'gross_sqm', 'net_sqm', 'land_sqm',
            'room_count', 'living_room_count', 'bathroom_count',
            'floor_number', 'total_floors', 'building_age',
            'heating_type', 'fuel_type', 'facade',
            'is_furnished', 'furniture_status',
            'features', 'features_text', 'amenities',
            'city', 'district', 'neighborhood', 'address',
            'latitude', 'longitude',
            'deed_status', 'is_in_site', 'site_name', 'dues_amount',
        ]));

        // Defaults
        $payload['status'] = 'draft';
        $payload['office_id'] = $officeId ?? auth()->user()?->office_id;
        $payload['agent_id'] = $agentId ?? auth()->id();
        $payload['reference_no'] = $payload['reference_no'] ?? 'IMP-' . strtoupper(uniqid());
        $payload['ai_description'] = $data['description'] ?? null;

        // Boolean cast'leri normalize et
        if (isset($payload['is_furnished'])) {
            $payload['is_furnished'] = (bool) $payload['is_furnished'];
        }
        if (isset($payload['is_in_site'])) {
            $payload['is_in_site'] = (bool) $payload['is_in_site'];
        }

        $listing = Listing::create($payload);

        // Fotoğrafları async indir
        $photoUrls = $data['photos'] ?? [];
        if (!empty($photoUrls) && is_array($photoUrls)) {
            \Modules\RealEstate\Jobs\DownloadListingPhotosJob::dispatch($listing->id, $photoUrls);
        }

        return $listing;
    }

    protected function detectHost(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);
        return self::SUPPORTED_HOSTS[$host] ?? null;
    }

    protected function fetchPage(string $url): ?string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'tr-TR,tr;q=0.9,en;q=0.8',
            ])
                ->timeout(20)
                ->withOptions(['allow_redirects' => true])
                ->get($url);

            if (!$response->successful()) {
                Log::warning('ListingImport: HTTP fail', ['status' => $response->status(), 'url' => $url]);
                return null;
            }

            return $response->body();
        } catch (\Throwable $e) {
            Log::error('ListingImport: fetch exception', ['error' => $e->getMessage(), 'url' => $url]);
            return null;
        }
    }

    /**
     * AI ile structured parse.
     */
    protected function parseWithAI(string $html, string $host, string $url): ?array
    {
        // HTML'i kısalt — gereksiz script/style at, ama foto URL'lerini koru
        $cleaned = $this->cleanHtml($html);
        // 30K char ile sınırla (token bütçesi için)
        $cleaned = mb_substr($cleaned, 0, 30000);

        $this->ai->withContext(auth()->user()?->office_id, auth()->id(), 'listing.import');

        $messages = [
            [
                'role' => 'system',
                'content' => "Sen bir Türkçe emlak ilan parsing uzmanısın. {$host} portalından alınan HTML/metin içeriğinden yapılandırılmış emlak verisi çıkarırsın. "
                    . "Yalnız sayfadaki bilgileri kullan, uydurma. Yoksa null bırak. "
                    . "JSON döndür: { "
                    . "\"title\": string, "
                    . "\"description\": string, "
                    . "\"type\": \"apartment|house|villa|office|shop|land|other\", "
                    . "\"category\": \"residential|commercial|land\", "
                    . "\"listing_type\": \"sale|rent|daily_rent\", "
                    . "\"price\": number (TL), "
                    . "\"price_currency\": \"TRY\", "
                    . "\"gross_sqm\": number, "
                    . "\"net_sqm\": number, "
                    . "\"room_count\": number, "
                    . "\"living_room_count\": number, "
                    . "\"bathroom_count\": number, "
                    . "\"floor_number\": number, "
                    . "\"total_floors\": number, "
                    . "\"building_age\": number, "
                    . "\"heating_type\": string, "
                    . "\"facade\": string, "
                    . "\"is_furnished\": boolean, "
                    . "\"furniture_status\": string, "
                    . "\"features\": [string, ...], "
                    . "\"city\": string, "
                    . "\"district\": string, "
                    . "\"neighborhood\": string, "
                    . "\"address\": string, "
                    . "\"deed_status\": string, "
                    . "\"is_in_site\": boolean, "
                    . "\"site_name\": string, "
                    . "\"dues_amount\": number, "
                    . "\"photos\": [string, ...] (mutlak URL'ler) "
                    . "}",
            ],
            [
                'role' => 'user',
                'content' => "Kaynak: {$url}\n\n" . $cleaned,
            ],
        ];

        $result = $this->ai->chatJson($messages, [
            'temperature' => 0.1,
            'max_tokens'  => 2000,
        ]);

        if (!$result) return null;

        // Foto URL'lerini meta-tag fallback ile tamamla
        if (empty($result['photos']) || !is_array($result['photos'])) {
            $result['photos'] = $this->extractPhotosFromHtml($html);
        }

        return $result;
    }

    /**
     * HTML'den script/style/svg vb. gereksizleri at.
     */
    protected function cleanHtml(string $html): string
    {
        // Önce script/style/noscript blokları temizle
        $patterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i',
            '/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/i',
            '/<noscript\b[^<]*(?:(?!<\/noscript>)<[^<]*)*<\/noscript>/i',
            '/<svg\b[^<]*(?:(?!<\/svg>)<[^<]*)*<\/svg>/i',
            '/<!--.*?-->/s',
        ];
        $cleaned = preg_replace($patterns, '', $html);

        // Birden fazla boşluğu tek boşluğa indir
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        return trim($cleaned);
    }

    /**
     * AI'nın yakalayamadığı durumda meta tag + img src'lerinden foto URL'leri çıkar.
     */
    protected function extractPhotosFromHtml(string $html): array
    {
        $urls = [];

        // og:image
        if (preg_match_all('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
            $urls = array_merge($urls, $m[1]);
        }

        // image src'leri (data-src dahil — lazy load için)
        if (preg_match_all('/<img[^>]+(?:src|data-src|data-lazy-src)=["\']([^"\']+\.(?:jpg|jpeg|png|webp)[^"\']*)["\']/i', $html, $m)) {
            foreach ($m[1] as $u) {
                if (str_starts_with($u, 'http')) $urls[] = $u;
            }
        }

        return array_values(array_unique($urls));
    }
}
