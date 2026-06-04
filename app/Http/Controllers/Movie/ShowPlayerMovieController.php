<?php

namespace App\Http\Controllers\Movie;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\Movie;
use App\Models\Setting;
use App\Enums\Content\ContentStatus;

class ShowPlayerMovieController extends Controller
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

        $user = Auth::user();
        $hasFullAccess = false;
        $isMember = $user ? $user->isImpulseMember() : false;
        
        $rawPpvPrice = (float)($content->ppv_price ?? 0);
        $discountRate = (float)Setting::get('membership_discount_rate', 10);
        
        $ppvPrice = $rawPpvPrice;
        if ($isMember && $discountRate > 0) {
            $ppvPrice = max(0.00, round($rawPpvPrice * (1 - ($discountRate / 100)), 2));
        }

        if ($user) {
            if ($user->hasRole('admin') || $user->id === $movie->user_id) {
                $hasFullAccess = true;
            } else {
                if ($rawPpvPrice <= 0) {
                    $hasFullAccess = true;
                } else {
                    if ($content->isPurchasedBy($user)) {
                        $hasFullAccess = true;
                    } elseif ($content->allow_membership && $isMember) {
                        $hasFullAccess = true;
                    }
                }
            }
        }

        return Inertia::render('user/movie/ShowPlayerMovie', [
            'movie' => $movie,
            'hasFullAccess' => $hasFullAccess,
            'ppvPrice' => $ppvPrice,
            'rawPpvPrice' => $rawPpvPrice,
            'isMember' => $isMember,
            'allowMembership' => (bool)$content->allow_membership,
        ]);
    }
}
