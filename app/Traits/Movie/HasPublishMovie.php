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
        $movieData = collect($data)->except(['ppv_price', 'allow_membership'])->toArray();
        $movie->fill($movieData)->save();
        // verify all fields are filled
        $missing = [];
        if (!$movie->title) $missing[] = 'Title';
        if (!$movie->description) $missing[] = 'Description';
        if (!$movie->movie_video) $missing[] = 'Movie Video';
        if (!$movie->horizontal_image) $missing[] = 'Horizontal Image';
        if (!$movie->vertical_image) $missing[] = 'Vertical Image';
        if (!empty($missing)) {
            throw new \Exception(__('Cannot publish this movie. Missing: ') . implode(', ', $missing));
        }

        $content = $movie->content;

        $content->fill([
            'status' => ContentStatus::PUBLISHED->value,
            'ppv_price' => $data['ppv_price'] ?? 0.00,
            'allow_membership' => (bool)($data['allow_membership'] ?? true),
        ])->save();

        // Log movie upload event
        $binacleService = app(BinacleService::class);
        $user = Auth::user();
        $binacleService->logMovieUpload($user, $movie->title);

        return $movie;
    }
}
