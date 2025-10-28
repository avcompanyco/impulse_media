<?php

namespace App\Http\Controllers\Movie;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Movie\HasUploadVerticalImage;
use App\Models\Movie;
use App\Http\Requests\Movie\UploadVerticalImageMovieRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;    
class UploadVerticalImageMovieController extends Controller
{
    use HasUploadVerticalImage;

    public function __invoke(UploadVerticalImageMovieRequest $request, Movie $movie)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to upload a vertical image for a movie"));
            }

            $_user = User::find(Auth::user()->id);
            if ($movie->user_id !== $_user->id) {
                throw new \Exception(__("You are not authorized to upload a vertical image for a movie"));
            }

            $this->uploadVerticalImage($movie, $request->validated());

            return inertiaSuccessHandler(
                __("Success"),
                __("Vertical image uploaded successfully")
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
