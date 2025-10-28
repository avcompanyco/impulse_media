<?php

namespace App\Http\Controllers\Movie;

use App\Http\Controllers\Controller;

use App\Traits\Movie\HasDeleteHorizontalImageMovie;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DeleteHorizontalImageMovieController extends Controller
{
    use HasDeleteHorizontalImageMovie;

    public function __invoke(Movie $movie)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to delete a horizontal image for a movie"));
            }

            $_user = User::find(Auth::user()->id);
            if ($movie->user_id !== $_user->id) {
                throw new \Exception(__("You are not authorized to delete a horizontal image for a movie"));
            }

            $this->deleteHorizontalImage($movie);

            return inertiaSuccessHandler(
                __("Success"),
                __("Horizontal image deleted successfully")
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
