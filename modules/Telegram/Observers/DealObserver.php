<?php

namespace Modules\Telegram\Observers;

use Modules\CRM\Models\Deal;
use Modules\CRM\Models\PipelineStage;
use Modules\Telegram\Services\TelegramService;

class DealObserver
{
    public function __construct(protected TelegramService $telegram) {}

    public function updated(Deal $deal): void
    {
        if ($deal->wasChanged('stage_id')) {
            $this->notifyStageChange($deal);
        }

        if ($deal->wasChanged('status')) {
            $status = $deal->status;
            if (in_array($status, ['won', 'lost'], true)) {
                $this->notifyClosed($deal, $status);
            }
        }
    }

    protected function notifyStageChange(Deal $deal): void
    {
        if (!$deal->assigned_to) return;

        $newStage = $deal->stage_id ? PipelineStage::find($deal->stage_id) : null;
        $oldStage = $deal->getOriginal('stage_id') ? PipelineStage::find($deal->getOriginal('stage_id')) : null;

        $body  = "📈 <b>Deal aşaması değişti</b>\n\n";
        $body .= "<b>#{$deal->id}</b> " . e($deal->title ?: 'Deal') . "\n";
        if ($oldStage || $newStage) {
            $body .= "Aşama: " . e($oldStage?->name ?? '—') . " → <b>" . e($newStage?->name ?? '—') . "</b>\n";
        }
        if ($deal->value) {
            $body .= "Değer: <b>" . number_format((float) $deal->value, 0, ',', '.') . " ₺</b>\n";
        }

        $this->telegram->notifyUser($deal->assigned_to, $body);
    }

    protected function notifyClosed(Deal $deal, string $status): void
    {
        if (!$deal->assigned_to) return;

        $emoji  = $status === 'won' ? '🎉' : '💔';
        $title  = $status === 'won' ? 'Deal kazanıldı!' : 'Deal kaybedildi';

        $body  = "{$emoji} <b>{$title}</b>\n\n";
        $body .= "<b>#{$deal->id}</b> " . e($deal->title ?: 'Deal') . "\n";
        if ($deal->value) {
            $body .= "Değer: <b>" . number_format((float) $deal->value, 0, ',', '.') . " ₺</b>\n";
        }
        if ($deal->commission_amount) {
            $body .= "Komisyon: <b>" . number_format((float) $deal->commission_amount, 0, ',', '.') . " ₺</b>\n";
        }
        if ($status === 'lost' && $deal->lost_reason) {
            $body .= "Sebep: " . e($deal->lost_reason) . "\n";
        }

        $this->telegram->notifyUser($deal->assigned_to, $body);

        if ($deal->office_id) {
            $this->telegram->notifyOffice(
                $deal->office_id,
                "{$emoji} <b>" . e($deal->title ?: "Deal #{$deal->id}") . "</b> — " . ($status === 'won' ? 'kazanıldı' : 'kaybedildi'),
            );
        }
    }
}
