<?php

namespace App\Traits\Movie;

use App\Models\Movie;

trait HasUpdateMovie
{
    public function update(Movie $movie, array $data)
    {
        $movie->update($data);
        return $movie;
    }
}
