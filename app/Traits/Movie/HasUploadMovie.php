<?php

namespace App\Traits\Movie;

use App\Models\Movie;

trait HasUploadMovie
{
    public function uploadMovie(Movie $movie, array $data)
    {
        $movie->update($data);
        return $movie;
    }
}
