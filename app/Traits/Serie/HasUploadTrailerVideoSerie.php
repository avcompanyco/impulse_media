<?php

namespace App\Traits\Serie;

use App\Models\Serie;

trait HasUploadTrailerVideoSerie
{
    public function uploadTrailerVideo(Serie $serie, array $data)
    {
        $serie->update($data);
        return $serie;
    }
}
