<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;

use App\Models\Serie;
use App\Models\SeasonSerie;
use App\Models\ChapterSerie;
use App\Traits\Serie\HasDeleteChapter;

class DestroyChapterController extends Controller
{
    use HasDeleteChapter;

    public function __invoke(Serie $serie, SeasonSerie $season, ChapterSerie $chapter)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to delete a chapter"));
            }

            if ($season->id !== $chapter->season_id) {
                throw new \Exception(__("Chapter not found"));
            }

            $this->delete($chapter);

            return inertiaSuccessHandler(
                __("Success"),
                __("Chapter deleted successfully")
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
