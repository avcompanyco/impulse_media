<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;

use App\Traits\Serie\HasDeleteChapterVideoChapter;
use App\Models\Serie;
use App\Models\ChapterSerie;

class DeleteChapterVideoChapterController extends Controller
{
    use HasDeleteChapterVideoChapter;

    public function __invoke(Serie $serie, ChapterSerie $chapter)
    {
        try {
            if (!$this->canAccess()) {
            throw new \Exception(__("You are not authorized to delete a chapter video"));
            }

            $this->deleteChapterVideo($chapter);

            return inertiaSuccessHandler(
                __("Success"),
                __("Chapter video deleted successfully")
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
