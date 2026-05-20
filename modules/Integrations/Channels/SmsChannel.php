<?php

namespace Modules\Integrations\Channels;

use Modules\CRM\Models\Conversation;
use Modules\CRM\Models\Message;
use Modules\Integrations\Connectors\SMSConnector;

class SmsChannel implements ChannelInterface
{
    public function __construct(protected SMSConnector $connector)
    {
    }

    public function name(): string
    {
        return 'sms';
    }

    public function isEnabled(): bool
    {
        $provider = config('reos.marketing.sms.provider', 'netgsm');
        $cfg = config("services.{$provider}", []);
        return !empty($cfg);
    }

    public function send(Conversation $conversation, string $body, array $attachments = [], ?int $userId = null): Message
    {
        $to = $conversation->channel_thread_id;
        if (!$to) {
            throw new \RuntimeException('Conversation has no SMS phone');
        }

        $status = 'queued';
        $externalId = null;

        try {
            $result = $this->connector->send($to, $body, $conversation->contact);
            $externalId = $result['message_id'] ?? null;
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
            'sent_by_user_id' => $userId,
            'status' => $status,
        ]);

        $conversation->touchLastMessage($message);

        return $message;
    }
}
