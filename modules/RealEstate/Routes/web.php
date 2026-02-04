<?php

use Illuminate\Support\Facades\Route;
use Modules\RealEstate\Http\Controllers\ListingController;
use Modules\RealEstate\Http\Controllers\ProjectController;
use Modules\RealEstate\Http\Controllers\MediaController;
use Modules\RealEstate\Http\Controllers\PortalSyncController;

/*
|--------------------------------------------------------------------------
| RealEstate Module Web Routes
|--------------------------------------------------------------------------
*/

// Listings
Route::resource('listings', ListingController::class);
Route::post('listings/{listing}/publish', [ListingController::class, 'publish'])->name('listings.publish');
Route::post('listings/{listing}/unpublish', [ListingController::class, 'unpublish'])->name('listings.unpublish');
Route::post('listings/{listing}/mark-sold', [ListingController::class, 'markAsSold'])->name('listings.mark-sold');
Route::post('listings/{listing}/duplicate', [ListingController::class, 'duplicate'])->name('listings.duplicate');
Route::get('listings/{listing}/brochure', [ListingController::class, 'generateBrochure'])->name('listings.brochure');
Route::post('listings/{listing}/restore-version/{version}', [ListingController::class, 'restoreVersion'])->name('listings.restore-version');

// Projects
Route::resource('projects', ProjectController::class);
Route::post('projects/{project}/toggle-featured', [ProjectController::class, 'toggleFeatured'])->name('projects.toggle-featured');

// Media Management
Route::post('listings/{listing}/media', [MediaController::class, 'upload'])->name('listings.media.upload');
Route::delete('listings/{listing}/media/{media}', [MediaController::class, 'destroy'])->name('listings.media.destroy');
Route::post('listings/{listing}/media/reorder', [MediaController::class, 'reorder'])->name('listings.media.reorder');

// Portal Sync
Route::get('portal-sync', [PortalSyncController::class, 'index'])->name('portal-sync.index');
Route::post('portal-sync/{listing}/sync', [PortalSyncController::class, 'sync'])->name('portal-sync.sync');
Route::post('portal-sync/{listing}/sync-all', [PortalSyncController::class, 'syncAll'])->name('portal-sync.sync-all');
Route::get('portal-sync/logs', [PortalSyncController::class, 'logs'])->name('portal-sync.logs');
