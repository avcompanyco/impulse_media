<?php

namespace App\Traits\Movie;

use App\Models\Movie;

trait HasDeleteVerticalImageMovie
{
    public function deleteVerticalImage(Movie $movie)
    {
        $movie->deleteVerticalImage();
    }
}
