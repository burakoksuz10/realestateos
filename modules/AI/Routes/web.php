<?php

use Illuminate\Support\Facades\Route;
use Modules\AI\Http\Controllers\ValuationController;
use Modules\AI\Http\Controllers\ContentController;
use Modules\AI\Http\Controllers\CopilotController;
use Modules\AI\Http\Controllers\TranslationController;
use Modules\AI\Http\Controllers\NewsController;

/*
|--------------------------------------------------------------------------
| AI Module Web Routes
|--------------------------------------------------------------------------
*/

// Valuation
Route::get('valuation', [ValuationController::class, 'index'])->name('valuation.index');
Route::get('valuation/{listing}', [ValuationController::class, 'show'])->name('valuation.show');
Route::post('valuation/{listing}/generate', [ValuationController::class, 'generate'])->name('valuation.generate');
Route::get('valuation/{listing}/report', [ValuationController::class, 'downloadReport'])->name('valuation.report');

// Content Generation
Route::get('content', [ContentController::class, 'index'])->name('content.index');
Route::post('content/description/{listing}', [ContentController::class, 'generateDescription'])->name('content.description');
Route::post('content/social/{listing}', [ContentController::class, 'generateSocial'])->name('content.social');
Route::post('content/ads/{listing}', [ContentController::class, 'generateAds'])->name('content.ads');
Route::post('content/headlines/{listing}', [ContentController::class, 'generateHeadlines'])->name('content.headlines');
Route::post('content/improve', [ContentController::class, 'improveText'])->name('content.improve');

// Copilot
Route::get('copilot', [CopilotController::class, 'index'])->name('copilot.index');
Route::get('copilot/lead/{lead}', [CopilotController::class, 'leadSuggestions'])->name('copilot.lead');
Route::post('copilot/analyze-call', [CopilotController::class, 'analyzeCall'])->name('copilot.analyze-call');
Route::get('copilot/appointments/{lead}', [CopilotController::class, 'suggestAppointments'])->name('copilot.appointments');
Route::post('copilot/chat', [CopilotController::class, 'chat'])->name('copilot.chat');

// Translation
Route::post('translate', [TranslationController::class, 'translate'])->name('translate');
Route::post('translate/listing/{listing}', [TranslationController::class, 'translateListing'])->name('translate.listing');
Route::post('translate/detect', [TranslationController::class, 'detectLanguage'])->name('translate.detect');

// News
Route::get('news', [NewsController::class, 'index'])->name('news.index');
Route::post('news/fetch', [NewsController::class, 'fetch'])->name('news.fetch');
Route::delete('news/{article}', [NewsController::class, 'destroy'])->name('news.destroy');
