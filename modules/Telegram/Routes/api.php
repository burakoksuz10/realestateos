<?php

use Illuminate\Support\Facades\Route;
use Modules\Telegram\Http\Controllers\WebhookController;

// Telegram POSTs updates here; no CSRF — public route by design.
Route::post('webhook', [WebhookController::class, 'handle'])->name('webhook');
