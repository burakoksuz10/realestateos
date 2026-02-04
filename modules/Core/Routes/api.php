<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Api\DashboardController;
use Modules\Core\Http\Controllers\Api\OfficeController;
use Modules\Core\Http\Controllers\Api\TeamController;
use Modules\Core\Http\Controllers\Api\UserController;
use Modules\Core\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| Core Module API Routes
|--------------------------------------------------------------------------
*/

// Dashboard Stats
Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
Route::get('/dashboard/charts', [DashboardController::class, 'charts']);
Route::get('/dashboard/recent-activity', [DashboardController::class, 'recentActivity']);

// Offices
Route::apiResource('offices', OfficeController::class);
Route::get('/offices/{office}/users', [OfficeController::class, 'users']);
Route::get('/offices/{office}/stats', [OfficeController::class, 'stats']);

// Teams
Route::apiResource('teams', TeamController::class);
Route::get('/teams/{team}/members', [TeamController::class, 'members']);

// Users
Route::apiResource('users', UserController::class);
Route::get('/users/{user}/performance', [UserController::class, 'performance']);

// Notifications
Route::get('/notifications', [NotificationController::class, 'index']);
Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

// Search
Route::get('/search', function (\Illuminate\Http\Request $request) {
    $query = $request->get('q');
    
    // Global search across all modules
    $results = [
        'listings' => \Modules\RealEstate\Models\Listing::search($query)->take(5)->get(),
        'contacts' => \Modules\CRM\Models\Contact::search($query)->take(5)->get(),
        'leads' => \Modules\CRM\Models\Lead::search($query)->take(5)->get(),
    ];
    
    return response()->json($results);
});
