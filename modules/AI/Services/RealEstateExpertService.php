<?php

namespace Modules\AI\Services;

use Illuminate\Support\Facades\Cache;
use Modules\BI\Services\AnalyticsService;
use Modules\CRM\Models\Deal;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\PipelineStage;
use Modules\RealEstate\Models\Listing;

/**
 * AI Emlak Uzmanı — prensip tabanlı operasyonel karar destek.
 *
 * Guzllik'in ai-reklam-uzmani pattern'inden ilham aldı:
 * - Önce VERİYİ topla (snapshot)
 * - Sonra PRENSİP listesi ile birlikte GPT'ye gönder
 * - GPT prensiplere göre verideki sorunları işaretleyip aksiyon önersin
 *
 * Kullanıcı 4 sorunla karşı karşıya olabilir, AI her birinde
 * { kategori, ciddiyet, başlık, açıklama, öneri, etkilenen, örnekler } döner.
 */
class RealEstateExpertService
{
    /**
     * Yönetimsel prensipler — AI'ya verilen "uzman aklı".
     */
    const PRINCIPLES = [
        '60+ gün satılmayan ilan: fiyat veya görsel iletişim sorunu var, fiyat indirimi/yeni fotoğraf önerilmeli',
        '30+ gün takılmış deal: müşteri kararsızlığı veya iletişim kopukluğu işareti, agent müdahalesi gerekli',
        'Dönüşüm oranı %5 altındaki lead kaynakları: ya yanlış kitleyi çekiyor ya da takip zayıf, bütçe gözden geçirilmeli',
        'Yanıt süresi (created_at → first_response_at) 60dk üstü: lead kalitesi düşüyor, yanıt süresi disiplini şart',
        'Skoru 80+ olup 7+ gün dokunulmamış lead: yüksek riskli kayıp, agent dağıtımı gözden geçirilmeli',
        'Kalite skoru 40 altı aktif ilan: foto/açıklama/fiyat eksik, AI içerik & foto iyileştirme önerilmeli',
        'Bir agent\'ın yanıt süresi ekip ortalamasının 2 katı: koçluk veya iş yükü dengelemesi gerek',
    ];

    public function __construct(
        protected AIService $ai,
        protected AnalyticsService $analytics,
    ) {}

    /**
     * Tüm ofis için uzman analizi üret.
     */
    public function analyze(?int $officeId = null, bool $force = false): array
    {
        $cacheKey = 'ai.expert_analysis.' . ($officeId ?? 'all') . '.' . now()->format('Y-m-d-H');
        if (!$force) {
            $cached = Cache::get($cacheKey);
            if ($cached) return $cached;
        }

        $snapshot = $this->buildSnapshot($officeId);

        $this->ai->withContext($officeId, auth()->id(), 'copilot.expert');

        $messages = [
            [
                'role' => 'system',
                'content' => "Sen RE-OS platformunda çalışan kıdemli bir emlak operasyon uzmanısın. "
                    . "Bir emlak ofisinin verilerini analiz edip OPERASYONEL sorunları tespit edersin. "
                    . "Aşağıdaki prensipleri kullanarak veriyi yorumla, somut ve sayısal sorunlara işaret et. "
                    . "Genelleme yapma, veriden gelen rakamları kullan. Türkçe yaz. "
                    . "JSON döndür: { \"insights\": [{ "
                    . "\"category\": \"listing|lead|deal|agent|source\", "
                    . "\"severity\": \"critical|warning|info\", "
                    . "\"title\": string (kısa başlık), "
                    . "\"description\": string (problem ve verisel kanıt 2-3 cümle), "
                    . "\"recommendation\": string (somut aksiyon), "
                    . "\"affected_count\": int, "
                    . "\"examples\": [string, ...] (en fazla 3 örnek — ID veya isim)"
                    . "}], \"summary\": string (1 cümle genel durum) }\n\n"
                    . "PRENSİPLER:\n- " . implode("\n- ", self::PRINCIPLES),
            ],
            [
                'role' => 'user',
                'content' => "Bugün: " . now()->translatedFormat('d F Y') . "\n\nVeriler:\n"
                    . json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            ],
        ];

        $response = $this->ai->chatJson($messages, [
            'temperature' => 0.3,
            'max_tokens'  => 2000,
        ]);

        if (!$response || !isset($response['insights'])) {
            $result = [
                'insights' => $this->fallbackInsights($snapshot),
                'summary'  => 'AI yanıtı alınamadı — kural tabanlı analiz gösteriliyor.',
                'generated_at' => now()->toIso8601String(),
                'source' => 'fallback',
                'principles' => self::PRINCIPLES,
            ];
        } else {
            $result = [
                'insights' => $response['insights'],
                'summary'  => $response['summary'] ?? '',
                'generated_at' => now()->toIso8601String(),
                'source' => 'ai',
                'principles' => self::PRINCIPLES,
            ];
        }

        Cache::put($cacheKey, $result, now()->addHours(2));
        return $result;
    }

    /**
     * Ofis durum snapshot'ı — AI'ya verilecek veri.
     */
    protected function buildSnapshot(?int $officeId): array
    {
        $listingQ = Listing::query()->where('status', 'active');
        $leadQ = Lead::query();
        $dealQ = Deal::query();
        if ($officeId) {
            $listingQ->where('office_id', $officeId);
            $leadQ->where('office_id', $officeId);
            $dealQ->where('office_id', $officeId);
        }

        // Stale listings (uzun süredir aktif)
        $staleListings = (clone $listingQ)
            ->where(function ($q) {
                $q->where('published_at', '<=', now()->subDays(60))
                  ->orWhereNull('published_at');
            })
            ->whereNull('sold_at')
            ->orderBy('published_at')
            ->limit(20)
            ->get(['id', 'reference_no', 'title', 'price', 'published_at', 'view_count', 'quality_score']);

        // Düşük kalite skorlu ilanlar
        $lowQualityListings = (clone $listingQ)
            ->where('quality_score', '<', 40)
            ->limit(15)
            ->get(['id', 'reference_no', 'title', 'quality_score', 'view_count']);

        // Stuck deals
        $stuckDeals = (clone $dealQ)
            ->where('status', 'open')
            ->whereNotNull('stage_entered_at')
            ->where('stage_entered_at', '<=', now()->subDays(30))
            ->with('stage:id,name')
            ->orderBy('stage_entered_at')
            ->limit(15)
            ->get(['id', 'title', 'value', 'stage_id', 'stage_entered_at', 'assigned_to']);

        // Hot but dormant leads (skor 80+ ve 7+ gün dokunulmamış)
        $dormantHotLeads = (clone $leadQ)
            ->whereNotIn('status', ['converted', 'lost'])
            ->where(function ($q) {
                $q->where('ai_score', '>=', 80)->orWhere('score', '>=', 80);
            })
            ->where(function ($q) {
                $q->whereNull('last_activity_at')
                  ->orWhere('last_activity_at', '<=', now()->subDays(7));
            })
            ->orderByDesc('ai_score')
            ->limit(15)
            ->get(['id', 'title', 'score', 'ai_score', 'last_activity_at', 'assigned_to']);

        // Yanıt süresi yavaş lead'ler (60+ dk)
        $slowResponseLeads = (clone $leadQ)
            ->whereNotNull('first_response_at')
            ->whereRaw('TIMESTAMPDIFF(MINUTE, created_at, first_response_at) > 60')
            ->where('created_at', '>=', now()->subDays(30))
            ->limit(10)
            ->get(['id', 'title', 'created_at', 'first_response_at', 'assigned_to']);

        // Lead source ROI
        $sourceROI = $this->analytics->getLeadSourcePerformance([
            'date_from' => now()->subDays(60),
            'date_to'   => now(),
            'office_id' => $officeId,
        ]);
        $weakSources = array_filter($sourceROI, fn ($s) => $s['conversion_rate'] < 5 && $s['total'] >= 5);

        // Agent performance
        $agentPerf = $this->analytics->getAgentPerformance([
            'date_from' => now()->subDays(30),
            'date_to'   => now(),
            'office_id' => $officeId,
        ]);

        return [
            'period' => '30 günlük genel görüntü',
            'stale_listings_60d_count' => $staleListings->count(),
            'stale_listings_60d_examples' => $staleListings->map(fn ($l) => [
                'id' => $l->id,
                'ref' => $l->reference_no,
                'title' => $l->title,
                'days_on_market' => $l->published_at ? (int) $l->published_at->diffInDays(now()) : null,
                'views' => $l->view_count,
                'quality' => $l->quality_score,
            ])->take(5)->all(),
            'low_quality_listings_count' => $lowQualityListings->count(),
            'low_quality_examples' => $lowQualityListings->map(fn ($l) => [
                'id' => $l->id, 'ref' => $l->reference_no, 'title' => $l->title, 'quality' => $l->quality_score,
            ])->take(5)->all(),
            'stuck_deals_30d_count' => $stuckDeals->count(),
            'stuck_deals_examples' => $stuckDeals->map(fn ($d) => [
                'id' => $d->id,
                'title' => $d->title,
                'stage' => $d->stage?->name,
                'days_in_stage' => $d->stage_entered_at ? (int) \Carbon\Carbon::parse($d->stage_entered_at)->diffInDays(now()) : null,
                'value' => $d->value,
            ])->take(5)->all(),
            'dormant_hot_leads_count' => $dormantHotLeads->count(),
            'dormant_hot_leads_examples' => $dormantHotLeads->map(fn ($l) => [
                'id' => $l->id, 'title' => $l->title, 'score' => $l->ai_score ?: $l->score,
                'days_silent' => $l->last_activity_at ? (int) $l->last_activity_at->diffInDays(now()) : null,
            ])->take(5)->all(),
            'slow_response_leads_count' => $slowResponseLeads->count(),
            'weak_sources' => array_values($weakSources),
            'top_agents' => array_slice(array_map(fn ($a) => [
                'name' => $a['name'],
                'deals_won' => $a['metrics']['deals_won'],
                'revenue' => $a['metrics']['revenue'],
                'conversion_rate' => $a['conversion_rate'],
                'activities' => $a['metrics']['activities'],
            ], $agentPerf), 0, 10),
        ];
    }

    /**
     * AI yokken kural tabanlı insight üret.
     */
    protected function fallbackInsights(array $snapshot): array
    {
        $out = [];

        if (($snapshot['stale_listings_60d_count'] ?? 0) > 0) {
            $out[] = [
                'category' => 'listing',
                'severity' => 'warning',
                'title' => '60+ gündür satılmayan ilanlar',
                'description' => "{$snapshot['stale_listings_60d_count']} aktif ilan 60+ gündür satılmadan duruyor.",
                'recommendation' => 'Fiyat analizi yap, AI fotoğraf iyileştirme uygula veya açıklamayı yenile.',
                'affected_count' => $snapshot['stale_listings_60d_count'],
                'examples' => array_map(fn ($l) => "#{$l['id']} " . ($l['title'] ?? ''), array_slice($snapshot['stale_listings_60d_examples'] ?? [], 0, 3)),
            ];
        }

        if (($snapshot['stuck_deals_30d_count'] ?? 0) > 0) {
            $out[] = [
                'category' => 'deal',
                'severity' => 'critical',
                'title' => '30+ gün aynı aşamada takılmış deal\'lar',
                'description' => "{$snapshot['stuck_deals_30d_count']} açık deal aynı aşamada bir aydan uzun süredir bekliyor.",
                'recommendation' => 'Müşteriyle ilet, deal durumunu güncelle veya kapat.',
                'affected_count' => $snapshot['stuck_deals_30d_count'],
                'examples' => array_map(fn ($d) => "#{$d['id']} " . ($d['title'] ?? ''), array_slice($snapshot['stuck_deals_examples'] ?? [], 0, 3)),
            ];
        }

        if (($snapshot['dormant_hot_leads_count'] ?? 0) > 0) {
            $out[] = [
                'category' => 'lead',
                'severity' => 'critical',
                'title' => 'Yüksek skorlu ama hareketsiz lead\'ler',
                'description' => "{$snapshot['dormant_hot_leads_count']} sıcak lead 7+ gündür dokunulmamış. Kayıp riski yüksek.",
                'recommendation' => 'Bugün içinde her birine bir kanaldan ulaş.',
                'affected_count' => $snapshot['dormant_hot_leads_count'],
                'examples' => array_map(fn ($l) => "#{$l['id']} " . ($l['title'] ?? ''), array_slice($snapshot['dormant_hot_leads_examples'] ?? [], 0, 3)),
            ];
        }

        if (!empty($snapshot['weak_sources'] ?? [])) {
            $names = array_column($snapshot['weak_sources'], 'source');
            $out[] = [
                'category' => 'source',
                'severity' => 'warning',
                'title' => 'Düşük dönüşümlü lead kaynakları',
                'description' => count($names) . " kaynakta %5 altı dönüşüm görülüyor: " . implode(', ', array_slice($names, 0, 4)),
                'recommendation' => 'Bu kaynakların bütçesini gözden geçir, daha kaliteli kanallara kaydır.',
                'affected_count' => count($names),
                'examples' => $names,
            ];
        }

        return $out;
    }
}
