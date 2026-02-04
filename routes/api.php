<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public API Routes
Route::prefix('v1')->group(function () {
    // Health Check
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'version' => config('reos.version'),
        ]);
    });

    // Authentication
    Route::post('/auth/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('/auth/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
    Route::post('/auth/forgot-password', [\App\Http\Controllers\Api\AuthController::class, 'forgotPassword']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::get('/auth/user', function (Request $request) {
            return $request->user()->load(['office', 'team', 'roles']);
        });
        Route::post('/auth/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
        Route::put('/auth/profile', [\App\Http\Controllers\Api\AuthController::class, 'updateProfile']);
        Route::put('/auth/password', [\App\Http\Controllers\Api\AuthController::class, 'updatePassword']);

        // Dashboard
        Route::get('/dashboard/stats', [\Modules\Core\Http\Controllers\Api\DashboardController::class, 'stats']);
        Route::get('/dashboard/charts', [\Modules\Core\Http\Controllers\Api\DashboardController::class, 'charts']);
    });
});

// Webhook Routes (handled by Integrations module)
