<?php

namespace App\Http\Controllers\Serie;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Serie\HasUploadThumbnailChapter;
use App\Models\Serie;
use App\Models\SeasonSerie;
use App\Models\ChapterSerie;
use App\Http\Requests\Serie\UploadThumbnailChapterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UploadThumbnailChapterController extends Controller
{
    use HasUploadThumbnailChapter;

    public function __invoke(UploadThumbnailChapterRequest $request, Serie $serie, SeasonSerie $season, ChapterSerie $chapter)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to upload a thumbnail for a chapter"));
            }

            if ($season->id !== $chapter->season_id) {
                throw new \Exception(__("Chapter not found"));
            }

            $_user = User::find(Auth::user()->id);
            if ($chapter->user_id !== $_user->id) {
                throw new \Exception(__("You are not authorized to upload a thumbnail for a chapter"));
            }

            $this->uploadThumbnailChapter($chapter, $request->validated());

            return inertiaSuccessHandler(
                __("Success"),
                __("Thumbnail chapter uploaded successfully")
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
