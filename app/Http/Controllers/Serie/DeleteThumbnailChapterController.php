<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Traits\Serie\HasDeleteThumbnailChapter;
use App\Models\Serie;
use App\Models\ChapterSerie;
use App\Models\User;

class DeleteThumbnailChapterController extends Controller
{
    use HasDeleteThumbnailChapter;

    public function __invoke(Serie $serie, ChapterSerie $chapter)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to delete a chapter thumbnail"));
            }

            $_user = User::find(Auth::user()->id);


            if ($serie->user_id !== $_user->id) {
                throw new \Exception(__("You are not authorized to delete a chapter thumbnail"));
            }

            if ($chapter->user_id !== $_user->id) {
                throw new \Exception(__("You are not authorized to delete a chapter thumbnail"));
            }

            $this->deleteThumbnail($chapter);

            return inertiaSuccessHandler(
                __("Success"),
                __("Chapter thumbnail deleted successfully")
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
