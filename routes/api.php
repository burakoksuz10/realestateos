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
        Route::get('/dashboard/recent-activity', [\Modules\Core\Http\Controllers\Api\DashboardController::class, 'recentActivity']);

        // Offices
        Route::apiResource('offices', \Modules\Core\Http\Controllers\Api\OfficeController::class);
        Route::get('offices/{office}/users', [\Modules\Core\Http\Controllers\Api\OfficeController::class, 'users']);
        Route::get('offices/{office}/stats', [\Modules\Core\Http\Controllers\Api\OfficeController::class, 'stats']);

        // Teams
        Route::apiResource('teams', \Modules\Core\Http\Controllers\Api\TeamController::class);
        Route::get('teams/{team}/members', [\Modules\Core\Http\Controllers\Api\TeamController::class, 'members']);

        // Users
        Route::apiResource('users', \Modules\Core\Http\Controllers\Api\UserController::class);
        Route::get('users/{user}/performance', [\Modules\Core\Http\Controllers\Api\UserController::class, 'performance']);

        // Notifications
        Route::get('notifications', [\Modules\Core\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::get('notifications/unread-count', [\Modules\Core\Http\Controllers\Api\NotificationController::class, 'unreadCount']);
        Route::post('notifications/{id}/read', [\Modules\Core\Http\Controllers\Api\NotificationController::class, 'read']);
        Route::post('notifications/read-all', [\Modules\Core\Http\Controllers\Api\NotificationController::class, 'readAll']);

        // Listings
        Route::apiResource('listings', \Modules\RealEstate\Http\Controllers\Api\ListingController::class);
        Route::get('listings/{listing}/similar', [\Modules\RealEstate\Http\Controllers\Api\ListingController::class, 'similar']);
        Route::post('listings/{listing}/view', [\Modules\RealEstate\Http\Controllers\Api\ListingController::class, 'recordView']);
        Route::post('listings/{listing}/favorite', [\Modules\RealEstate\Http\Controllers\Api\ListingController::class, 'toggleFavorite']);
        Route::get('listings-stats', [\Modules\RealEstate\Http\Controllers\Api\ListingController::class, 'stats']);

        // Projects
        Route::apiResource('projects', \Modules\RealEstate\Http\Controllers\Api\ProjectController::class);
        Route::get('projects/{project}/listings', [\Modules\RealEstate\Http\Controllers\Api\ProjectController::class, 'listings']);
        Route::get('projects/{project}/stats', [\Modules\RealEstate\Http\Controllers\Api\ProjectController::class, 'stats']);

        // CRM - Leads
        Route::apiResource('leads', \Modules\CRM\Http\Controllers\Api\LeadController::class);

        // CRM - Deals
        Route::apiResource('deals', \Modules\CRM\Http\Controllers\Api\DealController::class);

        // CRM - Contacts
        Route::apiResource('contacts', \Modules\CRM\Http\Controllers\Api\ContactController::class);
    });
});

// Webhook Routes (handled by Integrations module)
