<?php

namespace App\Http\Controllers\Short;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Short\HasUploadShort;
use App\Models\Short;
use App\Http\Requests\Short\UploadShortRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use App\Services\ChunkUploadService;

class UploadShortController extends Controller
{
    use HasUploadShort;

    public function __invoke(UploadShortRequest $request, Short $short)
    {
        if (!$this->canAccess()) {
            return inertiaErrorHandler(
                __("Error"),
                __("You are not authorized to upload a short")
            );
        }

        $_user = User::find(Auth::user()->id);
        if ($short->user_id !== $_user->id) {
            return inertiaErrorHandler(
                __("Error"),
                __("You are not authorized to upload a short")
            );
        }

        $data = $request->validated();

        $chunkUpload = new ChunkUploadService(
            file: $data['short_video'],
            isLastChunk: $data['is_last_chunk'],
            documentPath: 'short_videos',
            model: $short
        );
        try {

            $path = $chunkUpload->merge(false); // se coloca false, porque luego de la compresion se elimina el chunk

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
