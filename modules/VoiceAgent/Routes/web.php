<?php

use Illuminate\Support\Facades\Route;
use Modules\VoiceAgent\Http\Controllers\VoiceAgentController;

Route::get('/',  [VoiceAgentController::class, 'index'])->name('index');
Route::put('/', [VoiceAgentController::class, 'update'])->name('update');
