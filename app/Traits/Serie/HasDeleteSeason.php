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

        $season->delete();
    }
}
