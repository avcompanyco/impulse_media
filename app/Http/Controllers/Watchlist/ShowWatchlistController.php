<?php

namespace App\Http\Controllers\Watchlist;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\Content;
use App\Models\Watchlist;
use App\Enums\Content\ContentStatus;

class ShowWatchlistController extends Controller
{
    public function __invoke()
    {
        $watchlist = Watchlist::where('user_id', Auth::user()->id)
            ->with([
                'watchlistable' => function ($query) {
                    $query->with([
                        'content' => function ($contentQuery) {
                            $contentQuery->where('status', ContentStatus::PUBLISHED->value);
                        },
                        'category'
                    ]);
                }
            ])
            ->get()
            ->filter(function ($item) {
                return $item->watchlistable && $item->watchlistable->content;
            })
            ->groupBy(function ($item) {
                return $item->watchlistable->category->name ?? __("Without category");
            })
            ->map(function ($categoryItems) {
                return [
                    'category' => $categoryItems->first()->watchlistable->category ?? null,
                    'items' => $categoryItems->values()
                ];
            });

        return Inertia::render('user/watchlist/ShowWatchlist', [
            'watchlist' => $watchlist,
        ]);
    }
}
