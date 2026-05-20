<?php

namespace Modules\Integrations\Connectors;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Activity;

class CallConnector
{
    protected string $provider;
    protected array $config;

    public function __construct()
    {
        $this->provider = config('reos.voice.provider', config('services.voip.provider', 'netgsm'));
        $this->config = config("services.{$this->provider}", []);
    }

    public function isConfigured(): bool
    {
        return match ($this->provider) {
            'netgsm'   => !empty($this->config['usercode']) && !empty($this->config['password']),
            'bulutfon' => !empty($this->config['api_key']),
            'twilio'   => !empty($this->config['sid']) && !empty($this->config['token']),
            default    => false,
        };
    }

    /**
     * Initiate outbound call
     */
    public function call(string $to, ?string $from = null): array
    {
        $from = $from ?? ($this->config['default_number'] ?? $this->config['sender_id'] ?? null);

        return match($this->provider) {
            'netgsm'   => $this->callViaNetgsm($to, $from),
            'bulutfon' => $this->callViaBulutfon($to, $from),
            'twilio'   => $this->callViaTwilio($to, $from),
            default    => throw new \Exception("Unknown VoIP provider: {$this->provider}"),
        };
    }

    /**
     * Netgsm sesli arama — pre-recorded ses dosyası ile arar.
     * Doğrudan TTS-driven canlı çağrı için Netgsm IVR API ayrı bir entegrasyon gerektirir;
     * şimdilik kayıtlı ses URL'si ile dial pattern.
     *
     * @param  string  $to        E.164 numara (905...)
     * @param  ?string $from      Caller ID (Netgsm onaylı)
     * @param  ?string $audioUrl  Çalınacak ses dosyası URL'si (null → varsayılan ofis mesajı)
     */
    protected function callViaNetgsm(string $to, ?string $from, ?string $audioUrl = null): array
    {
        $audioUrl = $audioUrl
            ?? $this->config['default_audio_url']
            ?? config('services.netgsm.default_audio_url');

        if (!$audioUrl) {
            throw new \Exception('Netgsm voice call requires an audio URL.');
        }

        $response = Http::asForm()
            ->timeout(30)
            ->post('https://api.netgsm.com.tr/voice/send/post/', [
                'usercode'  => $this->config['usercode'] ?? null,
                'password'  => $this->config['password'] ?? null,
                'gsmno'     => $to,
                'audio_url' => $audioUrl,
                'msgheader' => $from ?? ($this->config['sender_id'] ?? null),
            ]);

        $body = trim($response->body());
        // Netgsm "00 jobid" formatında döner — başarı kodları: 00, 01, 02
        $parts = preg_split('/\s+/', $body);
        $code  = $parts[0] ?? '';
        $jobId = $parts[1] ?? null;

        if (!in_array($code, ['00', '01', '02'], true)) {
            throw new \Exception("Netgsm voice error: {$body}");
        }

        return [
            'provider' => 'netgsm',
            'call_id'  => $jobId,
            'status'   => 'queued',
            'raw'      => $body,
        ];
    }

    /**
     * Call via Bulutfon
     */
    protected function callViaBulutfon(string $to, string $from): array
    {
        $response = Http::withToken($this->config['api_key'])
            ->post('https://api.bulutfon.com/calls', [
                'caller' => $from,
                'callee' => $to,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Bulutfon call failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Call via Twilio
     */
    protected function callViaTwilio(string $to, string $from): array
    {
        $sid = $this->config['sid'];
        $token = $this->config['token'];

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Calls.json", [
                'To' => $to,
                'From' => $from,
                'Url' => route('webhooks.twilio.voice'),
            ]);

        if (!$response->successful()) {
            throw new \Exception('Twilio call failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Handle incoming call webhook
     */
    public function handleIncomingCall(array $payload): void
    {
        $callerNumber = $payload['caller'] ?? $payload['From'] ?? null;
        $callId = $payload['call_id'] ?? $payload['CallSid'] ?? null;

        if (!$callerNumber) {
            return;
        }

        // Find contact
        $contact = Contact::where('phone', 'like', "%{$callerNumber}%")->first();

        // Create activity
        $activity = Activity::create([
            'contact_id' => $contact?->id,
            'type' => 'call',
            'subject' => 'Gelen arama',
            'description' => "Arayan: {$callerNumber}",
            'is_automated' => true,
            'metadata' => [
                'call_id' => $callId,
                'caller' => $callerNumber,
                'direction' => 'inbound',
                'status' => 'ringing',
            ],
        ]);

        // Trigger event
        event(new \Modules\Integrations\Events\CallReceived($contact, $payload, $activity));
    }

    /**
     * Handle call ended webhook
     */
    public function handleCallEnded(array $payload): void
    {
        $callId = $payload['call_id'] ?? $payload['CallSid'] ?? null;
        $duration = $payload['duration'] ?? $payload['CallDuration'] ?? 0;
        $recordingUrl = $payload['recording_url'] ?? $payload['RecordingUrl'] ?? null;

        // Find activity
        $activity = Activity::where('metadata->call_id', $callId)->first();

        if ($activity) {
            $activity->update([
                'call_duration' => $duration,
                'call_recording_url' => $recordingUrl,
                'completed_at' => now(),
                'metadata' => array_merge($activity->metadata ?? [], [
                    'status' => 'completed',
                    'duration' => $duration,
                ]),
            ]);

            // Trigger AI analysis if recording available
            if ($recordingUrl) {
                event(new \Modules\Integrations\Events\CallEnded($activity, $recordingUrl));
            }
        }
    }

    /**
     * Get call recording
     */
    public function getRecording(string $callId): ?string
    {
        return match($this->provider) {
            'netgsm'   => $this->getNetgsmRecording($callId),
            'bulutfon' => $this->getBulutfonRecording($callId),
            'twilio'   => $this->getTwilioRecording($callId),
            default    => null,
        };
    }

    protected function getNetgsmRecording(string $callId): ?string
    {
        // Netgsm voice job report endpoint — recording URL job rapor cevabında döner.
        $response = Http::asForm()
            ->timeout(15)
            ->post('https://api.netgsm.com.tr/voice/report/get/', [
                'usercode' => $this->config['usercode'] ?? null,
                'password' => $this->config['password'] ?? null,
                'jobid'    => $callId,
            ]);

        if (!$response->successful()) {
            return null;
        }

        // Netgsm XML/JSON döner — basit regex ile URL ayıkla
        $body = $response->body();
        if (preg_match('#<recording[^>]*>([^<]+)</recording>#i', $body, $m)) {
            return $m[1];
        }
        $data = $response->json();
        return $data['recording_url'] ?? null;
    }

    protected function getBulutfonRecording(string $callId): ?string
    {
        $response = Http::withToken($this->config['api_key'])
            ->get("https://api.bulutfon.com/calls/{$callId}/recording");

        return $response->successful() ? $response->json()['url'] ?? null : null;
    }

    protected function getTwilioRecording(string $callId): ?string
    {
        $sid = $this->config['sid'];
        $token = $this->config['token'];

        $response = Http::withBasicAuth($sid, $token)
            ->get("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Calls/{$callId}/Recordings.json");

        $recordings = $response->json()['recordings'] ?? [];
        
        return !empty($recordings) 
            ? "https://api.twilio.com{$recordings[0]['uri']}" 
            : null;
    }

    /**
     * Transcribe call recording — provider-aware (ElevenLabs default, Whisper fallback).
     */
    public function transcribe(string $recordingUrl): ?string
    {
        $service = app(\Modules\CRM\Services\CallTranscriptionService::class);
        $result = $service->fromUrl($recordingUrl);
        return $result['text'] ?? null;
    }

    /**
     * Get call logs
     */
    public function getCallLogs(array $filters = []): array
    {
        return match($this->provider) {
            'bulutfon' => $this->getBulutfonLogs($filters),
            'twilio' => $this->getTwilioLogs($filters),
            default => [],
        };
    }

    protected function getBulutfonLogs(array $filters): array
    {
        $response = Http::withToken($this->config['api_key'])
            ->get('https://api.bulutfon.com/cdrs', $filters);

        return $response->json()['cdrs'] ?? [];
    }

    protected function getTwilioLogs(array $filters): array
    {
        $sid = $this->config['sid'];
        $token = $this->config['token'];

        $response = Http::withBasicAuth($sid, $token)
            ->get("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Calls.json", $filters);

        return $response->json()['calls'] ?? [];
    }
}
