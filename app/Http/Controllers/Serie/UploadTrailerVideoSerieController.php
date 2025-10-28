<?php

namespace App\Http\Controllers\Serie;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Serie\HasUploadTrailerVideoSerie;
use App\Models\Serie;
use App\Http\Requests\Serie\UploadTrailerVideoSerieRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use App\Services\ChunkUploadService;

class UploadTrailerVideoSerieController extends Controller
{
    use HasUploadTrailerVideoSerie;

    public function __invoke(UploadTrailerVideoSerieRequest $request, Serie $serie)
    {
        if (!$this->canAccess()) {
            return inertiaErrorHandler(
                __("Error"),
                __("You are not authorized to upload a trailer for a serie")
            );
        }

        $_user = User::find(Auth::user()->id);
        if ($serie->user_id !== $_user->id) {
            return inertiaErrorHandler(
                __("Error"),
                __("You are not authorized to upload a trailer for a serie")
            );
        }

        $data = $request->validated();

        $chunkUpload = new ChunkUploadService(
            file: $data['trailer_video'],
            isLastChunk: $data['is_last_chunk'],
            documentPath: 'trailer_videos',
            model: $serie
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
