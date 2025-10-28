<?php

namespace App\Http\Controllers\Movie;

use App\Http\Controllers\Controller;

use App\Http\Requests\Movie\UpdateMovieRequest;
use App\Traits\Movie\HasUpdateMovie;
use App\Models\Movie;

class UpdateMovieController extends Controller
{
    use HasUpdateMovie;

    public function __invoke(UpdateMovieRequest $request, Movie $movie)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to update a movie"));
            }

            $this->update($movie, $request->validated());

            return inertiaSuccessHandler(
                __("Success"),
                __("Movie updated successfully")
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
