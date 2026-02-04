<?php

use Illuminate\Support\Facades\Route;
use Modules\Integrations\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Webhook Routes (No Authentication)
|--------------------------------------------------------------------------
*/

// WhatsApp Webhooks
Route::get('whatsapp', [WebhookController::class, 'verifyWhatsapp'])->name('whatsapp.verify');
Route::post('whatsapp', [WebhookController::class, 'handleWhatsapp'])->name('whatsapp.handle');

// Twilio Webhooks
Route::post('twilio/voice', [WebhookController::class, 'handleTwilioVoice'])->name('twilio.voice');
Route::post('twilio/voice/status', [WebhookController::class, 'handleTwilioVoiceStatus'])->name('twilio.voice.status');
Route::post('twilio/sms', [WebhookController::class, 'handleTwilioSms'])->name('twilio.sms');

// Bulutfon Webhooks
Route::post('bulutfon/call', [WebhookController::class, 'handleBulutfonCall'])->name('bulutfon.call');
Route::post('bulutfon/cdr', [WebhookController::class, 'handleBulutfonCdr'])->name('bulutfon.cdr');

// Payment Webhooks
Route::post('paytr/callback', [WebhookController::class, 'handlePayTRCallback'])->name('paytr.callback');
Route::post('iyzico/callback', [WebhookController::class, 'handleIyzicoCallback'])->name('iyzico.callback');

// Portal Webhooks
Route::post('sahibinden', [WebhookController::class, 'handleSahibindenWebhook'])->name('sahibinden');
Route::post('hepsiemlak', [WebhookController::class, 'handleHepsiemlakWebhook'])->name('hepsiemlak');
Route::post('emlakjet', [WebhookController::class, 'handleEmlakjetWebhook'])->name('emlakjet');

// Meta (Facebook/Instagram) Webhooks
Route::get('meta', [WebhookController::class, 'verifyMeta'])->name('meta.verify');
Route::post('meta', [WebhookController::class, 'handleMeta'])->name('meta.handle');
