<?php

namespace App\Http\Controllers\Short;

use App\Http\Controllers\Controller;
use App\Enums\Content\ContentStatus;
use App\Models\Short;
use Illuminate\Http\Request;

class PublicRandomShortController extends Controller
{
    private const MAX_PUBLIC_SHORTS = 7;

    public function __invoke(Request $request)
    {
        try {
            // Check if the user has already reached the public shorts limit
            $watchedCount = session('public_shorts_watched_count', 0);

            if ($watchedCount >= self::MAX_PUBLIC_SHORTS) {
                return response()->json([
                    'shorts' => [],
                    'limit_reached' => true,
                    'watched_count' => $watchedCount,
                ]);
            }

            $previousShortIds = session('public_previous_short_ids', []);

            // Only load as many shorts as needed to reach the limit
            $remaining = self::MAX_PUBLIC_SHORTS - $watchedCount;
            $batchSize = min(10, $remaining);

            // 1. Fetch movies and series with trailers
            $movies = \App\Models\Movie::whereHas('content', function ($query) {
                $query->where('status', ContentStatus::PUBLISHED->value);
            })->where('trailer_video', '!=', '')
              ->whereNotNull('trailer_video')
              ->with(['content', 'user' => function ($query) {
                  $query->select('id', 'name', 'username', 'image');
              }])->inRandomOrder()->limit(10)->get();

            $series = \App\Models\Serie::whereHas('content', function ($query) {
                $query->where('status', ContentStatus::PUBLISHED->value);
            })->where('trailer_video', '!=', '')
              ->whereNotNull('trailer_video')
              ->with(['content', 'user' => function ($query) {
                  $query->select('id', 'name', 'username', 'image');
              }])->inRandomOrder()->limit(10)->get();

            $trailers = collect();

            foreach ($movies as $movie) {
                $trailers->push([
                    'id' => 'movie_' . $movie->id,
                    'short_video_url' => $movie->trailer_video_url,
                    'text_caption' => $movie->title . "\n\n" . $movie->description,
                    'user' => $movie->user,
                    'content' => $movie->content,
                    'is_trailer' => true,
                    'content_type' => 'movie',
                    'contentable_id' => $movie->id,
                ]);
            }

            foreach ($series as $serie) {
                $trailers->push([
                    'id' => 'serie_' . $serie->id,
                    'short_video_url' => $serie->trailer_video_url,
                    'text_caption' => $serie->title . "\n\n" . $serie->description,
                    'user' => $serie->user,
                    'content' => $serie->content,
                    'is_trailer' => true,
                    'content_type' => 'serie',
                    'contentable_id' => $serie->id,
                ]);
            }

            // 2. Fetch regular shorts
            $numericIds = array_filter($previousShortIds, 'is_numeric');
            $shorts = Short::whereNotIn('id', $numericIds)
                ->inRandomOrder()
                ->whereHas('content', function ($query) {
                    $query->where('status', ContentStatus::PUBLISHED->value);
                })
                ->with(['content', 'user' => function ($query) {
                    $query->select('id', 'name', 'username', 'image');
                }])
                ->limit($batchSize)
                ->get()
                ->map(function ($short) {
                    return [
                        'id' => $short->id,
                        'short_video_url' => $short->short_video_url,
                        'text_caption' => $short->text_caption,
                        'user' => $short->user,
                        'content' => $short->content,
                        'is_trailer' => false,
                    ];
                });

            // 3. Filter trailers
            $filteredTrailers = $trailers->filter(function ($item) use ($previousShortIds) {
                return !in_array($item['id'], $previousShortIds);
            })->shuffle()->take(3);

            // 4. Merge and Shuffle
            $merged = $shorts->merge($filteredTrailers)->shuffle()->take($batchSize);

            // If we've shown all shorts, reset and start over
            if ($merged->isEmpty() && !empty($previousShortIds)) {
                session()->forget('public_previous_short_ids');
                return $this->__invoke($request);
            }

            $newIds = array_merge($previousShortIds, $merged->pluck('id')->toArray());
            session(['public_previous_short_ids' => $newIds]);

            return response()->json([
                'shorts' => $merged,
                'limit_reached' => false,
                'watched_count' => $watchedCount,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['shorts' => [], 'limit_reached' => false, 'watched_count' => 0, 'error' => $th->getMessage()]);
        }
    }
}
