<?php

namespace App\Traits\Movie;

use App\Models\Movie;

trait HasDeleteTrailerMovie
{
    public function deleteTrailerMovie(Movie $movie)
    {
        $movie->deleteTrailerMovie();
    }
}
