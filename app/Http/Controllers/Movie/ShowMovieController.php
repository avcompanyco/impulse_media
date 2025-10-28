<?php

namespace App\Http\Controllers\Movie;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\User;
use App\Models\Content;
use App\Models\Movie;
use App\Models\Category;
use App\Enums\Content\ContentType;
use App\Enums\Content\ContentStatus;

class ShowMovieController extends Controller
{
    public function __invoke(Movie $movie)
    {
        $content = $movie->content;
        if ($content->status !== ContentStatus::PUBLISHED) {
            return redirect()->route('dashboard')->with('error', 'Movie not published');
        }

        $movie = $movie->load(['content', 'watchlist', 'category' => function ($query) {
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

        return Inertia::render('user/movie/ShowMovie', [
            'movie' => $movie,
        ]);
    }
}
