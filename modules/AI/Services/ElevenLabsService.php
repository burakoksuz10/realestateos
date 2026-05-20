<?php

namespace Modules\AI\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ElevenLabs STT (speech-to-text) + TTS (text-to-speech) servisi.
 *
 * - Türkçe odaklı transkripsiyon için STT'de `scribe_v1` modelini kullanırız.
 * - TTS için multilingual model + Türkçe ses (configurable).
 *
 * API key boşsa servis "configured değil" döner — fallback (Whisper) devreye girer.
 */
class ElevenLabsService
{
    protected string $baseUrl;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.elevenlabs.api_key');
        $this->baseUrl = config('services.elevenlabs.base_url', 'https://api.elevenlabs.io/v1');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Bir ses dosyasını yazıya dök.
     *
     * @param  string  $filePath  Local audio path
     * @param  array   $opts      ['language' => 'tr', 'model_id' => 'scribe_v1', 'diarize' => false]
     * @return array{text: string, language?: string, language_probability?: float, raw: array}|null
     */
    public function transcribe(string $filePath, array $opts = []): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }
        if (!file_exists($filePath)) {
            Log::warning('ElevenLabs transcribe: file not found', ['path' => $filePath]);
            return null;
        }

        $modelId = $opts['model_id']
            ?? config('services.elevenlabs.stt_model', 'scribe_v1');
        $language = $opts['language']
            ?? config('services.elevenlabs.stt_language', 'tr');

        try {
            $response = $this->client()
                ->timeout((int) config('services.elevenlabs.timeout', 120))
                ->asMultipart()
                ->attach('file', file_get_contents($filePath), basename($filePath))
                ->post($this->baseUrl . '/speech-to-text', [
                    ['name' => 'model_id', 'contents' => $modelId],
                    ['name' => 'language_code', 'contents' => $language],
                    ['name' => 'diarize',  'contents' => !empty($opts['diarize']) ? 'true' : 'false'],
                ]);

            if (!$response->successful()) {
                Log::warning('ElevenLabs STT failed', [
                    'status' => $response->status(),
                    'body'   => mb_substr($response->body(), 0, 500),
                ]);
                return null;
            }

            $data = $response->json() ?? [];
            return [
                'text'                 => (string) ($data['text'] ?? ''),
                'language'             => $data['language_code'] ?? $language,
                'language_probability' => $data['language_probability'] ?? null,
                'raw'                  => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('ElevenLabs STT exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Metni MP3 olarak seslendir, byte içeriğini döner.
     *
     * @return string|null  MP3 binary content; null on failure
     */
    public function textToSpeech(string $text, array $opts = []): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $voiceId = $opts['voice_id']
            ?? config('services.elevenlabs.default_voice_id');
        $modelId = $opts['model_id']
            ?? config('services.elevenlabs.tts_model', 'eleven_multilingual_v2');

        if (!$voiceId) {
            Log::warning('ElevenLabs TTS: voice_id missing');
            return null;
        }

        try {
            $response = $this->client()
                ->timeout((int) config('services.elevenlabs.timeout', 90))
                ->withHeaders(['Accept' => 'audio/mpeg'])
                ->post($this->baseUrl . '/text-to-speech/' . $voiceId, [
                    'text'     => $text,
                    'model_id' => $modelId,
                    'voice_settings' => [
                        'stability'        => $opts['stability']        ?? 0.5,
                        'similarity_boost' => $opts['similarity_boost'] ?? 0.75,
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('ElevenLabs TTS failed', [
                    'status' => $response->status(),
                    'body'   => mb_substr($response->body(), 0, 300),
                ]);
                return null;
            }

            return $response->body();
        } catch (\Throwable $e) {
            Log::error('ElevenLabs TTS exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    protected function client(): PendingRequest
    {
        return Http::withHeaders([
            'xi-api-key' => $this->apiKey,
        ]);
    }
}
