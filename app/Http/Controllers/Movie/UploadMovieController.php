<?php

namespace App\Http\Controllers\Movie;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Movie\HasUploadMovie;
use App\Models\Movie;
use App\Http\Requests\Movie\UploadMovieRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use App\Services\ChunkUploadService;

class UploadMovieController extends Controller
{
    use HasUploadMovie;

    public function __invoke(UploadMovieRequest $request, Movie $movie)
    {
        if (!$this->canAccess()) {
            return inertiaErrorHandler(
                __("Error"),
                __("You are not authorized to upload a movie")
            );
        }

        $_user = User::find(Auth::user()->id);
        if ($movie->user_id !== $_user->id) {
            return inertiaErrorHandler(
                __("Error"),
                __("You are not authorized to upload a movie")
            );
        }

        $data = $request->validated();

        $chunkUpload = new ChunkUploadService(
            file: $data['movie_video'],
            isLastChunk: $data['is_last_chunk'],
            documentPath: 'movie_videos',
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
