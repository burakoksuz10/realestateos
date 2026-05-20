<?php

namespace Modules\Telegram\Services;

use Illuminate\Support\Facades\Log;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Task;
use Modules\Telegram\Models\TelegramUser;

class CallbackHandler
{
    public function __construct(protected TelegramService $telegram) {}

    /**
     * Handle a Telegram callback_query (button tap on an inline keyboard).
     *
     * Callback data convention: "<action>:<id>[:<extra>]"
     *   lead.assign:<leadId>   — assign that lead to the tapper
     *   lead.task:<leadId>     — create a follow-up task on this lead for tapper
     *   lead.open:<leadId>     — send the lead summary as a message
     */
    public function handle(array $callback): void
    {
        $callbackId = (string) ($callback['id'] ?? '');
        $chatId     = (string) ($callback['message']['chat']['id'] ?? '');
        $data       = (string) ($callback['data'] ?? '');

        if (!$callbackId || !$chatId || !$data) {
            return;
        }

        $tu = TelegramUser::where('telegram_chat_id', $chatId)->where('is_active', true)->first();
        if (!$tu) {
            $this->telegram->answerCallback($callbackId, 'Hesabınız bağlı değil.', true);
            return;
        }

        [$action, $idPart, $extra] = array_pad(explode(':', $data, 3), 3, null);

        try {
            match ($action) {
                'lead.assign' => $this->assignLead($callbackId, $chatId, $tu, (int) $idPart),
                'lead.task'   => $this->createTaskOnLead($callbackId, $chatId, $tu, (int) $idPart),
                'lead.open'   => $this->openLead($callbackId, $chatId, (int) $idPart),
                default       => $this->telegram->answerCallback($callbackId, 'Bilinmeyen işlem.', true),
            };
        } catch (\Throwable $e) {
            Log::error('Telegram callback failed', ['error' => $e->getMessage(), 'data' => $data]);
            $this->telegram->answerCallback($callbackId, 'Bir hata oluştu.', true);
        }
    }

    protected function assignLead(string $callbackId, string $chatId, TelegramUser $tu, int $leadId): void
    {
        $lead = Lead::find($leadId);
        if (!$lead) {
            $this->telegram->answerCallback($callbackId, 'Lead bulunamadı.', true);
            return;
        }

        $lead->assigned_to = $tu->user_id;
        $lead->save();

        $this->telegram->answerCallback($callbackId, '✅ Size atandı.');
        $this->telegram->sendMessage($chatId, "✅ <b>Lead #{$lead->id}</b> size atandı.");
    }

    protected function createTaskOnLead(string $callbackId, string $chatId, TelegramUser $tu, int $leadId): void
    {
        $lead = Lead::find($leadId);
        if (!$lead) {
            $this->telegram->answerCallback($callbackId, 'Lead bulunamadı.', true);
            return;
        }

        Task::create([
            'created_by'  => $tu->user_id,
            'assigned_to' => $tu->user_id,
            'lead_id'     => $lead->id,
            'contact_id'  => $lead->contact_id,
            'title'       => "Lead takibi: " . ($lead->title ?: "Lead #{$lead->id}"),
            'type'        => 'follow_up',
            'priority'    => 'high',
            'status'      => 'pending',
            'due_date'    => now()->addDay()->toDateString(),
        ]);

        $this->telegram->answerCallback($callbackId, '✅ Görev oluşturuldu (yarın).');
        $this->telegram->sendMessage($chatId, "📋 Lead #{$lead->id} için yarın için görev oluşturuldu.");
    }

    protected function openLead(string $callbackId, string $chatId, int $leadId): void
    {
        $lead = Lead::with('contact')->find($leadId);
        if (!$lead) {
            $this->telegram->answerCallback($callbackId, 'Lead bulunamadı.', true);
            return;
        }

        $score = $lead->ai_score ?: $lead->score ?: 0;
        $body  = "📋 <b>Lead #{$lead->id}</b>\n";
        $body .= e($lead->title ?: '—') . "\n";
        $body .= "Skor: <b>{$score}</b> • Durum: " . e($lead->status) . "\n";
        if ($lead->contact) {
            $name  = trim(($lead->contact->first_name ?? '') . ' ' . ($lead->contact->last_name ?? ''));
            $body .= "\n👤 " . e($name ?: '—');
            if ($lead->contact->phone) $body .= "\n📞 " . e($lead->contact->phone);
        }

        $this->telegram->answerCallback($callbackId);
        $this->telegram->sendMessage($chatId, $body);
    }
}
