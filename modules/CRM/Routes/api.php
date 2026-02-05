<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CRM API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for the CRM module.
|
*/

Route::prefix('leads')->name('leads.')->group(function () {
    Route::get('/', function () {
        return response()->json(['message' => 'CRM Leads API']);
    })->name('index');
});

Route::prefix('contacts')->name('contacts.')->group(function () {
    Route::get('/', function () {
        return response()->json(['message' => 'CRM Contacts API']);
    })->name('index');
});

Route::prefix('deals')->name('deals.')->group(function () {
    Route::get('/', function () {
        return response()->json(['message' => 'CRM Deals API']);
    })->name('index');
});
