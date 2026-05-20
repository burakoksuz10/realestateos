<?php

namespace Modules\AI\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\CRM\Models\Deal;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Task;

/**
 * Bir danışman için "Bugün ne yapmalıyım?" listesi üretir.
 *
 * Verileri kendisi toplar (açık lead'ler, görevler, takılan deal'lar),
 * GPT'ye JSON modda öncelikli aksiyon listesi yazdırır. UI ve sabah
 * brifingi bu servisi kullanır.
 */
class DailyPlannerService
{
    public function __construct(protected AIService $ai) {}

    /**
     * Bir user için günlük plan üret. Tek saatlik cache (kullanıcı manuel
     * yenileyebilir).
     */
    public function generateForAgent(User $agent, bool $force = false): array
    {
        $cacheKey = "ai.daily_plan.{$agent->id}." . now()->format('Y-m-d-H');
        if (!$force) {
            $cached = Cache::get($cacheKey);
            if ($cached) return $cached;
        }

        $snapshot = $this->collectSnapshot($agent);

        // Hiç veri yoksa AI'ya gitme — boş plan döndür
        if ($snapshot['empty']) {
            $result = [
                'priorities' => [],
                'summary' => 'Bugün için aktif iş yok. Yeni lead aramaya odaklan veya portfoyü gözden geçir.',
                'generated_at' => now()->toIso8601String(),
                'source' => 'empty',
            ];
            Cache::put($cacheKey, $result, now()->addHour());
            return $result;
        }

        $this->ai->withContext($agent->office_id, $agent->id, 'copilot.daily_plan');

        $messages = [
            [
                'role' => 'system',
                'content' => "Sen RE-OS adlı emlak operasyon platformunda çalışan kıdemli bir satış koçusun. "
                    . "Bir danışmanın bugünkü iş yükünü görüp en yüksek değer üreten 5 aksiyonu önceliklendirmen gerek. "
                    . "Aksiyonlar SOMUT olsun: 'Müşteriyi ara', 'İlan göster', 'Teklif yaz', 'Takip görüşmesi'. "
                    . "Türkçe yaz. JSON döndür: { "
                    . "\"priorities\": [{ \"action\": string, \"reason\": string, \"target\": string (lead/deal/task adı + ID), \"impact\": \"yüksek|orta|düşük\", \"effort_minutes\": int }], "
                    . "\"summary\": string (1-2 cümle, gün için strateji) "
                    . "}",
            ],
            [
                'role' => 'user',
                'content' => "Danışman: {$agent->name}\n"
                    . "Bugün: " . now()->translatedFormat('d F Y, l') . "\n\n"
                    . "Açık iş yükü:\n" . json_encode($snapshot['payload'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            ],
        ];

        $response = $this->ai->chatJson($messages, [
            'temperature' => 0.4,
            'max_tokens'  => 900,
        ]);

        if (!$response || !isset($response['priorities'])) {
            $result = [
                'priorities' => $this->fallbackPriorities($snapshot['raw']),
                'summary' => 'AI üretemedi — kural tabanlı sıralama gösteriliyor.',
                'generated_at' => now()->toIso8601String(),
                'source' => 'fallback',
            ];
        } else {
            $result = [
                'priorities' => array_slice($response['priorities'], 0, 5),
                'summary' => $response['summary'] ?? '',
                'generated_at' => now()->toIso8601String(),
                'source' => 'ai',
            ];
        }

        Cache::put($cacheKey, $result, now()->addHour());
        return $result;
    }

    /**
     * Danışmanın açık iş yükünü topla.
     */
    protected function collectSnapshot(User $agent): array
    {
        // Bugünkü görevler
        $tasks = Task::where('assigned_to', $agent->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->where(function ($q) {
                $q->whereDate('due_date', '<=', today())
                  ->orWhereNull('due_date');
            })
            ->orderByRaw("FIELD(priority, 'urgent','high','medium','low')")
            ->limit(15)
            ->get(['id', 'title', 'priority', 'due_date', 'lead_id', 'deal_id']);

        // Sıcak lead'ler (skor >= 70 ve uzun süredir hareket yok ya da yeni)
        $hotLeads = Lead::where('assigned_to', $agent->id)
            ->whereNotIn('status', ['converted', 'lost'])
            ->where(function ($q) {
                $q->where('ai_score', '>=', 70)->orWhere('score', '>=', 70);
            })
            ->orderByDesc('ai_score')
            ->limit(10)
            ->get(['id', 'title', 'status', 'score', 'ai_score', 'last_activity_at', 'temperature']);

        // Takip gereken lead'ler (son 3+ gün hareket yok, hâlâ açık)
        $followUpLeads = Lead::where('assigned_to', $agent->id)
            ->whereNotIn('status', ['converted', 'lost'])
            ->where(function ($q) {
                $q->whereNull('last_activity_at')
                  ->orWhere('last_activity_at', '<', now()->subDays(3));
            })
            ->orderBy('last_activity_at')
            ->limit(10)
            ->get(['id', 'title', 'status', 'last_activity_at']);

        // Açık deal'lar — özellikle takılanlar
        $deals = Deal::where('assigned_to', $agent->id)
            ->where('status', 'open')
            ->with('stage:id,name')
            ->orderBy('stage_entered_at')
            ->limit(10)
            ->get(['id', 'title', 'value', 'currency', 'stage_id', 'stage_entered_at', 'probability']);

        $payload = [
            'today_tasks' => $tasks->map(fn ($t) => [
                'id'       => $t->id,
                'title'    => $t->title,
                'priority' => $t->priority,
                'due'      => optional($t->due_date)->toDateString(),
            ])->all(),
            'hot_leads' => $hotLeads->map(fn ($l) => [
                'id'           => $l->id,
                'name'         => $l->title,
                'score'        => $l->ai_score ?: $l->score,
                'temperature'  => $l->temperature,
                'last_contact' => optional($l->last_activity_at)->diffForHumans(),
            ])->all(),
            'follow_up_leads' => $followUpLeads->map(fn ($l) => [
                'id'           => $l->id,
                'name'         => $l->title,
                'status'       => $l->status,
                'last_contact' => $l->last_activity_at ? $l->last_activity_at->diffForHumans() : 'hiç temas yok',
            ])->all(),
            'open_deals' => $deals->map(fn ($d) => [
                'id'             => $d->id,
                'title'          => $d->title,
                'stage'          => $d->stage?->name,
                'value'          => $d->value,
                'days_in_stage'  => $d->stage_entered_at ? (int) Carbon::parse($d->stage_entered_at)->diffInDays(now()) : null,
                'probability'    => $d->probability,
            ])->all(),
        ];

        $empty = empty($payload['today_tasks'])
            && empty($payload['hot_leads'])
            && empty($payload['follow_up_leads'])
            && empty($payload['open_deals']);

        return [
            'payload' => $payload,
            'raw' => compact('tasks', 'hotLeads', 'followUpLeads', 'deals'),
            'empty' => $empty,
        ];
    }

    /**
     * AI yokken kural tabanlı 5 öncelik üret.
     */
    protected function fallbackPriorities(array $raw): array
    {
        $out = [];

        foreach ($raw['tasks'] ?? [] as $t) {
            if (in_array($t->priority, ['urgent', 'high'], true)) {
                $out[] = [
                    'action' => $t->title,
                    'reason' => 'Yüksek öncelikli görev — ' . $t->priority,
                    'target' => "Görev #{$t->id}",
                    'impact' => 'yüksek',
                    'effort_minutes' => 30,
                ];
            }
            if (count($out) >= 2) break;
        }

        foreach ($raw['hotLeads'] ?? [] as $l) {
            $out[] = [
                'action' => "Müşteriyi ara: " . ($l->title ?? 'Lead'),
                'reason' => "Sıcak lead — skor " . (int) ($l->ai_score ?: $l->score),
                'target' => "Lead #{$l->id}",
                'impact' => 'yüksek',
                'effort_minutes' => 15,
            ];
            if (count($out) >= 4) break;
        }

        foreach ($raw['deals'] ?? [] as $d) {
            $days = $d->stage_entered_at ? (int) Carbon::parse($d->stage_entered_at)->diffInDays(now()) : 0;
            if ($days >= 7) {
                $out[] = [
                    'action' => "Deal'ı ilerlet: " . ($d->title ?? "Deal #{$d->id}"),
                    'reason' => "{$days} gündür aynı aşamada",
                    'target' => "Deal #{$d->id}",
                    'impact' => 'orta',
                    'effort_minutes' => 20,
                ];
                break;
            }
        }

        return array_slice($out, 0, 5);
    }
}
