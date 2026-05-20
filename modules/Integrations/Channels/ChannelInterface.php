<?php

namespace Modules\Integrations\Channels;

use Modules\CRM\Models\Conversation;
use Modules\CRM\Models\Message;

/**
 * Birleşik iletişim kanalı arayüzü.
 *
 * Her kanal (Telegram, WhatsApp, SMS, Email...) bu sözleşmeyi
 * uygulayarak Unified Inbox'a takılır.
 */
interface ChannelInterface
{
    /**
     * Kanal teknik adı: telegram, whatsapp, sms, email, instagram_dm, facebook_messenger
     */
    public function name(): string;

    /**
     * Bu kanalın çalışır durumda olup olmadığı (config + credential var mı).
     */
    public function isEnabled(): bool;

    /**
     * Mesaj gönder. Yeni bir Message kaydı oluşturup conversation'a bağlar.
     *
     * @param  Conversation        $conversation
     * @param  string              $body
     * @param  array<int,string>   $attachments  Mutlak dosya yolları veya URL'ler
     * @param  int|null            $userId       Gönderen kullanıcı (audit)
     * @return Message
     */
    public function send(Conversation $conversation, string $body, array $attachments = [], ?int $userId = null): Message;
}
