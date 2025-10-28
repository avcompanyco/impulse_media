<?php

namespace App\Traits\Serie;

use App\Models\Serie;

trait HasDeleteTrailerVideoSerie
{
    public function deleteTrailerVideo(Serie $serie)
    {
        $serie->deleteTrailerVideoSerie();
        return $serie;
    }
}
