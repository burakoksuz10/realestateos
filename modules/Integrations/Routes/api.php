<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Integrations API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for the Integrations module.
|
*/

Route::prefix('portals')->name('portals.')->group(function () {
    Route::get('/', function () {
        return response()->json(['message' => 'Portals API']);
    })->name('index');
    
    Route::post('/sync', function () {
        return response()->json(['message' => 'Portal sync initiated']);
    })->name('sync');
});

Route::prefix('sms')->name('sms.')->group(function () {
    Route::post('/send', function () {
        return response()->json(['message' => 'SMS API']);
    })->name('send');
});

Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
    Route::post('/send', function () {
        return response()->json(['message' => 'WhatsApp API']);
    })->name('send');
});

Route::prefix('payments')->name('payments.')->group(function () {
    Route::get('/', function () {
        return response()->json(['message' => 'Payments API']);
    })->name('index');
});
