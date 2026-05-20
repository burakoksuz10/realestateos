<?php

namespace Modules\Telegram\Services;

use Illuminate\Support\Facades\Log;
use Modules\AI\Services\ElevenLabsService;
use Modules\CRM\Models\Activity;
use Modules\CRM\Models\Lead;
use Modules\Telegram\Models\TelegramUser;
use OpenAI\Client as OpenAIClient;

class MediaIngestService
{
    public function __construct(
        protected TelegramService $telegram,
        protected ConversationIngestService $inbox,
    ) {}

    /**
     * When an agent forwards a photo/voice/document to the bot, attach it
     * to the agent's most recently active lead as an activity AND
     * record it as a Message in the agent's telegram conversation thread.
     */
    public function ingest(array $message, TelegramUser $tu, ?string $caption = null): void
    {
        $lead = $this->resolveTargetLead($tu);
        if (!$lead) {
            $this->telegram->sendMessage(
                $tu->telegram_chat_id,
                "📎 Dosya alındı ama bir lead'e bağlanamadı. Bir lead'e bağlamak için önce /leads ile bir tane seç."
            );
            return;
        }

        $kind     = $this->detectKind($message);
        $fileId   = $this->extractFileId($message, $kind);
        $localPath = null;

        if ($fileId) {
            $localPath = $this->telegram->downloadFile(
                $fileId,
                storage_path("app/telegram-ingest/" . $tu->user_id),
            );
        }

        $description = $caption ?: $this->autoDescription($kind);
        $aiSummary   = null;
        $duration    = $message['voice']['duration'] ?? $message['audio']['duration'] ?? $message['video']['duration'] ?? null;
        $messageId   = isset($message['message_id']) ? (string) $message['message_id'] : null;

        // Voice → transcribe via Whisper if AI is enabled.
        if ($kind === 'voice' && $localPath && config('reos.ai.enabled', true)) {
            $aiSummary = $this->transcribeVoice($localPath);
        }

        Activity::create([
            'user_id'      => $tu->user_id,
            'lead_id'      => $lead->id,
            'contact_id'   => $lead->contact_id,
            'type'         => $kind === 'voice' ? 'note' : 'note',
            'subject'      => $this->subjectFor($kind),
            'description'  => $description,
            'ai_summary'   => $aiSummary,
            'metadata'     => [
                'source'         => 'telegram',
                'telegram_kind'  => $kind,
                'local_path'     => $localPath,
                'telegram_chat'  => $tu->telegram_chat_id,
            ],
            'is_automated' => true,
        ]);

        // Unified Inbox kaydı — paralel
        try {
            $this->inbox->recordIncomingMedia(
                chatId: $tu->telegram_chat_id,
                kind: $kind,
                caption: $caption,
                localPath: $localPath,
                externalMessageId: $messageId,
                tu: $tu,
                aiSummary: $aiSummary,
                duration: $duration,
            );
        } catch (\Throwable $e) {
            Log::warning('MediaIngest → inbox failed', ['error' => $e->getMessage()]);
        }

        $lead->last_activity_at = now();
        $lead->save();

        $reply  = "✅ <b>Lead #{$lead->id}</b> kaydına eklendi.\n";
        $reply .= "Tür: <b>" . $this->labelFor($kind) . "</b>";
        if ($aiSummary) {
            $reply .= "\n\n📝 <b>Özet:</b>\n" . e(mb_substr($aiSummary, 0, 800));
        }

        $this->telegram->sendMessageWithButtons(
            $tu->telegram_chat_id,
            $reply,
            [[
                ['text' => '📋 Lead kartını gör', 'callback_data' => "lead.open:{$lead->id}"],
                ['text' => '➕ Yarın için görev', 'callback_data' => "lead.task:{$lead->id}"],
            ]],
        );
    }

    protected function resolveTargetLead(TelegramUser $tu): ?Lead
    {
        return Lead::where('assigned_to', $tu->user_id)
            ->whereNotIn('status', ['converted', 'lost'])
            ->orderByDesc('last_activity_at')
            ->orderByDesc('updated_at')
            ->first();
    }

    protected function detectKind(array $message): string
    {
        if (isset($message['photo']))    return 'photo';
        if (isset($message['voice']))    return 'voice';
        if (isset($message['audio']))    return 'audio';
        if (isset($message['video']))    return 'video';
        if (isset($message['document'])) return 'document';
        return 'unknown';
    }

    protected function extractFileId(array $message, string $kind): ?string
    {
        return match ($kind) {
            'photo'    => end($message['photo'])['file_id'] ?? null,
            'voice'    => $message['voice']['file_id']    ?? null,
            'audio'    => $message['audio']['file_id']    ?? null,
            'video'    => $message['video']['file_id']    ?? null,
            'document' => $message['document']['file_id'] ?? null,
            default    => null,
        };
    }

    protected function subjectFor(string $kind): string
    {
        return match ($kind) {
            'photo'    => 'Telegram: foto',
            'voice'    => 'Telegram: sesli not',
            'audio'    => 'Telegram: ses kaydı',
            'video'    => 'Telegram: video',
            'document' => 'Telegram: doküman',
            default    => 'Telegram: ek',
        };
    }

    protected function labelFor(string $kind): string
    {
        return match ($kind) {
            'photo'    => 'Fotoğraf',
            'voice'    => 'Sesli not',
            'audio'    => 'Ses',
            'video'    => 'Video',
            'document' => 'Doküman',
            default    => 'Ek',
        };
    }

    protected function autoDescription(string $kind): string
    {
        return "Saha ekibi tarafından Telegram üzerinden iletildi (" . $this->labelFor($kind) . ").";
    }

    /**
     * Transcribe a voice message — provider-aware (ElevenLabs default, Whisper fallback).
     * Returns null on failure (we log silently — the activity is still created).
     */
    protected function transcribeVoice(string $localPath): ?string
    {
        $provider = config('reos.ai.transcription_provider', 'elevenlabs');

        if ($provider === 'elevenlabs') {
            $eleven = app(ElevenLabsService::class);
            if ($eleven->isConfigured()) {
                $result = $eleven->transcribe($localPath, ['language' => 'tr']);
                if ($result && !empty($result['text'])) {
                    return $result['text'];
                }
                Log::info('Telegram voice: ElevenLabs returned empty, falling back to Whisper');
            }
        }

        try {
            /** @var OpenAIClient $client */
            $client = app(OpenAIClient::class);

            $response = $client->audio()->transcribe([
                'model'    => config('reos.ai.whisper_model', config('reos.ai.transcription_model', 'whisper-1')),
                'file'     => fopen($localPath, 'r'),
                'language' => 'tr',
            ]);

            return $response->text ?? null;
        } catch (\Throwable $e) {
            Log::warning('Telegram voice transcription failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
