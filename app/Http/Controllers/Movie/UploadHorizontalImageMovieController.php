<?php

namespace App\Http\Controllers\Movie;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Movie\HasUploadHorizontalImage;
use App\Models\Movie;
use App\Http\Requests\Movie\UploadHorizontalImageMovieRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UploadHorizontalImageMovieController extends Controller
{
    use HasUploadHorizontalImage;

    public function __invoke(UploadHorizontalImageMovieRequest $request, Movie $movie)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to upload a horizontal image for a movie"));
            }

            $_user = User::find(Auth::user()->id);
            if ($movie->user_id !== $_user->id) {
                throw new \Exception(__("You are not authorized to upload a horizontal image for a movie"));
            }

            $this->uploadHorizontalImage($movie, $request->validated());

            return inertiaSuccessHandler(
                __("Success"),
                __("Horizontal image uploaded successfully")
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
