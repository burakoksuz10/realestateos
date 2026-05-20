<?php

namespace Modules\CRM\Console\Commands;

use Illuminate\Console\Command;
use Modules\CRM\Models\Deal;
use Modules\CRM\Models\PipelineStage;
use Modules\Telegram\Services\TelegramService;

/**
 * Takılan deal uyarıları.
 *
 * `stage_entered_at` üzerinden N gün+ aynı stage'de kalan açık deal'ları
 * danışmana + ofis kanalına Telegram'dan bildirir. Her gün bir kere
 * çalışacak şekilde planlanmıştır (idempotent değil — her gün hatırlatma
 * gelir; çok rahatsız olursa eşik yükseltilir).
 */
class AlertStalledDealsCommand extends Command
{
    protected $signature = 'deals:stalled
                            {--days=14 : Stage\'de kalma gün eşiği}
                            {--limit=200 : Maks. deal sayısı}
                            {--dry : Sadece raporla, bildirim atma}';

    protected $description = 'Belirlenen günden fazla aynı stage\'de kalan açık deal\'lara takılma uyarısı gönderir.';

    public function handle(TelegramService $telegram): int
    {
        $days = max(1, (int) $this->option('days'));
        $limit = max(1, (int) $this->option('limit'));
        $dry = (bool) $this->option('dry');

        $threshold = now()->subDays($days);

        // Won/lost stage'leri hariç tut
        $closedStageIds = PipelineStage::query()
            ->where(function ($q) {
                $q->where('is_won_stage', true)->orWhere('is_lost_stage', true);
            })
            ->pluck('id')
            ->all();

        $query = Deal::query()
            ->with(['stage', 'assignedTo', 'contact', 'listing'])
            ->where('status', 'open')
            ->whereNotNull('stage_entered_at')
            ->where('stage_entered_at', '<=', $threshold)
            ->when(!empty($closedStageIds), fn ($q) => $q->whereNotIn('stage_id', $closedStageIds))
            ->limit($limit);

        $deals = $query->get();

        if ($deals->isEmpty()) {
            $this->info("Takılan deal yok ({$days}+ gün eşiğinde).");
            return self::SUCCESS;
        }

        $byAgent = $deals->groupBy('assigned_to');
        $byOffice = $deals->groupBy('office_id');

        $notifications = 0;

        // Per agent
        foreach ($byAgent as $agentId => $agentDeals) {
            if (!$agentId) continue;

            $body  = "⚠️ <b>Takılmış deal'lar ({$days}+ gün)</b>\n\n";
            foreach ($agentDeals as $deal) {
                $stageName = $deal->stage?->name ?? '—';
                $dayCount = (int) now()->diffInDays($deal->stage_entered_at);
                $value = $deal->value ? number_format((float) $deal->value, 0, ',', '.') . ' ₺' : '—';
                $body .= "• <b>#{$deal->id}</b> " . e($deal->title ?: 'Deal') . "\n";
                $body .= "  Stage: " . e($stageName) . " — <b>{$dayCount} gün</b>\n";
                $body .= "  Değer: {$value}\n";
                $contact = $deal->contact;
                if ($contact) {
                    $body .= "  Kontak: " . e($contact->full_name ?? $contact->first_name ?? 'Müşteri');
                    if ($contact->phone) $body .= " · {$contact->phone}";
                    $body .= "\n";
                }
                $body .= "\n";
            }
            $body .= "Aksiyon önerisi: Müşteriyle iletişime geç, deal durumunu güncelle veya kapat.";

            if (!$dry) {
                $sent = $telegram->notifyUser($agentId, $body);
                $notifications += $sent;
            }
        }

        // Office summary (manager view)
        foreach ($byOffice as $officeId => $officeDeals) {
            if (!$officeId) continue;
            $count = $officeDeals->count();
            $totalValue = $officeDeals->sum('value');
            $summary = "📊 <b>Ofis takılmış deal raporu</b>\n";
            $summary .= "Toplam: <b>{$count}</b> deal, " . number_format((float) $totalValue, 0, ',', '.') . " ₺\n";
            $summary .= "Eşik: <b>{$days}+ gün</b> aynı stage'de kalanlar.";

            if (!$dry) {
                $sent = $telegram->notifyOffice($officeId, $summary);
                $notifications += $sent;
            }
        }

        $action = $dry ? 'simulate' : 'sent';
        $this->info("Takılan deal: {$deals->count()} — bildirim {$action}: {$notifications}");

        return self::SUCCESS;
    }
}
