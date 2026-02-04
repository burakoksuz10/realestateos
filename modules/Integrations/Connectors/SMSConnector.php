<?php

namespace Modules\Integrations\Connectors;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\Activity;

class SMSConnector
{
    protected string $provider;
    protected array $config;

    public function __construct()
    {
        $this->provider = config('reos.marketing.sms.provider', 'netgsm');
        $this->config = config("services.{$this->provider}", []);
    }

    /**
     * Send SMS
     */
    public function send(string $to, string $message, ?Contact $contact = null): array
    {
        $to = $this->formatPhoneNumber($to);

        $result = match($this->provider) {
            'netgsm' => $this->sendViaNetgsm($to, $message),
            'iletimerkezi' => $this->sendViaIletiMerkezi($to, $message),
            'twilio' => $this->sendViaTwilio($to, $message),
            default => throw new \Exception("Unknown SMS provider: {$this->provider}"),
        };

        // Log activity
        if ($contact) {
            Activity::create([
                'user_id' => auth()->id(),
                'contact_id' => $contact->id,
                'type' => 'sms',
                'subject' => 'SMS gönderildi',
                'description' => $message,
                'metadata' => [
                    'provider' => $this->provider,
                    'message_id' => $result['message_id'] ?? null,
                    'status' => $result['status'] ?? 'sent',
                ],
            ]);
        }

        return $result;
    }

    /**
     * Send bulk SMS
     */
    public function sendBulk(array $recipients, string $message): array
    {
        $results = [];

        foreach ($recipients as $recipient) {
            $phone = is_array($recipient) ? $recipient['phone'] : $recipient;
            $contact = is_array($recipient) ? ($recipient['contact'] ?? null) : null;

            try {
                $results[] = [
                    'phone' => $phone,
                    'status' => 'success',
                    'result' => $this->send($phone, $message, $contact),
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'phone' => $phone,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Send via Netgsm
     */
    protected function sendViaNetgsm(string $to, string $message): array
    {
        $response = Http::get('https://api.netgsm.com.tr/sms/send/get', [
            'usercode' => $this->config['usercode'],
            'password' => $this->config['password'],
            'gsmno' => $to,
            'message' => $message,
            'msgheader' => $this->config['sender_id'],
        ]);

        $code = trim($response->body());

        if (!in_array($code, ['00', '01', '02'])) {
            throw new \Exception("Netgsm error: {$code}");
        }

        return [
            'status' => 'sent',
            'provider' => 'netgsm',
            'response_code' => $code,
        ];
    }

    /**
     * Send via Ileti Merkezi
     */
    protected function sendViaIletiMerkezi(string $to, string $message): array
    {
        $response = Http::withBasicAuth($this->config['api_key'], $this->config['api_secret'])
            ->post('https://api.iletimerkezi.com/v1/send-sms', [
                'request' => [
                    'authentication' => [
                        'key' => $this->config['api_key'],
                        'hash' => $this->config['api_secret'],
                    ],
                    'order' => [
                        'sender' => $this->config['sender_id'],
                        'message' => [
                            'text' => $message,
                            'receipents' => [
                                'number' => [$to],
                            ],
                        ],
                    ],
                ],
            ]);

        if (!$response->successful()) {
            throw new \Exception('Ileti Merkezi error: ' . $response->body());
        }

        return [
            'status' => 'sent',
            'provider' => 'iletimerkezi',
            'response' => $response->json(),
        ];
    }

    /**
     * Send via Twilio
     */
    protected function sendViaTwilio(string $to, string $message): array
    {
        $sid = $this->config['sid'];
        $token = $this->config['token'];
        $from = $this->config['from'];

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To' => "+{$to}",
                'From' => $from,
                'Body' => $message,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Twilio error: ' . $response->body());
        }

        $data = $response->json();

        return [
            'status' => 'sent',
            'provider' => 'twilio',
            'message_id' => $data['sid'],
        ];
    }

    /**
     * Format phone number
     */
    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add Turkey country code if needed
        if (strlen($phone) === 10 && str_starts_with($phone, '5')) {
            $phone = '90' . $phone;
        }

        return $phone;
    }

    /**
     * Get SMS balance/credits
     */
    public function getBalance(): array
    {
        return match($this->provider) {
            'netgsm' => $this->getNetgsmBalance(),
            'twilio' => $this->getTwilioBalance(),
            default => ['balance' => 'unknown'],
        };
    }

    protected function getNetgsmBalance(): array
    {
        $response = Http::get('https://api.netgsm.com.tr/balance', [
            'usercode' => $this->config['usercode'],
            'password' => $this->config['password'],
        ]);

        return [
            'balance' => trim($response->body()),
            'provider' => 'netgsm',
        ];
    }

    protected function getTwilioBalance(): array
    {
        $sid = $this->config['sid'];
        $token = $this->config['token'];

        $response = Http::withBasicAuth($sid, $token)
            ->get("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Balance.json");

        return [
            'balance' => $response->json()['balance'] ?? 'unknown',
            'currency' => $response->json()['currency'] ?? 'USD',
            'provider' => 'twilio',
        ];
    }
}
