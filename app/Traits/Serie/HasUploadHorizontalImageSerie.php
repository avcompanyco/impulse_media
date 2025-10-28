<?php

namespace App\Traits\Serie;

use App\Models\Serie;

trait HasUploadHorizontalImageSerie
{
    public function uploadHorizontalImage(Serie $serie, array $data)
    {
        if ($data['horizontal_image']) {
            $serie->updateHorizontalImage($data['horizontal_image']);
        }

        return $serie;
    }
}
