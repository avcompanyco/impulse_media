<?php

namespace App\Http\Controllers\Movie;

use App\Http\Controllers\Controller;

use App\Traits\Movie\HasDeleteMovieMovie;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DeleteMovieMovieController extends Controller
{
    use HasDeleteMovieMovie;

    public function __invoke(Movie $movie)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to delete a movie for a movie"));
            }

            $_user = User::find(Auth::user()->id);
            if ($movie->user_id !== $_user->id) {
                throw new \Exception(__("You are not authorized to delete a movie for a movie"));
            }

            $this->deleteMovie($movie);

            return inertiaSuccessHandler(
                __("Success"),
                __("Movie deleted successfully")
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
