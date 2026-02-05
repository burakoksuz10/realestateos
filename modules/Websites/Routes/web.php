<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Websites Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for the Websites module.
|
*/

Route::prefix('websites')->name('websites.')->group(function () {
    Route::get('/', function () {
        return view('websites::index');
    })->name('index');
    
    Route::get('/{website}', function ($website) {
        return view('websites::show', compact('website'));
    })->name('show');
    
    Route::get('/{website}/pages', function ($website) {
        return view('websites::pages.index', compact('website'));
    })->name('pages.index');
    
    Route::get('/{website}/forms', function ($website) {
        return view('websites::forms.index', compact('website'));
    })->name('forms.index');
});
