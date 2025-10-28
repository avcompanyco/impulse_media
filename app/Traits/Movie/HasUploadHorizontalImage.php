<?php

namespace App\Traits\Movie;

use App\Models\Movie;

trait HasUploadHorizontalImage
{
    public function uploadHorizontalImage(Movie $movie, array $data)
    {
        if ($data['horizontal_image']) {
            $movie->updateHorizontalImage($data['horizontal_image']);
        }

        return $movie;
    }
}
