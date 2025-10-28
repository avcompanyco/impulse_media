<?php

namespace App\Traits\Serie;

use App\Models\ChapterSerie;

trait HasUpdateChapter
{
    public function update(ChapterSerie $chapter, array $data)
    {
        $chapter->fill($data)->save();
        // verify all fields are filled
        if (!$chapter->chapter_number || !$chapter->title || !$chapter->thumbnail || !$chapter->chapter_video) {
            throw new \Exception(__("Cannot update this chapter"));
        }

        return $chapter;
    }
}
