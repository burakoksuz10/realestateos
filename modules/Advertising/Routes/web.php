<?php

use Illuminate\Support\Facades\Route;
use Modules\Advertising\Http\Controllers\CampaignController;

Route::get('/', [CampaignController::class, 'index'])->name('index');
Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
Route::put('/campaigns/{campaign}', [CampaignController::class, 'update'])->name('campaigns.update');
Route::delete('/campaigns/{campaign}', [CampaignController::class, 'destroy'])->name('campaigns.destroy');
Route::post('/campaigns/{campaign}/toggle', [CampaignController::class, 'toggle'])->name('campaigns.toggle');
Route::post('/campaigns/{campaign}/analyze', [CampaignController::class, 'analyze'])->name('campaigns.analyze');
Route::post('/sync', [CampaignController::class, 'sync'])->name('sync');
