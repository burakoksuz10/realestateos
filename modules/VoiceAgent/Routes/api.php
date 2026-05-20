<?php

use Illuminate\Support\Facades\Route;
use Modules\VoiceAgent\Http\Controllers\ToolController;
use Modules\VoiceAgent\Http\Controllers\WebhookController;

Route::prefix('tools')->group(function () {
    Route::post('search-listing',   [ToolController::class, 'searchListing'])->name('tools.search-listing');
    Route::post('create-lead',      [ToolController::class, 'createLead'])->name('tools.create-lead');
    Route::post('request-transfer', [ToolController::class, 'requestTransfer'])->name('tools.request-transfer');
    Route::post('pre-call-brief',   [ToolController::class, 'preCallBrief'])->name('tools.pre-call-brief');
    Route::post('book-callback',    [ToolController::class, 'bookCallback'])->name('tools.book-callback');
});

Route::post('webhook', [WebhookController::class, 'handle'])->name('webhook');
