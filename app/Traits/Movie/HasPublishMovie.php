<?php

namespace App\Traits\Movie;

use App\Enums\Content\ContentStatus;
use App\Models\Movie;
use App\Services\BinacleService;
use Illuminate\Support\Facades\Auth;

trait HasPublishMovie
{
    public function publish(Movie $movie, array $data)
    {
        $movie->fill($data)->save();
        // verify all fields are filled
        if (! $movie->title || ! $movie->description || ! $movie->movie_video || ! $movie->trailer_video || ! $movie->horizontal_image || ! $movie->vertical_image) {
            throw new \Exception(__('Cannot publish this movie'));
        }

        $content = $movie->content;

        $content->fill([
            'status' => ContentStatus::PUBLISHED->value,
        ])->save();

        // Log movie upload event
        $binacleService = app(BinacleService::class);
        $user = Auth::user();
        $binacleService->logMovieUpload($user, $movie->title);

        return $movie;
    }
}
