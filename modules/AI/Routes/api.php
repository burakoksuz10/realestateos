<?php

use Illuminate\Support\Facades\Route;
use Modules\AI\Http\Controllers\Api\ValuationController;
use Modules\AI\Http\Controllers\Api\ContentController;
use Modules\AI\Http\Controllers\Api\CopilotController;
use Modules\AI\Http\Controllers\Api\MatchingController;

/*
|--------------------------------------------------------------------------
| AI Module API Routes
|--------------------------------------------------------------------------
*/

// Valuation
Route::post('valuation/{listing}', [ValuationController::class, 'generate']);
Route::get('valuation/{listing}', [ValuationController::class, 'show']);
Route::get('valuation/{listing}/comparables', [ValuationController::class, 'comparables']);
Route::get('valuation/{listing}/trends', [ValuationController::class, 'trends']);

// Content Generation
Route::post('content/description', [ContentController::class, 'generateDescription']);
Route::post('content/social', [ContentController::class, 'generateSocial']);
Route::post('content/ads', [ContentController::class, 'generateAds']);
Route::post('content/headlines', [ContentController::class, 'generateHeadlines']);
Route::post('content/seo', [ContentController::class, 'generateSEO']);
Route::post('content/improve', [ContentController::class, 'improve']);

// Copilot
Route::post('copilot/lead-suggestions/{lead}', [CopilotController::class, 'leadSuggestions']);
Route::post('copilot/analyze-call', [CopilotController::class, 'analyzeCall']);
Route::post('copilot/analyze-message', [CopilotController::class, 'analyzeMessage']);
Route::post('copilot/suggest-response', [CopilotController::class, 'suggestResponse']);
Route::post('copilot/next-actions/{lead}', [CopilotController::class, 'nextActions']);
Route::post('copilot/chat', [CopilotController::class, 'chat']);

// Matching
Route::get('matching/listings/{lead}', [MatchingController::class, 'matchingListings']);
Route::get('matching/leads/{listing}', [MatchingController::class, 'matchingLeads']);
Route::get('matching/similar/{listing}', [MatchingController::class, 'similarListings']);
Route::post('matching/search', [MatchingController::class, 'semanticSearch']);

// Translation
Route::post('translate', function (\Illuminate\Http\Request $request) {
    $service = app(\Modules\AI\Services\TranslationService::class);
    
    return response()->json([
        'translation' => $service->translate(
            $request->text,
            $request->target_language,
            $request->source_language ?? 'tr'
        )
    ]);
});
