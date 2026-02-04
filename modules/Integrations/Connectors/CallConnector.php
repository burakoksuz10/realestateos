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
        $this->provider = config('services.voip.provider', 'bulutfon');
        $this->config = config("services.{$this->provider}", []);
    }

    /**
     * Initiate outbound call
     */
    public function call(string $to, string $from = null): array
    {
        $from = $from ?? $this->config['default_number'];

        return match($this->provider) {
            'bulutfon' => $this->callViaBulutfon($to, $from),
            'twilio' => $this->callViaTwilio($to, $from),
            default => throw new \Exception("Unknown VoIP provider: {$this->provider}"),
        };
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
            'bulutfon' => $this->getBulutfonRecording($callId),
            'twilio' => $this->getTwilioRecording($callId),
            default => null,
        };
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
     * Transcribe call recording
     */
    public function transcribe(string $recordingUrl): ?string
    {
        // Use OpenAI Whisper for transcription
        try {
            $audioContent = Http::get($recordingUrl)->body();
            
            $response = Http::withToken(config('services.openai.api_key'))
                ->attach('file', $audioContent, 'recording.mp3')
                ->post('https://api.openai.com/v1/audio/transcriptions', [
                    'model' => 'whisper-1',
                    'language' => 'tr',
                ]);

            return $response->json()['text'] ?? null;
        } catch (\Exception $e) {
            Log::error('Transcription failed: ' . $e->getMessage());
            return null;
        }
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
