<?php

namespace App\Traits\Movie;

use App\Models\Movie;

trait HasUploadTrailerMovie
{
    public function uploadTrailerMovie(Movie $movie, array $data)
    {
        $movie->update($data);
        return $movie;
    }
}
