<?php

use Illuminate\Support\Facades\Route;
use Modules\SocialMedia\Http\Controllers\SocialController;

Route::get('/', [SocialController::class, 'index'])->name('index');
Route::post('/posts', [SocialController::class, 'store'])->name('posts.store');
Route::put('/posts/{post}', [SocialController::class, 'update'])->name('posts.update');
Route::delete('/posts/{post}', [SocialController::class, 'destroy'])->name('posts.destroy');
Route::post('/posts/{post}/publish', [SocialController::class, 'publish'])->name('posts.publish');
Route::post('/ai/caption', [SocialController::class, 'generateCaption'])->name('ai.caption');
Route::post('/ai/plan', [SocialController::class, 'generatePlan'])->name('ai.plan');
Route::post('/ai/image', [SocialController::class, 'generateImage'])->name('ai.image');
Route::post('/ai/enhance', [SocialController::class, 'enhanceImage'])->name('ai.enhance');
Route::post('/ai/from-listing', [SocialController::class, 'generateFromListing'])->name('ai.from-listing');
Route::post('/ai/hashtags', [SocialController::class, 'suggestHashtags'])->name('ai.hashtags');
Route::post('/cards/generate', [SocialController::class, 'generateCard'])->name('cards.generate');
Route::get('/calendar', [SocialController::class, 'calendar'])->name('calendar');
