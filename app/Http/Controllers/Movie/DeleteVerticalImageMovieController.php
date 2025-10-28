<?php

namespace App\Http\Controllers\Movie;

use App\Http\Controllers\Controller;

use App\Traits\Movie\HasDeleteVerticalImageMovie;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DeleteVerticalImageMovieController extends Controller
{
    use HasDeleteVerticalImageMovie;

    public function __invoke(Movie $movie)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to delete a vertical image for a movie"));
            }

            $_user = User::find(Auth::user()->id);
            if ($movie->user_id !== $_user->id) {
                throw new \Exception(__("You are not authorized to delete a vertical image for a movie"));
            }

            $this->deleteVerticalImage($movie);

            return inertiaSuccessHandler(
                __("Success"),
                __("Vertical image deleted successfully")
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
