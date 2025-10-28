<?php

namespace App\Traits\Serie;

use App\Models\ChapterSerie;

trait HasUploadChapterVideoChapter
{
    public function uploadChapterVideo(ChapterSerie $chapter, array $data)
    {
        $chapter->update($data);
        return $chapter;
    }
}
