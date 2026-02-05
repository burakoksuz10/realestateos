<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| BI (Business Intelligence) API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for the BI module.
|
*/

Route::prefix('analytics')->name('analytics.')->group(function () {
    Route::get('/dashboard', function () {
        return response()->json(['message' => 'Dashboard Analytics API']);
    })->name('dashboard');
    
    Route::get('/sales', function () {
        return response()->json(['message' => 'Sales Analytics API']);
    })->name('sales');
    
    Route::get('/leads', function () {
        return response()->json(['message' => 'Leads Analytics API']);
    })->name('leads');
    
    Route::get('/performance', function () {
        return response()->json(['message' => 'Performance Analytics API']);
    })->name('performance');
});

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', function () {
        return response()->json(['message' => 'Reports API']);
    })->name('index');
    
    Route::post('/generate', function () {
        return response()->json(['message' => 'Report generation initiated']);
    })->name('generate');
    
    Route::get('/export/{format}', function ($format) {
        return response()->json(['message' => 'Report export', 'format' => $format]);
    })->name('export');
});
