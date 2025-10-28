<?php

namespace App\Traits\Movie;

use App\Models\Movie;

trait HasDeleteHorizontalImageMovie
{
    public function deleteHorizontalImage(Movie $movie)
    {
        $movie->deleteHorizontalImage();
    }
}
