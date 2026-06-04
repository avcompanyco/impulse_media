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

class CreateMovieController extends Controller
{
    public function __invoke()
    {
        $_user = User::find(Auth::user()->id);

        // Spectators cannot upload content
        if ($_user->isSpectator()) {
            return redirect()->route('dashboard')->with([
                'type' => 'error',
                'title' => __('Access Denied'),
                'message' => __('Spectator accounts cannot upload content. Switch to a Creator account to start uploading.'),
            ]);
        }

        // get content with movie morph,
        $movie = Movie::where('user_id', $_user->id)
            ->whereHas('content', function ($query) {
                $query->where('type', ContentType::MOVIE->value)
                    ->where('status', ContentStatus::DRAFT->value);
            })
            ->first();

        if (!$movie) {
            $movie = Movie::create([
                'title' => '',
                'description' => '',
                'movie_video' => '',
                'trailer_video' => '',
                'horizontal_image' => '',
                'vertical_image' => '',
                'user_id' => $_user->id,
            ]);
        }

        $content = $movie->content;
        if (!$content) {
            $movie->content()->create([
                'type' => ContentType::MOVIE->value,
                'status' => ContentStatus::DRAFT->value,
                'user_id' => $_user->id,
            ]);
        }

        $movie->load('content');

        $categories = Category::with('subcategories')->orderBy('name', 'asc')->get();

        $plan = $_user->getCurrentPlan();

        if (!$plan) {
            return redirect()->route('user.profile.subscription');
        }

        $canPublish = true;
        $moviesCount = $_user->movies()
            ->where('status', ContentStatus::PUBLISHED->value)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
        if (!$plan->is_unlimited_content)
        {
            $canPublish = $plan->movies_upload_count > $moviesCount;
        }
        return Inertia::render('user/movie/CreateMovie', [
            'movie' => $movie,
            'categories' => $categories,
            'isCanPublish' => $canPublish,
            'moviesCount' => $moviesCount,
            'plan' => $plan,
        ]);
    }
}
