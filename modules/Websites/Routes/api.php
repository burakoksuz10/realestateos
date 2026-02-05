<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Websites API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for the Websites module.
|
*/

Route::prefix('websites')->name('websites.')->group(function () {
    Route::get('/', function () {
        return response()->json(['message' => 'Websites API']);
    })->name('index');
    
    Route::get('/{website}/pages', function ($website) {
        return response()->json(['message' => 'Website Pages API', 'website' => $website]);
    })->name('pages');
    
    Route::post('/{website}/forms/{form}/submit', function ($website, $form) {
        return response()->json(['message' => 'Form submitted', 'website' => $website, 'form' => $form]);
    })->name('forms.submit');
    
    Route::post('/tracking', function () {
        return response()->json(['message' => 'Visitor tracked']);
    })->name('tracking');
});
