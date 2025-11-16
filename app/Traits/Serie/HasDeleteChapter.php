<?php

namespace App\Traits\Serie;

use App\Models\ChapterSerie;

trait HasDeleteChapter
{
    public function delete(ChapterSerie $chapter)
    {
        $chapter->deleteAll();
        
        $chapter->delete();
    }
}
