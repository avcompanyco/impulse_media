<?php

namespace App\Http\Controllers\Watchlist;

use App\Http\Controllers\Controller;

use App\Traits\Watchlist\HasDeleteWatchlist;

class DeleteWatchlistController extends Controller
{
    use HasDeleteWatchlist;

    public function __invoke()
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to delete a watchlist"));
            }

            $this->delete();

            return inertiaSuccessHandler(
                __("Success"),
                __("Watchlist deleted successfully")
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
