<?php

namespace App\Traits\Movie;

use App\Models\Movie;

trait HasUploadVerticalImage
{
    public function uploadVerticalImage(Movie $movie, array $data)
    {
        if ($data['vertical_image']) {
            $movie->updateVerticalImage($data['vertical_image']);
        }

        return $movie;
    }
}
