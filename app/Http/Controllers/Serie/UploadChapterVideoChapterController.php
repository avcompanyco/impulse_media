<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;
use App\Traits\Serie\HasUploadChapterVideoChapter;
use App\Models\Serie;
use App\Models\ChapterSerie;
use App\Models\SeasonSerie;
use App\Http\Requests\Serie\UploadChapterVideoChapterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use App\Services\ChunkUploadService;

class UploadChapterVideoChapterController extends Controller
{
    use HasUploadChapterVideoChapter;

    public function __invoke(UploadChapterVideoChapterRequest $request,Serie $serie, SeasonSerie $season, ChapterSerie $chapter)
    {
        if (!$this->canAccess()) {
            return inertiaErrorHandler(
                __("Error"),
                __("You are not authorized to upload a chapter video for a chapter")
            );
        }

        $_user = User::find(Auth::user()->id);
        if ($chapter->user_id !== $_user->id) {
            return inertiaErrorHandler(
                __("Error"),
                __("You are not authorized to upload a chapter video for a chapter")
            );
        }

        if ($serie->user_id !== $_user->id) {
            return inertiaErrorHandler(
                __("Error"),
                __("You are not authorized to upload a chapter video for a chapter")
            );
        }

        $data = $request->validated();

        $chunkUpload = new ChunkUploadService(
            file: $data['chapter_video'],
            isLastChunk: $data['is_last_chunk'],
            documentPath: 'chapter_videos',
            model: $chapter
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
