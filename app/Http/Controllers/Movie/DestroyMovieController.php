<?php

namespace App\Http\Controllers\Movie;

use App\Http\Controllers\Controller;

use App\Traits\Movie\HasDeleteMovie;
use App\Models\Movie;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DestroyMovieController extends Controller
{
    use HasDeleteMovie;

    public function __invoke(Movie $movie)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to delete a movie"));
            }

            $_user = User::find(Auth::user()->id);

            if ($_user->id !== $movie->user_id) {
                throw new \Exception(__("You are not authorized to delete this movie"));
            }

            $this->delete($movie);

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
