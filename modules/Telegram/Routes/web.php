<?php

use Illuminate\Support\Facades\Route;
use Modules\Telegram\Http\Controllers\TelegramController;

Route::get('/', [TelegramController::class, 'index'])->name('index');
Route::post('pair', [TelegramController::class, 'pair'])->name('pair');
Route::delete('unlink/{id}', [TelegramController::class, 'unlink'])->name('unlink');
Route::post('webhook/set', [TelegramController::class, 'setWebhook'])->name('webhook.set');
