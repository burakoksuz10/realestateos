<?php

namespace Modules\Integrations\Connectors;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\Activity;

class WhatsAppConnector
{
    protected string $apiUrl;
    protected string $accessToken;
    protected string $phoneNumberId;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url', 'https://graph.facebook.com/v18.0');
        $this->accessToken = config('services.whatsapp.access_token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
    }

    /**
     * Send text message
     */
    public function sendMessage(string $to, string $message, ?Contact $contact = null): array
    {
        $to = $this->formatPhoneNumber($to);

        $response = Http::withToken($this->accessToken)
            ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $to,
                'type' => 'text',
                'text' => [
                    'preview_url' => true,
                    'body' => $message,
                ],
            ]);

        $result = $response->json();

        // Log activity
        if ($contact) {
            Activity::create([
                'user_id' => auth()->id(),
                'contact_id' => $contact->id,
                'type' => 'whatsapp',
                'subject' => 'WhatsApp mesajı gönderildi',
                'description' => $message,
                'metadata' => [
                    'message_id' => $result['messages'][0]['id'] ?? null,
                    'status' => $response->successful() ? 'sent' : 'failed',
                ],
            ]);
        }

        if (!$response->successful()) {
            Log::error('WhatsApp send failed', ['response' => $result]);
            throw new \Exception($result['error']['message'] ?? 'WhatsApp send failed');
        }

        return $result;
    }

    /**
     * Send template message
     */
    public function sendTemplate(string $to, string $templateName, array $parameters = [], ?string $language = 'tr'): array
    {
        $to = $this->formatPhoneNumber($to);

        $components = [];
        if (!empty($parameters)) {
            $components[] = [
                'type' => 'body',
                'parameters' => array_map(fn($p) => ['type' => 'text', 'text' => $p], $parameters),
            ];
        }

        $response = Http::withToken($this->accessToken)
            ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => ['code' => $language],
                    'components' => $components,
                ],
            ]);

        if (!$response->successful()) {
            throw new \Exception($response->json()['error']['message'] ?? 'Template send failed');
        }

        return $response->json();
    }

    /**
     * Send media message
     */
    public function sendMedia(string $to, string $type, string $mediaUrl, ?string $caption = null): array
    {
        $to = $this->formatPhoneNumber($to);

        $mediaPayload = [
            'link' => $mediaUrl,
        ];

        if ($caption && in_array($type, ['image', 'video', 'document'])) {
            $mediaPayload['caption'] = $caption;
        }

        $response = Http::withToken($this->accessToken)
            ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => $type,
                $type => $mediaPayload,
            ]);

        if (!$response->successful()) {
            throw new \Exception($response->json()['error']['message'] ?? 'Media send failed');
        }

        return $response->json();
    }

    /**
     * Send listing card
     */
    public function sendListingCard(string $to, \Modules\RealEstate\Models\Listing $listing): array
    {
        $message = "🏠 *{$listing->title}*\n\n";
        $message .= "📍 {$listing->full_location}\n";
        $message .= "💰 {$listing->formatted_price}\n";
        $message .= "📐 {$listing->gross_sqm} m²\n";
        $message .= "🛏️ {$listing->room_info}\n\n";
        $message .= "🔗 Detaylar için: " . route('listings.show', $listing);

        // Send image first if available
        $photo = $listing->getFirstMediaUrl('photos');
        if ($photo) {
            $this->sendMedia($to, 'image', $photo, $message);
            return ['status' => 'sent_with_image'];
        }

        return $this->sendMessage($to, $message);
    }

    /**
     * Handle incoming webhook
     */
    public function handleWebhook(array $payload): void
    {
        $entries = $payload['entry'] ?? [];

        foreach ($entries as $entry) {
            $changes = $entry['changes'] ?? [];
            
            foreach ($changes as $change) {
                if ($change['field'] === 'messages') {
                    $this->processMessages($change['value']);
                }
            }
        }
    }

    /**
     * Process incoming messages
     */
    protected function processMessages(array $value): void
    {
        $messages = $value['messages'] ?? [];
        $contacts = $value['contacts'] ?? [];

        foreach ($messages as $message) {
            $from = $message['from'];
            $messageId = $message['id'];
            $timestamp = $message['timestamp'];
            $type = $message['type'];
            $text = $message['text']['body'] ?? null;

            // Find or create contact
            $contact = Contact::where('phone', 'like', "%{$from}%")
                ->orWhere('whatsapp', 'like', "%{$from}%")
                ->first();

            if (!$contact && !empty($contacts)) {
                $waContact = $contacts[0];
                $contact = Contact::create([
                    'first_name' => $waContact['profile']['name'] ?? 'WhatsApp User',
                    'phone' => $from,
                    'whatsapp' => $from,
                    'source' => 'whatsapp',
                ]);
            }

            // Log activity
            if ($contact) {
                Activity::create([
                    'contact_id' => $contact->id,
                    'type' => 'whatsapp',
                    'subject' => 'WhatsApp mesajı alındı',
                    'description' => $text,
                    'is_automated' => true,
                    'metadata' => [
                        'message_id' => $messageId,
                        'message_type' => $type,
                        'timestamp' => $timestamp,
                    ],
                ]);

                // Trigger AI analysis
                event(new \Modules\Integrations\Events\WhatsAppMessageReceived($contact, $message));
            }
        }
    }

    /**
     * Format phone number for WhatsApp
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add Turkey country code if not present
        if (strlen($phone) === 10 && str_starts_with($phone, '5')) {
            $phone = '90' . $phone;
        }

        return $phone;
    }

    /**
     * Mark message as read
     */
    public function markAsRead(string $messageId): bool
    {
        $response = Http::withToken($this->accessToken)
            ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'status' => 'read',
                'message_id' => $messageId,
            ]);

        return $response->successful();
    }
}
