<?php

namespace Modules\Integrations\Channels;

use Modules\CRM\Models\Conversation;
use Modules\CRM\Models\Message;
use Modules\Integrations\Connectors\WhatsAppConnector;

class WhatsAppChannel implements ChannelInterface
{
    public function __construct(protected WhatsAppConnector $connector)
    {
    }

    public function name(): string
    {
        return 'whatsapp';
    }

    public function isEnabled(): bool
    {
        return !empty(config('services.whatsapp.access_token'))
            && !empty(config('services.whatsapp.phone_number_id'));
    }

    public function send(Conversation $conversation, string $body, array $attachments = [], ?int $userId = null): Message
    {
        $to = $conversation->channel_thread_id;
        if (!$to) {
            throw new \RuntimeException('Conversation has no WhatsApp phone');
        }

        $status = 'queued';
        $externalId = null;

        try {
            if (!empty($attachments)) {
                $result = $this->connector->sendMedia($to, 'image', $attachments[0], $body ?: null);
            } else {
                $result = $this->connector->sendMessage($to, $body, $conversation->contact);
            }
            $externalId = $result['messages'][0]['id'] ?? null;
            $status = 'sent';
        } catch (\Throwable $e) {
            $status = 'failed';
        }

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
