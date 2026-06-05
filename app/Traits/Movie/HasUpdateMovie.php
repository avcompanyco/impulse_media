<?php

namespace App\Traits\Movie;

use App\Models\Movie;

trait HasUpdateMovie
{
    public function update(Movie $movie, array $data)
    {
        $movieData = collect($data)->except(['ppv_price', 'allow_membership'])->toArray();
        $movie->update($movieData);

        if ($movie->content) {
            $movie->content->update([
                'ppv_price' => $data['ppv_price'] ?? 0.00,
                'allow_membership' => (bool)($data['allow_membership'] ?? true),
            ]);
        }

        return $movie;
    }
}
