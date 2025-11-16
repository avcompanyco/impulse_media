<?php

namespace App\Traits\Serie;

use App\Models\SeasonSerie; 
trait HasDeleteSeason
{
    public function delete(SeasonSerie $season)
    {
        if ($season->chapters()->count() > 0) {
            throw new \Exception(__("Season has chapters"));
        }

        if ($season->allChapters()->count() > 0) {
            // Delete all chapters
            foreach ($season->allChapters as $chapter) {
                $chapter->deleteAll();
                $chapter->delete();
            }
        }

        $season->delete();
    }
}
