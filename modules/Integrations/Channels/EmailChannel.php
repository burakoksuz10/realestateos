<?php

namespace Modules\Integrations\Channels;

use Illuminate\Support\Facades\Mail;
use Modules\CRM\Models\Conversation;
use Modules\CRM\Models\Message;

class EmailChannel implements ChannelInterface
{
    public function name(): string
    {
        return 'email';
    }

    public function isEnabled(): bool
    {
        return !empty(config('mail.default')) && !empty(config('mail.from.address'));
    }

    public function send(Conversation $conversation, string $body, array $attachments = [], ?int $userId = null): Message
    {
        $to = $conversation->channel_thread_id;
        if (!$to) {
            throw new \RuntimeException('Conversation has no email address');
        }

        $subject = $conversation->subject ?: 'Mesajınız';
        $status = 'queued';

        try {
            Mail::raw($body, function ($mail) use ($to, $subject, $attachments) {
                $mail->to($to)->subject($subject);
                foreach ($attachments as $a) {
                    if (is_string($a) && file_exists($a)) {
                        $mail->attach($a);
                    }
                }
            });
            $status = 'sent';
        } catch (\Throwable $e) {
            $status = 'failed';
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'direction' => 'out',
            'channel' => $this->name(),
            'body' => $body,
            'attachments' => $attachments ? array_map(fn ($a) => ['path' => $a], $attachments) : null,
            'sent_by_user_id' => $userId,
            'status' => $status,
            'meta' => ['subject' => $subject],
        ]);

        $conversation->touchLastMessage($message);

        return $message;
    }
}
