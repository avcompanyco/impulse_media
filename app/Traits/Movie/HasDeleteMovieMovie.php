<?php

namespace App\Traits\Movie;

use App\Models\Movie;

trait HasDeleteMovieMovie       
{
    public function deleteMovie(Movie $movie)
    {
        $movie->deleteMovie();
    }
}
