<?php

namespace App\Traits\Serie;

use App\Models\Serie;

trait HasDeleteHorizontalImageSerie
{
    public function deleteHorizontalImage(Serie $serie)
    {
        $serie->deleteHorizontalImage();
        return $serie;
    }
}
