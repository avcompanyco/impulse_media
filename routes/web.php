<?php

use Illuminate\Support\Facades\Route;

// Public reels/shorts landing page — accessible without login
// (authenticated users are redirected to their dashboard inside the controller)
Route::get('/', \App\Http\Controllers\Short\PublicShortController::class)
    ->name('home');

// Public API: random shorts feed (no auth required)
Route::get('/public/shorts/random', \App\Http\Controllers\Short\PublicRandomShortController::class)
    ->name('public.shorts.random');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/caeher.php';
