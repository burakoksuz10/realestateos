<?php

namespace Modules\VoiceAgent\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\CRM\Models\Activity;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\Lead;
use Modules\Telegram\Services\TelegramService;
use Modules\VoiceAgent\Models\VoiceAgentConfig;

/**
 * ElevenLabs Conversational AI Agents post-call webhook'u.
 *
 * Çağrı bittiğinde ElevenLabs şu ana hatlarda payload gönderir:
 * {
 *   conversation_id, agent_id, started_at, ended_at, duration_seconds,
 *   caller_phone, transferred_to, transfer_status, recording_url,
 *   transcript: [{ role: 'user'|'agent', text, ts }],
 *   summary, sentiment, intent, tool_calls: [...]
 * }
 *
 * Endpoint: POST /api/voice-agent/webhook
 * Auth: X-Voice-Agent-Token (verify middleware)
 */
class WebhookController extends Controller
{
    public function handle(Request $request, TelegramService $telegram): JsonResponse
    {
        $payload = $request->all();
        Log::info('Voice agent webhook', ['payload_keys' => array_keys($payload)]);

        $conversationId = (string) ($payload['conversation_id'] ?? $payload['id'] ?? '');
        $agentId        = $payload['agent_id'] ?? null;
        $callerPhone    = $payload['caller_phone'] ?? $payload['from'] ?? null;
        $duration       = (int) ($payload['duration_seconds'] ?? $payload['duration'] ?? 0);
        $recordingUrl   = $payload['recording_url'] ?? null;
        $summary        = $payload['summary'] ?? null;
        $sentiment      = $payload['sentiment'] ?? null;
        $intent         = $payload['intent'] ?? null;
        $transcriptArr  = $payload['transcript'] ?? [];
        $toolCalls      = $payload['tool_calls'] ?? [];

        // İlişkili ofis — agent_id'ye göre bul
        $config = $agentId
            ? VoiceAgentConfig::where('elevenlabs_agent_id', $agentId)->first()
            : null;
        $officeId = $config?->office_id;

        // Çağrı sırasında oluşmuş lead_id varsa tool_calls'tan çek
        $leadId = $this->extractLeadId($toolCalls);
        $lead = $leadId ? Lead::with('contact', 'assignedTo')->find($leadId) : null;

        $contactId = $lead?->contact_id;
        if (!$contactId && $callerPhone) {
            $contact = Contact::where('phone', $callerPhone)
                ->when($officeId, fn ($q) => $q->where(function ($q2) use ($officeId) {
                    $q2->whereNull('office_id')->orWhere('office_id', $officeId);
                }))
                ->first();
            $contactId = $contact?->id;
        }

        // Transcript metnini birleştir
        $transcriptText = $this->flattenTranscript($transcriptArr);

        $activity = Activity::create([
            'lead_id'            => $leadId,
            'contact_id'         => $contactId,
            'type'               => 'call',
            'subject'            => 'Sesli AI Çağrısı',
            'description'        => $summary
                ?: 'AI sekreter ile yapılan görüşme — ' . ($intent ? "Niyet: {$intent}" : ''),
            'call_recording_url' => $recordingUrl,
            'call_duration'      => $duration,
            'call_transcript'    => $transcriptText,
            'call_sentiment'     => $sentiment,
            'ai_summary'         => $summary,
            'ai_sentiment'       => $sentiment,
            'ai_intent'          => $intent,
            'is_automated'       => true,
            'completed_at'       => now(),
            'metadata'           => [
                'source'          => 'voice_agent',
                'conversation_id' => $conversationId,
                'agent_id'        => $agentId,
                'caller_phone'    => $callerPhone,
                'transferred_to'  => $payload['transferred_to']   ?? null,
                'transfer_status' => $payload['transfer_status'] ?? null,
                'tool_calls'      => $toolCalls,
            ],
        ]);

        // Danışmana / ofis kanalına özet at
        if ($config) {
            $this->notifyOffice($telegram, $config, $lead, $callerPhone, $summary, $intent, $duration);
        }

        return response()->json([
            'ok'          => true,
            'activity_id' => $activity->id,
        ]);
    }

    protected function extractLeadId(array $toolCalls): ?int
    {
        foreach ($toolCalls as $tc) {
            $name = $tc['name'] ?? $tc['tool'] ?? '';
            if (in_array($name, ['create_lead', 'createLead', 'create-lead'], true)) {
                $resp = $tc['response'] ?? $tc['result'] ?? [];
                if (is_string($resp)) {
                    $resp = json_decode($resp, true) ?: [];
                }
                if (!empty($resp['lead_id'])) {
                    return (int) $resp['lead_id'];
                }
            }
        }
        return null;
    }

    protected function flattenTranscript(array $transcript): string
    {
        if (empty($transcript)) return '';
        $lines = [];
        foreach ($transcript as $turn) {
            $role = ($turn['role'] ?? '') === 'agent' ? 'AI' : 'Müşteri';
            $text = $turn['text'] ?? $turn['content'] ?? '';
            if (trim($text) === '') continue;
            $lines[] = "{$role}: {$text}";
        }
        return implode("\n", $lines);
    }

    protected function notifyOffice(
        TelegramService $telegram,
        VoiceAgentConfig $config,
        ?Lead $lead,
        ?string $callerPhone,
        ?string $summary,
        ?string $intent,
        int $duration,
    ): void {
        $name = $lead?->contact?->full_name ?? 'Bilinmeyen';
        $minutes = round($duration / 60, 1);

        $lines = [];
        $lines[] = "📞 *Sesli AI çağrısı bitti*";
        $lines[] = "👤 {$name}" . ($callerPhone ? " ({$callerPhone})" : '');
        $lines[] = "⏱ {$minutes} dk";
        if ($intent)    $lines[] = "🎯 Niyet: {$intent}";
        if ($summary)   $lines[] = "\n📝 " . mb_substr($summary, 0, 400);
        if ($lead)      $lines[] = "\n🔗 Lead #{$lead->id}";

        $msg = implode("\n", $lines);

        try {
            // Önce ilan danışmanı (lead'in assignee'sine)
            if ($lead?->assignedTo) {
                $tu = \Modules\Telegram\Models\TelegramUser::where('user_id', $lead->assigned_to)
                    ->where('is_active', true)
                    ->first();
                if ($tu) {
                    $telegram->sendMessage($tu->telegram_chat_id, $msg);
                    return;
                }
            }
            if ($config->telegram_office_channel) {
                $telegram->sendMessage($config->telegram_office_channel, $msg);
            }
        } catch (\Throwable $e) {
            Log::warning('Voice agent post-call notify failed', ['error' => $e->getMessage()]);
        }
    }
}
