<?php

namespace App\Traits\Serie;

use App\Models\ChapterSerie;
use App\Enums\Content\ContentStatus;

trait HasPublishChapter
{
    public function publish(ChapterSerie $chapter, array $data)
    {
        $chapter->fill($data)->save();
        // verify all fields are filled
        if (!$chapter->chapter_number || !$chapter->title || !$chapter->thumbnail || !$chapter->chapter_video) {
            throw new \Exception(__("Cannot publish this chapter"));
        }

        $chapter->fill([
            'status' => ContentStatus::PUBLISHED->value,
        ])->save();


        return $chapter;
    }
}
