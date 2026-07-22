<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\User;
use App\Models\Content;
use App\Models\Serie;
use App\Models\Category;
use App\Enums\Content\ContentType;
use App\Enums\Content\ContentStatus;

class ShowSerieController extends Controller
{
    public function __invoke(Serie $serie)
    {
        $_user = User::find(Auth::user()->id);

        $content = $serie->content;
        if ($content->status !== ContentStatus::PUBLISHED) {
            return redirect()->route('dashboard')->with('error', 'Movie not published');
        }

        // Load serie with all necessary relationships
        $serie = $serie->load([
            'content', 
            'watchlist', 
            'category' => function ($query) {
                $query->with(['series' => function ($query) {
                    $query->whereHas('content', function ($query) {
                        $query->where('status', ContentStatus::PUBLISHED->value);
                    })->inRandomOrder()->limit(20);
                }]);
            },
            'subcategory', 
            'user' => function ($query) {
                $query->select('id', 'name', 'username', 'image');
            },
            'seasons' => function ($query) {
                $query->orderBy('id', 'asc');
            },
            'seasons.chapters' => function ($query) {
                $query->orderBy('chapter_number', 'asc');
            }
        ]);

        $user = Auth::user();
        $hasFullAccess = false;
        $isPurchased = false;
        $isMember = $user ? $user->isImpulseMember() : false;
        $rawPpvPrice = (float)($content->ppv_price ?? 0);

        if ($user) {
            if ($user->hasRole('admin') || $user->id === $serie->user_id) {
                $hasFullAccess = true;
            } else {
                if ($content->isPurchasedBy($user)) {
                    $hasFullAccess = true;
                    $isPurchased = true;
                } elseif ($content->allow_membership && $isMember) {
                    $hasFullAccess = true;
                } elseif ($rawPpvPrice <= 0) {
                    $hasFullAccess = true;
                }
            }
        }

        return Inertia::render('user/serie/ShowSerie', [
            'serie' => $serie,
            'hasFullAccess' => $hasFullAccess,
            'isPurchased' => $isPurchased,
            'ppvPrice' => $rawPpvPrice,
        ]);
    }
}
