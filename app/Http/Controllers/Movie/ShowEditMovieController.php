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

class ShowEditMovieController extends Controller
{
    public function __invoke(Movie $movie)
    {
        $_user = User::find(Auth::user()->id);

        if ($movie->user_id != $_user->id) {
            return inertiaErrorHandler(
                __("Error"),
                __("Movie not found")
            );
        }

        $movie->load('content');
        
        if (!$movie->content || $movie->content->status == ContentStatus::DRAFT) {
            return redirect()->route('user.upload.movie');
        }

        $categories = Category::with('subcategories')->orderBy('name', 'asc')->get();

        return Inertia::render('user/movie/ShowEditMovie', [
            'movie' => $movie,
            'categories' => $categories,
        ]);
    }
}
