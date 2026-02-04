<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\DashboardController;
use Modules\Core\Http\Controllers\OfficeController;
use Modules\Core\Http\Controllers\TeamController;
use Modules\Core\Http\Controllers\UserController;
use Modules\Core\Http\Controllers\RoleController;
use Modules\Core\Http\Controllers\NotificationController;
use Modules\Core\Http\Controllers\SettingsController;
use Modules\Core\Http\Controllers\AuditLogController;

/*
|--------------------------------------------------------------------------
| Core Module Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

// Offices
Route::resource('offices', OfficeController::class);
Route::post('offices/{office}/toggle-status', [OfficeController::class, 'toggleStatus'])->name('offices.toggle-status');

// Teams
Route::resource('teams', TeamController::class);
Route::post('teams/{team}/add-member', [TeamController::class, 'addMember'])->name('teams.add-member');
Route::delete('teams/{team}/remove-member/{user}', [TeamController::class, 'removeMember'])->name('teams.remove-member');

// Users
Route::resource('users', UserController::class);
Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
Route::get('users/{user}/activity', [UserController::class, 'activity'])->name('users.activity');

// Roles & Permissions
Route::resource('roles', RoleController::class);
Route::post('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions');

// Notifications
Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

// Settings
Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
Route::get('settings/profile', [SettingsController::class, 'profile'])->name('settings.profile');
Route::put('settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
Route::put('settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');

// Audit Logs
Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
Route::get('audit-logs/export', [AuditLogController::class, 'export'])->name('audit-logs.export');
