<?php

namespace App\Http\Controllers\Movie;

use App\Http\Controllers\Controller;
use App\Traits\Movie\HasPublishMovie;
use App\Http\Requests\Movie\UpdateMovieRequest;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PublishMovieController extends Controller
{
    use HasPublishMovie;

    public function __invoke(UpdateMovieRequest $request, Movie $movie)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to publish a movie"));
            }

            $_user = User::find(Auth::user()->id);
            if ($_user->id !== $movie->user_id) {
                throw new \Exception(__("You are not authorized to publish this movie"));
            }

            $data = $request->validated();
            $this->publish($movie, $data);
            // return redirect()->route('user.upload.movie');

            return inertiaSuccessHandler(
                __("Success"),
                __("Movie published successfully")
            );
        } catch (\Throwable $th) {
            dd($th);
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
