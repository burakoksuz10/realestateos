<?php

use Illuminate\Support\Facades\Route;
use Modules\Integrations\Http\Controllers\IntegrationController;
use Modules\Integrations\Http\Controllers\PortalController;
use Modules\Integrations\Http\Controllers\CommunicationController;

/*
|--------------------------------------------------------------------------
| Integrations Module Web Routes
|--------------------------------------------------------------------------
*/

// Integration Settings
Route::get('/', [IntegrationController::class, 'index'])->name('index');
Route::get('/{integration}', [IntegrationController::class, 'show'])->name('show');
Route::put('/{integration}', [IntegrationController::class, 'update'])->name('update');
Route::post('/{integration}/test', [IntegrationController::class, 'test'])->name('test');

// Portal Sync
Route::prefix('portals')->name('portals.')->group(function () {
    Route::get('/', [PortalController::class, 'index'])->name('index');
    Route::get('/logs', [PortalController::class, 'logs'])->name('logs');
    Route::post('/sync/{listing}', [PortalController::class, 'sync'])->name('sync');
    Route::post('/sync-all/{listing}', [PortalController::class, 'syncAll'])->name('sync-all');
    Route::delete('/remove/{listing}/{portal}', [PortalController::class, 'remove'])->name('remove');
});

// Communication
Route::prefix('communication')->name('communication.')->group(function () {
    Route::get('/sms', [CommunicationController::class, 'smsIndex'])->name('sms.index');
    Route::post('/sms/send', [CommunicationController::class, 'sendSms'])->name('sms.send');
    Route::post('/sms/bulk', [CommunicationController::class, 'sendBulkSms'])->name('sms.bulk');
    
    Route::get('/whatsapp', [CommunicationController::class, 'whatsappIndex'])->name('whatsapp.index');
    Route::post('/whatsapp/send', [CommunicationController::class, 'sendWhatsapp'])->name('whatsapp.send');
    Route::post('/whatsapp/template', [CommunicationController::class, 'sendWhatsappTemplate'])->name('whatsapp.template');
    
    Route::get('/calls', [CommunicationController::class, 'callsIndex'])->name('calls.index');
    Route::post('/calls/initiate', [CommunicationController::class, 'initiateCall'])->name('calls.initiate');
    Route::get('/calls/{activity}/recording', [CommunicationController::class, 'getRecording'])->name('calls.recording');
    Route::post('/calls/{activity}/transcribe', [CommunicationController::class, 'transcribeCall'])->name('calls.transcribe');
});
