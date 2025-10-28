<?php

namespace App\Http\Controllers\Movie;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Movie\HasUploadTrailerMovie;
use App\Models\Movie;
use App\Http\Requests\Movie\UploadTrailerMovieRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use App\Services\ChunkUploadService;

class UploadTrailerMovieController extends Controller
{
    use HasUploadTrailerMovie;

    public function __invoke(UploadTrailerMovieRequest $request, Movie $movie)
    {
        if (!$this->canAccess()) {
            return inertiaErrorHandler(
                __("Error"),
                __("You are not authorized to upload a trailer for a movie")
            );
        }

        $_user = User::find(Auth::user()->id);
        if ($movie->user_id !== $_user->id) {
            return inertiaErrorHandler(
                __("Error"),
                __("You are not authorized to upload a trailer for a movie")
            );
        }

        $data = $request->validated();

        $chunkUpload = new ChunkUploadService(
            file: $data['trailer_video'],
            isLastChunk: $data['is_last_chunk'],
            documentPath: 'trailer_videos',
            model: $movie
        );
        try {

            $path = $chunkUpload->merge();

            // if the last chunk, store the uploaded file
            if ($path) {
                return redirect()->back()->with([
                    'complete' => true
                ]);
            }

            return redirect()->back()->with([
                'complete' => false
            ]);
        } catch (\Throwable $th) {
            $chunkUpload->deleteChunk();
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
