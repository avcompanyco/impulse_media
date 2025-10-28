<?php

namespace App\Traits\Serie;

use App\Models\Serie;

trait HasDeleteVerticalImageSerie
{
    public function deleteVerticalImage(Serie $serie)
    {
        $serie->deleteVerticalImage(); // es metodo del modelo
        return $serie;
    }
}
