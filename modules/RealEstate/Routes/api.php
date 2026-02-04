<?php

use Illuminate\Support\Facades\Route;
use Modules\RealEstate\Http\Controllers\Api\ListingController;
use Modules\RealEstate\Http\Controllers\Api\ProjectController;
use Modules\RealEstate\Http\Controllers\Api\SearchController;

/*
|--------------------------------------------------------------------------
| RealEstate Module API Routes
|--------------------------------------------------------------------------
*/

// Listings
Route::apiResource('listings', ListingController::class);
Route::get('listings/{listing}/similar', [ListingController::class, 'similar']);
Route::get('listings/{listing}/stats', [ListingController::class, 'stats']);
Route::post('listings/{listing}/view', [ListingController::class, 'recordView']);
Route::post('listings/{listing}/favorite', [ListingController::class, 'toggleFavorite']);

// Projects
Route::apiResource('projects', ProjectController::class);
Route::get('projects/{project}/listings', [ProjectController::class, 'listings']);
Route::get('projects/{project}/stats', [ProjectController::class, 'stats']);

// Search
Route::get('search', [SearchController::class, 'search']);
Route::get('search/suggestions', [SearchController::class, 'suggestions']);
Route::get('search/filters', [SearchController::class, 'filters']);

// Location data
Route::get('locations/cities', function () {
    return \Modules\RealEstate\Models\Listing::distinct()
        ->whereNotNull('city')
        ->pluck('city');
});

Route::get('locations/districts/{city}', function ($city) {
    return \Modules\RealEstate\Models\Listing::where('city', $city)
        ->distinct()
        ->whereNotNull('district')
        ->pluck('district');
});

// Statistics
Route::get('stats/overview', function () {
    return [
        'total_listings' => \Modules\RealEstate\Models\Listing::count(),
        'active_listings' => \Modules\RealEstate\Models\Listing::active()->count(),
        'total_projects' => \Modules\RealEstate\Models\Project::count(),
        'avg_price' => \Modules\RealEstate\Models\Listing::active()->avg('price'),
    ];
});
