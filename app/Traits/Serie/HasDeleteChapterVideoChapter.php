<?php

namespace App\Traits\Serie;

use App\Models\ChapterSerie;

trait HasDeleteChapterVideoChapter
{
    public function deleteChapterVideo(ChapterSerie $chapter)
    {
        $chapter->deleteChapterVideo();
        return $chapter;
    }
}
