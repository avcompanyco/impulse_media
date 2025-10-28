<?php

namespace App\Traits\Serie;

use App\Models\ChapterSerie;

trait HasUploadThumbnailChapter
{
    public function uploadThumbnailChapter(ChapterSerie $chapter, array $data)
    {
        if ($data['thumbnail']) {
            $chapter->updateThumbnail($data['thumbnail']);
        }

        return $chapter;
    }
}
