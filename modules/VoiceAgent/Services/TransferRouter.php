<?php

namespace Modules\VoiceAgent\Services;

use Modules\CRM\Models\Lead;
use Modules\RealEstate\Models\Listing;
use Modules\VoiceAgent\Models\VoiceAgentConfig;

/**
 * "Beni insanla görüştür" → kim açacak kararını verir.
 *
 * Karar girdileri:
 *   - Ofisin routing_mode'u (4 mod)
 *   - Mesai saati içinde mi?
 *   - İlan/Lead biliniyor mu? (ilanın danışmanı varsa onun telefonu)
 *
 * Çıktı:
 *   { action: 'transfer'|'callback'|'voicemail', phone?, target_user_id?, reason, message }
 *   - transfer  → AI: "Sizi bağlıyorum, ayrılmayın" + ElevenLabs SIP transfer
 *   - callback  → AI: "Şu an dışarıdayız, sizi X'te arayacağız" + book_callback
 *   - voicemail → AI: "Mesajınızı bırakın"
 */
class TransferRouter
{
    /**
     * @param array $ctx { office_id, caller_phone, listing_ref?, lead_id? }
     */
    public function resolve(array $ctx): array
    {
        $config = VoiceAgentConfig::where('office_id', $ctx['office_id'])->first();

        if (!$config || !$config->is_active) {
            return $this->callbackResponse(reason: 'voice_agent_inactive');
        }

        // Mesai dışıysa direkt callback
        if (!$config->isWithinBusinessHours()) {
            return $this->callbackResponse(reason: 'after_hours');
        }

        $listing = !empty($ctx['listing_ref'])
            ? Listing::where('reference_no', $ctx['listing_ref'])->first()
            : null;

        $lead = !empty($ctx['lead_id'])
            ? Lead::find($ctx['lead_id'])
            : null;

        $ownerAgent = $listing?->agent
            ?? $lead?->assignedTo;
        $ownerPhone = $ownerAgent?->phone
            ?? $config->default_agent_phone;

        return match ($config->routing_mode) {
            VoiceAgentConfig::MODE_LISTING_OWNER_ONLY => $this->ownerOrCallback($ownerPhone, $ownerAgent?->id, $config),
            VoiceAgentConfig::MODE_SECRETARY_ONLY     => $this->secretaryOrCallback($config),
            VoiceAgentConfig::MODE_CALLBACK_ONLY      => $this->callbackResponse(reason: 'callback_only_mode'),
            // Default mode: önce ilan sahibi → sonra sekreter
            default => $this->ownerThenSecretary($ownerPhone, $ownerAgent?->id, $config),
        };
    }

    protected function ownerThenSecretary(?string $ownerPhone, ?int $ownerUserId, VoiceAgentConfig $config): array
    {
        if ($ownerPhone) {
            return [
                'action'         => 'transfer',
                'phone'          => $ownerPhone,
                'target_user_id' => $ownerUserId,
                'fallback_phone' => $config->secretary_phone,
                'ring_timeout'   => $config->ring_timeout_seconds,
                'reason'         => 'listing_owner_first',
                'message'        => 'Sizi danışmanımıza bağlıyorum, ayrılmayın.',
            ];
        }
        return $this->secretaryOrCallback($config, fallbackReason: 'no_owner_phone');
    }

    protected function ownerOrCallback(?string $ownerPhone, ?int $ownerUserId, VoiceAgentConfig $config): array
    {
        if ($ownerPhone) {
            return [
                'action'         => 'transfer',
                'phone'          => $ownerPhone,
                'target_user_id' => $ownerUserId,
                'ring_timeout'   => $config->ring_timeout_seconds,
                'reason'         => 'listing_owner_only',
                'message'        => 'Sizi ilan danışmanına bağlıyorum, ayrılmayın.',
            ];
        }
        return $this->callbackResponse(reason: 'no_owner_phone_owner_only_mode');
    }

    protected function secretaryOrCallback(VoiceAgentConfig $config, string $fallbackReason = 'secretary_only'): array
    {
        if ($config->secretary_phone) {
            return [
                'action'       => 'transfer',
                'phone'        => $config->secretary_phone,
                'ring_timeout' => $config->ring_timeout_seconds,
                'reason'       => $fallbackReason,
                'message'      => 'Sizi sekreterimize aktarıyorum, ayrılmayın.',
            ];
        }
        return $this->callbackResponse(reason: 'no_secretary_phone');
    }

    protected function callbackResponse(string $reason): array
    {
        return [
            'action'  => 'callback',
            'reason'  => $reason,
            'message' => 'Şu an müsait birinden yardım alamıyoruz. Sizi en kısa sürede geri arayacağız — uygun bir saat söyleyebilir misiniz?',
        ];
    }
}
