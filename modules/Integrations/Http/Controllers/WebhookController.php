<?php

namespace Modules\Integrations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Integrations\Connectors\WhatsAppConnector;
use Modules\Integrations\Connectors\CallConnector;
use Modules\Integrations\Connectors\PaymentConnector;

class WebhookController extends Controller
{
    /**
     * Verify WhatsApp webhook
     */
    public function verifyWhatsapp(Request $request)
    {
        $verifyToken = config('services.whatsapp.verify_token');
        
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Handle WhatsApp webhook
     */
    public function handleWhatsapp(Request $request)
    {
        Log::info('WhatsApp webhook received', $request->all());

        try {
            $connector = app(WhatsAppConnector::class);
            $connector->handleWebhook($request->all());
        } catch (\Exception $e) {
            Log::error('WhatsApp webhook error: ' . $e->getMessage());
        }

        return response('OK', 200);
    }

    /**
     * Handle Twilio voice webhook
     */
    public function handleTwilioVoice(Request $request)
    {
        Log::info('Twilio voice webhook', $request->all());

        $connector = app(CallConnector::class);
        $connector->handleIncomingCall($request->all());

        // Return TwiML response
        $response = new \Twilio\TwiML\VoiceResponse();
        $response->say('Merhaba, aramanız yönlendiriliyor.', ['language' => 'tr-TR']);
        $response->dial(config('services.twilio.forward_to'));

        return response($response, 200)->header('Content-Type', 'text/xml');
    }

    /**
     * Handle Twilio voice status webhook
     */
    public function handleTwilioVoiceStatus(Request $request)
    {
        Log::info('Twilio voice status', $request->all());

        if ($request->get('CallStatus') === 'completed') {
            $connector = app(CallConnector::class);
            $connector->handleCallEnded($request->all());
        }

        return response('OK', 200);
    }

    /**
     * Handle Twilio SMS webhook
     */
    public function handleTwilioSms(Request $request)
    {
        Log::info('Twilio SMS webhook', $request->all());

        $from = $request->get('From');
        $body = $request->get('Body');

        // Find contact and create activity
        $contact = \Modules\CRM\Models\Contact::where('phone', 'like', "%{$from}%")->first();

        if ($contact) {
            \Modules\CRM\Models\Activity::create([
                'contact_id' => $contact->id,
                'type' => 'sms',
                'subject' => 'SMS alındı',
                'description' => $body,
                'is_automated' => true,
                'metadata' => [
                    'from' => $from,
                    'message_sid' => $request->get('MessageSid'),
                ],
            ]);
        }

        return response('OK', 200);
    }

    /**
     * Handle Bulutfon call webhook
     */
    public function handleBulutfonCall(Request $request)
    {
        Log::info('Bulutfon call webhook', $request->all());

        $connector = app(CallConnector::class);
        $connector->handleIncomingCall($request->all());

        return response('OK', 200);
    }

    /**
     * Handle Bulutfon CDR webhook
     */
    public function handleBulutfonCdr(Request $request)
    {
        Log::info('Bulutfon CDR webhook', $request->all());

        $connector = app(CallConnector::class);
        $connector->handleCallEnded($request->all());

        return response('OK', 200);
    }

    /**
     * Handle PayTR callback
     */
    public function handlePayTRCallback(Request $request)
    {
        Log::info('PayTR callback', $request->all());

        $connector = app(PaymentConnector::class);
        
        if (!$connector->verifyCallback($request->all())) {
            return response('Invalid hash', 400);
        }

        $orderId = $request->get('merchant_oid');
        $status = $request->get('status');

        // Update payment status in database
        // Dispatch event for payment completed/failed

        return response('OK', 200);
    }

    /**
     * Handle Iyzico callback
     */
    public function handleIyzicoCallback(Request $request)
    {
        Log::info('Iyzico callback', $request->all());

        // Process Iyzico callback

        return response('OK', 200);
    }

    /**
     * Handle Sahibinden webhook
     */
    public function handleSahibindenWebhook(Request $request)
    {
        Log::info('Sahibinden webhook', $request->all());

        // Process portal webhook (lead notifications, etc.)

        return response('OK', 200);
    }

    /**
     * Handle Hepsiemlak webhook
     */
    public function handleHepsiemlakWebhook(Request $request)
    {
        Log::info('Hepsiemlak webhook', $request->all());

        return response('OK', 200);
    }

    /**
     * Handle Emlakjet webhook
     */
    public function handleEmlakjetWebhook(Request $request)
    {
        Log::info('Emlakjet webhook', $request->all());

        return response('OK', 200);
    }

    /**
     * Verify Meta webhook
     */
    public function verifyMeta(Request $request)
    {
        $verifyToken = config('services.meta.verify_token');
        
        $mode = $request->get('hub.mode');
        $token = $request->get('hub.verify_token');
        $challenge = $request->get('hub.challenge');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Handle Meta webhook (Lead Ads, etc.)
     */
    public function handleMeta(Request $request)
    {
        Log::info('Meta webhook', $request->all());

        $entries = $request->get('entry', []);

        foreach ($entries as $entry) {
            $changes = $entry['changes'] ?? [];
            
            foreach ($changes as $change) {
                if ($change['field'] === 'leadgen') {
                    $this->processMetaLead($change['value']);
                }
            }
        }

        return response('OK', 200);
    }

    /**
     * Process Meta lead
     */
    protected function processMetaLead(array $leadData)
    {
        $leadgenId = $leadData['leadgen_id'];
        $formId = $leadData['form_id'];
        $pageId = $leadData['page_id'];

        // Fetch lead details from Meta API
        $accessToken = config('services.meta.access_token');
        
        $response = \Http::get("https://graph.facebook.com/v18.0/{$leadgenId}", [
            'access_token' => $accessToken,
        ]);

        if (!$response->successful()) {
            Log::error('Failed to fetch Meta lead', ['leadgen_id' => $leadgenId]);
            return;
        }

        $leadDetails = $response->json();
        $fieldData = collect($leadDetails['field_data'] ?? []);

        // Create contact
        $contact = \Modules\CRM\Models\Contact::create([
            'first_name' => $fieldData->firstWhere('name', 'first_name')['values'][0] ?? '',
            'last_name' => $fieldData->firstWhere('name', 'last_name')['values'][0] ?? '',
            'email' => $fieldData->firstWhere('name', 'email')['values'][0] ?? '',
            'phone' => $fieldData->firstWhere('name', 'phone_number')['values'][0] ?? '',
            'source' => 'meta',
            'source_detail' => "Form: {$formId}",
        ]);

        // Create lead
        \Modules\CRM\Models\Lead::create([
            'contact_id' => $contact->id,
            'source' => 'meta',
            'source_detail' => "Facebook Lead Ad",
            'utm_source' => 'facebook',
            'utm_medium' => 'lead_ad',
            'utm_campaign' => $formId,
            'status' => 'new',
        ]);
    }
}
