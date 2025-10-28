<?php

namespace App\Traits\Serie;

use App\Models\ChapterSerie;

trait HasDeleteThumbnailChapter
{
    public function deleteThumbnail(ChapterSerie $chapter)
    {
        $chapter->deleteThumbnail();
        return $chapter;
    }
}
