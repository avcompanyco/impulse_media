<?php

namespace App\Traits\Serie;

use App\Models\Serie;

trait HasUploadVerticalImageSerie
{
    public function uploadVerticalImage(Serie $serie, array $data)
    {
        if ($data['vertical_image']) {
            $serie->updateVerticalImage($data['vertical_image']);
        }

        return $serie;
    }
}
