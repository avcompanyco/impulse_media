<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\Serie;
use App\Enums\Content\ContentStatus;

class ShowPlayerSerieController extends Controller
{
    public function __invoke(Serie $serie)
    {
        $content = $serie->content;
        if ($content->status !== ContentStatus::PUBLISHED) {
            return redirect()->route('dashboard')->with('error', 'Serie not published');
        }

        $serie = $serie->load(['content', 'watchlist', 'category' => function ($query) {
            $query->with(['movies' => function ($query) {
                $query->whereHas('content', function ($query) {
                    $query->where('status', ContentStatus::PUBLISHED->value);
                })->inRandomOrder()->limit(20);
            }, 'series' => function ($query) {
                $query->whereHas('content', function ($query) {
                    $query->where('status', ContentStatus::PUBLISHED->value);
                })->inRandomOrder()->limit(20);
            }]);
        }, 'subcategory', 'user' => function ($query) {
            $query->select('id', 'name', 'username', 'image');
        }]);

        return Inertia::render('user/serie/ShowPlayerSerie', [
            'serie' => $serie,
        ]);
    }
}
