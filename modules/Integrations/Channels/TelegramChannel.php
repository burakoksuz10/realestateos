<?php

namespace Modules\Integrations\Channels;

use Modules\CRM\Models\Conversation;
use Modules\CRM\Models\Message;
use Modules\Telegram\Services\TelegramService;

class TelegramChannel implements ChannelInterface
{
    public function __construct(protected TelegramService $telegram)
    {
    }

    public function name(): string
    {
        return 'telegram';
    }

    public function isEnabled(): bool
    {
        return $this->telegram->isConfigured();
    }

    public function send(Conversation $conversation, string $body, array $attachments = [], ?int $userId = null): Message
    {
        $chatId = $conversation->channel_thread_id;
        if (!$chatId) {
            throw new \RuntimeException('Conversation has no telegram chat id');
        }

        $status = 'queued';
        $externalId = null;
        $sentOk = false;

        if (!empty($attachments)) {
            // İlk attachment'i foto olarak gönder, kalanlar şu an metin (basit ilk versiyon)
            $first = $attachments[0];
            $sentOk = $this->telegram->sendPhoto($chatId, $first, $body ?: null);
        } else {
            $sentOk = $this->telegram->sendMessage($chatId, $body);
        }

        $status = $sentOk ? 'sent' : 'failed';

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'direction' => 'out',
            'channel' => $this->name(),
            'external_id' => $externalId,
            'body' => $body,
            'attachments' => $attachments ? array_map(fn ($a) => ['url' => $a, 'type' => 'image'], $attachments) : null,
            'sent_by_user_id' => $userId,
            'status' => $status,
        ]);

        $conversation->touchLastMessage($message);

        return $message;
    }
}
