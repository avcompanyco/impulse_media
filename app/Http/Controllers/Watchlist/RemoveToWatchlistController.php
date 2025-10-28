<?php

namespace App\Http\Controllers\Watchlist;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Models\Serie;
use App\Models\Movie;
use App\Models\Watchlist;

class RemoveToWatchlistController extends Controller
{
    public function __invoke($type, $id)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to remove from watchlist"));
            }

            if ($type === 'movie') {
                $movie = Movie::find($id);
                if (!$movie) {
                    throw new \Exception(__("Movie not found"));
                }
                // verify if the movie is in the watchlist
                $watchlist = Watchlist::where('user_id', Auth::user()->id)
                    ->where('watchlistable_id', $movie->id)
                    ->where('watchlistable_type', Movie::class)
                    ->first();
                if (!$watchlist) {
                    throw new \Exception(__("Movie not found in watchlist"));
                }
                $watchlist->delete();
            } else if ($type === 'serie') {
                $serie = Serie::find($id);
                if (!$serie) {
                    throw new \Exception(__("Serie not found"));
                }
                // verify if the serie is in the watchlist
                $watchlist = Watchlist::where('user_id', Auth::user()->id)
                    ->where('watchlistable_id', $serie->id)
                    ->where('watchlistable_type', Serie::class)
                    ->first();
                if (!$watchlist) {
                    throw new \Exception(__("Serie not found in watchlist"));
                }
                $watchlist->delete();
            }

            return inertiaSuccessHandler(
                __("Success"),
                __("Watchlist removed successfully")
            );
        } catch (\Throwable $th) {
            return inertiaErrorHandler(
                __("Error"),
                $th->getMessage()
            );
        }
    }

    public function canAccess()
    {
        return true;
    }
}
