<?php

use Illuminate\Support\Facades\Route;
use Modules\BI\Http\Controllers\ReportController;
use Modules\BI\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| BI Module Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('index');

// Reports
Route::get('/conversion-funnel', [ReportController::class, 'conversionFunnel'])->name('conversion-funnel');
Route::get('/agent-performance', [ReportController::class, 'agentPerformance'])->name('agent-performance');
Route::get('/lead-sources', [ReportController::class, 'leadSources'])->name('lead-sources');
Route::get('/portal-performance', [ReportController::class, 'portalPerformance'])->name('portal-performance');
Route::get('/listing-performance', [ReportController::class, 'listingPerformance'])->name('listing-performance');
Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');

// Export
Route::get('/export/{report}', [ReportController::class, 'export'])->name('export');
