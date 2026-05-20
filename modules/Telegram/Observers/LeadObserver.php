<?php

namespace Modules\Telegram\Observers;

use Modules\CRM\Models\Lead;
use Modules\Telegram\Services\TelegramService;

class LeadObserver
{
    public function __construct(protected TelegramService $telegram) {}

    public function created(Lead $lead): void
    {
        if ($lead->assigned_to) {
            $this->notifyAssignment($lead);
        }

        $score = (int) ($lead->ai_score ?: $lead->score ?: 0);
        if ($score >= 80) {
            $this->notifyHot($lead, $score);
        }
    }

    public function updated(Lead $lead): void
    {
        // Assignment changed → tell the new owner.
        if ($lead->wasChanged('assigned_to') && $lead->assigned_to) {
            $this->notifyAssignment($lead);
        }

        // Score crossed the 80 threshold (cold/warm → hot).
        if ($lead->wasChanged(['ai_score', 'score'])) {
            $current  = (int) ($lead->ai_score ?: $lead->score ?: 0);
            $previous = (int) ($lead->getOriginal('ai_score') ?: $lead->getOriginal('score') ?: 0);

            if ($current >= 80 && $previous < 80) {
                $this->notifyHot($lead, $current);
            }
        }
    }

    protected function notifyAssignment(Lead $lead): void
    {
        if (!$lead->assigned_to) return;

        $score = (int) ($lead->ai_score ?: $lead->score ?: 0);
        $temp  = $score >= 80 ? '🔥' : ($score >= 60 ? '🟠' : ($score >= 40 ? '🟡' : '🧊'));

        $body  = "🆕 <b>Yeni lead size atandı</b>\n\n";
        $body .= "{$temp} <b>#{$lead->id}</b> " . e($lead->title ?: '—') . "\n";
        $body .= "Skor: <b>{$score}</b> • Durum: " . e($lead->status ?: '—');

        if ($lead->relationLoaded('contact') || $lead->contact_id) {
            $contact = $lead->contact;
            if ($contact) {
                $name = trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? ''));
                if ($name)            $body .= "\n👤 " . e($name);
                if ($contact->phone)  $body .= "\n📞 " . e($contact->phone);
            }
        }

        $this->telegram->notifyUser($lead->assigned_to, $body, [
            'reply_markup' => json_encode([
                'inline_keyboard' => [[
                    ['text' => '📋 Detay',         'callback_data' => "lead.open:{$lead->id}"],
                    ['text' => '➕ Yarın görev',   'callback_data' => "lead.task:{$lead->id}"],
                ]],
            ]),
        ]);
    }

    protected function notifyHot(Lead $lead, int $score): void
    {
        if (!$lead->assigned_to) return;

        $body  = "🔥 <b>HOT LEAD ALARMI</b>\n\n";
        $body .= "<b>#{$lead->id}</b> " . e($lead->title ?: '—') . "\n";
        $body .= "AI skor: <b>{$score}</b>\n";

        $signals = $lead->intent_signals ?? [];
        if (is_array($signals) && !empty($signals)) {
            $top = array_slice($signals, 0, 3);
            $labels = array_map(fn($s) => is_array($s) ? ($s['label'] ?? json_encode($s)) : (string) $s, $top);
            $body .= "💡 " . e(implode(' • ', $labels)) . "\n";
        }

        $body .= "\nHızlıca dokunun — bu lead alev alev.";

        $this->telegram->notifyUser($lead->assigned_to, $body, [
            'reply_markup' => json_encode([
                'inline_keyboard' => [[
                    ['text' => '📋 Detay', 'callback_data' => "lead.open:{$lead->id}"],
                ]],
            ]),
        ]);
    }
}
