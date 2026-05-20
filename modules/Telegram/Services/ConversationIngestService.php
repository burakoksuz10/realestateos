<?php

namespace Modules\Telegram\Services;

use Modules\CRM\Models\Conversation;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Message;
use Modules\Telegram\Models\TelegramUser;

/**
 * Telegram'dan gelen mesajları Unified Inbox (Conversation + Message)
 * yapısına yazar. Activity yazımı paralel olarak korunur.
 */
class ConversationIngestService
{
    /**
     * Incoming text mesajı conversation + message olarak yaz.
     */
    public function recordIncomingText(
        string $chatId,
        string $text,
        ?string $externalMessageId,
        ?TelegramUser $tu,
    ): Message {
        $conversation = $this->resolveConversation($chatId, $tu);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'direction'       => 'in',
            'channel'         => 'telegram',
            'external_id'     => $externalMessageId,
            'body'            => $text,
            'status'          => 'received',
        ]);

        $conversation->touchLastMessage($message);

        return $message;
    }

    /**
     * Foto / ses / video / doküman → Message attachment olarak yaz.
     */
    public function recordIncomingMedia(
        string $chatId,
        string $kind,
        ?string $caption,
        ?string $localPath,
        ?string $externalMessageId,
        ?TelegramUser $tu,
        ?string $aiSummary = null,
        ?int $duration = null,
    ): Message {
        $conversation = $this->resolveConversation($chatId, $tu);

        $attachment = [
            'type' => $kind,
            'path' => $localPath,
        ];
        if ($localPath && str_starts_with($localPath, storage_path('app/public'))) {
            $relative = str_replace(storage_path('app/public'), '', $localPath);
            $attachment['url'] = url('storage' . $relative);
        }
        if ($duration !== null) {
            $attachment['duration'] = $duration;
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'direction'       => 'in',
            'channel'         => 'telegram',
            'external_id'     => $externalMessageId,
            'body'            => $caption,
            'attachments'     => [$attachment],
            'ai_summary'      => $aiSummary,
            'status'          => 'received',
        ]);

        $conversation->touchLastMessage($message);

        return $message;
    }

    /**
     * Telegram chat_id için conversation'ı bul veya oluştur.
     * Office + lead/contact eşleştirmesi TelegramUser üzerinden yapılır.
     */
    public function resolveConversation(string $chatId, ?TelegramUser $tu): Conversation
    {
        $officeId = null;
        $leadId = null;
        $contactId = null;

        if ($tu) {
            $user = $tu->user;
            $officeId = $user?->office_id;

            // Eşleştirilmiş agent'in son aktif lead'inden contact'ı çek
            $lead = Lead::where('assigned_to', $tu->user_id)
                ->whereNotIn('status', ['converted', 'lost'])
                ->orderByDesc('last_activity_at')
                ->orderByDesc('updated_at')
                ->first();
            if ($lead) {
                $leadId = $lead->id;
                $contactId = $lead->contact_id;
            }
        }

        return Conversation::firstOrCreate(
            [
                'channel'           => 'telegram',
                'channel_thread_id' => $chatId,
            ],
            [
                'office_id'  => $officeId,
                'lead_id'    => $leadId,
                'contact_id' => $contactId,
                'status'     => 'open',
                'subject'    => null,
                'meta'       => [
                    'telegram_user_id' => $tu?->id,
                ],
            ],
        );
    }
}
