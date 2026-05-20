<?php

namespace Modules\VoiceAgent\Services;

use Illuminate\Support\Facades\Log;
use Modules\CRM\Models\Lead;
use Modules\RealEstate\Models\Listing;
use Modules\Telegram\Models\TelegramUser;
use Modules\Telegram\Services\TelegramService;
use Modules\VoiceAgent\Models\VoiceAgentConfig;

/**
 * Çağrı bağlanmadan önce hedef danışmana (ya da ofis kanalına)
 * "Müşteri X arıyor, Y ilanı, bütçesi Z, hazır ol" Telegram brifingi atar.
 */
class PreCallBriefService
{
    public function __construct(protected TelegramService $telegram) {}

    public function dispatch(array $ctx): array
    {
        $config = VoiceAgentConfig::where('office_id', $ctx['office_id'])->first();
        if (!$config) {
            return ['delivered' => false, 'reason' => 'no_voice_config'];
        }

        $listing = !empty($ctx['listing_ref'])
            ? Listing::where('reference_no', $ctx['listing_ref'])->first()
            : null;

        $lead = !empty($ctx['lead_id'])
            ? Lead::with('contact')->find($ctx['lead_id'])
            : null;

        $msg = $this->buildMessage($ctx, $listing, $lead);

        // Hedef agent için Telegram chat_id bul
        $sent = false;
        if (!empty($ctx['target_user_id'])) {
            $tu = TelegramUser::where('user_id', $ctx['target_user_id'])
                ->where('is_active', true)
                ->first();
            if ($tu) {
                $sent = $this->telegram->sendMessage($tu->telegram_chat_id, $msg);
            }
        }

        // Fallback: ofis kanalına gönder
        if (!$sent && $config->telegram_office_channel) {
            try {
                $sent = $this->telegram->sendMessage($config->telegram_office_channel, $msg);
            } catch (\Throwable $e) {
                Log::warning('PreCallBrief office channel failed', ['error' => $e->getMessage()]);
            }
        }

        return [
            'delivered' => (bool) $sent,
            'channel'   => $sent ? ($ctx['target_user_id'] ? 'agent' : 'office') : null,
        ];
    }

    protected function buildMessage(array $ctx, ?Listing $listing, ?Lead $lead): string
    {
        $name  = $lead?->contact?->full_name ?? 'Bilinmeyen';
        $phone = $ctx['caller_phone'];
        $summary = $ctx['summary'] ?? '';

        $lines = [];
        $lines[] = "📞 *Sesli AI — Bağlanmak üzere*";
        $lines[] = "👤 {$name}";
        $lines[] = "📱 {$phone}";

        if ($listing) {
            $lines[] = "🏠 İlan: #{$listing->reference_no} — " . mb_substr($listing->title, 0, 60);
            if ($listing->price) {
                $lines[] = "💰 " . number_format($listing->price, 0, ',', '.') . ' ' . ($listing->price_currency ?: 'TL');
            }
        }
        if ($lead?->metadata['budget'] ?? null) {
            $lines[] = "💵 Müşteri bütçesi: " . number_format($lead->metadata['budget'], 0, ',', '.');
        }

        $lines[] = '';
        $lines[] = "📝 *Konuşma özeti:* " . mb_substr($summary, 0, 400);
        $lines[] = '';
        $lines[] = "⏱ Yaklaşık 5 saniye içinde sana bağlanıyor — hazır ol.";

        return implode("\n", $lines);
    }
}
