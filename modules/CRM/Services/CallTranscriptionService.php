<?php

namespace Modules\CRM\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\AI\Services\AIService;
use Modules\AI\Services\ElevenLabsService;
use Modules\CRM\Models\Activity;
use OpenAI\Client as OpenAIClient;

/**
 * AI Çağrı Özetleme.
 *
 * Akış:
 *   1) Audio dosya/URL → STT (ElevenLabs default, Whisper fallback)
 *   2) Transcript → GPT JSON (özet, sentiment, intent, buying signals, next actions)
 *   3) İlgili Activity'ye yaz / yeni Activity oluştur
 */
class CallTranscriptionService
{
    public function __construct(
        protected ElevenLabsService $eleven,
        protected AIService $ai,
    ) {}

    /**
     * Local file path için tam pipeline.
     *
     * @return array{text: string, summary: ?string, sentiment: ?string, intent: ?string, next_actions: ?array, buying_signals: ?array, provider: string}
     */
    public function fromFile(string $filePath, array $opts = []): array
    {
        $stt = $this->transcribe($filePath, $opts);
        $analysis = $this->analyze($stt['text'] ?? '', $opts);

        return array_merge($stt, $analysis);
    }

    /**
     * Recording URL → indir → fromFile().
     */
    public function fromUrl(string $url, array $opts = []): array
    {
        $tmp = tempnam(sys_get_temp_dir(), 'call_') . '.mp3';
        try {
            $audio = Http::timeout(60)->get($url);
            if (!$audio->successful()) {
                Log::warning('Recording download failed', ['url' => $url, 'status' => $audio->status()]);
                return $this->emptyResult('download_failed');
            }
            file_put_contents($tmp, $audio->body());
            return $this->fromFile($tmp, $opts);
        } finally {
            if (file_exists($tmp)) @unlink($tmp);
        }
    }

    /**
     * Çıktıyı bir Activity satırına yaz.
     */
    public function attachToActivity(Activity $activity, array $result): Activity
    {
        $activity->update([
            'call_transcript' => $result['text']      ?? $activity->call_transcript,
            'ai_summary'      => $result['summary']   ?? null,
            'ai_sentiment'    => $result['sentiment'] ?? null,
            'ai_intent'       => $result['intent']    ?? null,
            'ai_next_actions' => $result['next_actions'] ?? null,
            'call_sentiment'  => $result['sentiment'] ?? null,
            'metadata'        => array_merge($activity->metadata ?? [], [
                'transcription_provider' => $result['provider']        ?? null,
                'buying_signals'         => $result['buying_signals']  ?? null,
            ]),
        ]);
        return $activity;
    }

    /**
     * Çıktı için tek-tıkla bir Activity üret (lead/contact için).
     */
    public function createCallActivity(
        ?int $leadId,
        ?int $contactId,
        ?string $recordingUrl,
        array $result,
        ?int $userId = null,
        ?int $durationSeconds = null,
    ): Activity {
        return Activity::create([
            'user_id'            => $userId ?? auth()->id(),
            'lead_id'            => $leadId,
            'contact_id'         => $contactId,
            'type'               => 'call',
            'subject'            => 'Çağrı (AI özet)',
            'description'        => mb_substr((string) ($result['summary'] ?? ''), 0, 1000),
            'call_recording_url' => $recordingUrl,
            'call_duration'      => $durationSeconds,
            'call_transcript'    => $result['text']      ?? null,
            'call_sentiment'     => $result['sentiment'] ?? null,
            'ai_summary'         => $result['summary']   ?? null,
            'ai_sentiment'       => $result['sentiment'] ?? null,
            'ai_intent'          => $result['intent']    ?? null,
            'ai_next_actions'    => $result['next_actions'] ?? null,
            'completed_at'       => now(),
            'is_automated'       => true,
            'metadata'           => [
                'transcription_provider' => $result['provider']       ?? null,
                'buying_signals'         => $result['buying_signals'] ?? null,
            ],
        ]);
    }

    /**
     * Provider switch — eleven default, whisper fallback.
     */
    protected function transcribe(string $filePath, array $opts): array
    {
        $provider = $opts['transcription_provider']
            ?? config('reos.ai.transcription_provider', 'elevenlabs');

        if ($provider === 'elevenlabs' && $this->eleven->isConfigured()) {
            $result = $this->eleven->transcribe($filePath, $opts);
            if ($result) {
                return [
                    'text'     => $result['text'],
                    'provider' => 'elevenlabs',
                ];
            }
            Log::info('ElevenLabs STT returned null, falling back to Whisper');
        }

        // Whisper fallback
        $text = $this->transcribeViaWhisper($filePath, $opts);
        return [
            'text'     => (string) $text,
            'provider' => $text !== null ? 'whisper' : 'failed',
        ];
    }

    protected function transcribeViaWhisper(string $filePath, array $opts): ?string
    {
        try {
            /** @var OpenAIClient $client */
            $client = app(OpenAIClient::class);
            $response = $client->audio()->transcribe([
                'model'    => config('reos.ai.whisper_model', 'whisper-1'),
                'file'     => fopen($filePath, 'r'),
                'language' => $opts['language'] ?? 'tr',
            ]);
            return $response->text ?? null;
        } catch (\Throwable $e) {
            Log::warning('Whisper transcribe failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Transcript → GPT JSON (özet + sentiment + intent + next actions + buying signals).
     */
    protected function analyze(string $transcript, array $opts): array
    {
        if (trim($transcript) === '') {
            return $this->emptyAnalysis();
        }

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt()],
            ['role' => 'user',   'content' => "Görüşme transkripti:\n\n" . mb_substr($transcript, 0, 12000)],
        ];

        $json = $this->ai
            ->withContext(officeId: $opts['office_id'] ?? null, userId: $opts['user_id'] ?? null, feature: 'call.summary')
            ->chatJson($messages, [
                'model'       => $opts['model'] ?? config('reos.ai.summary_model', 'gpt-4o-mini'),
                'temperature' => 0.3,
            ]);

        if (!$json) {
            return $this->emptyAnalysis();
        }

        return [
            'summary'        => $json['summary']        ?? null,
            'sentiment'      => $json['sentiment']      ?? null,
            'intent'         => $json['intent']         ?? null,
            'next_actions'   => $json['next_actions']   ?? null,
            'buying_signals' => $json['buying_signals'] ?? null,
        ];
    }

    protected function systemPrompt(): string
    {
        return <<<'TXT'
Sen bir emlak ofisi danışmanı asistanısın. Sana bir müşteri görüşmesi (telefon transkripti) verilecek.
Aşağıdaki yapıda STRICT JSON döndür (başka açıklama, markdown veya ek metin yok):

{
  "summary": "Görüşmenin Türkçe 3-5 cümlelik özeti.",
  "sentiment": "positive | neutral | negative",
  "intent": "buy | rent | sell | inquiry | follow_up | complaint | other",
  "next_actions": ["danışmanın yapması gereken 2-4 maddelik somut aksiyon"],
  "buying_signals": ["açık alım/kira sinyali — bütçe, lokasyon, oda sayısı, aciliyet"]
}

Boş ya da belirsiz kalan alanları null olarak bırak.
TXT;
    }

    protected function emptyAnalysis(): array
    {
        return [
            'summary'        => null,
            'sentiment'      => null,
            'intent'         => null,
            'next_actions'   => null,
            'buying_signals' => null,
        ];
    }

    protected function emptyResult(string $reason): array
    {
        return [
            'text'     => '',
            'provider' => $reason,
        ] + $this->emptyAnalysis();
    }
}
