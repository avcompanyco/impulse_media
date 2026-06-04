<?php

namespace App\Http\Controllers\Short;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\Content\ContentStatus;
use App\Models\Short;

class RandomShortController extends Controller
{

    public function __invoke(Request $request)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to publish a short"));
            }

            $previouds_short_ids = session('previous_short_ids', []);

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
            $numericIds = array_filter($previouds_short_ids, 'is_numeric');
            $shorts = Short::whereNotIn('id', $numericIds)
                ->inRandomOrder()
                ->whereHas('content', function ($query) {
                    $query->where('status', ContentStatus::PUBLISHED->value);
                })
                ->with(['content', 'user' => function ($query) {
                    $query->select('id', 'name', 'username', 'image');
                }])
                ->limit(7)
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
            $filteredTrailers = $trailers->filter(function ($item) use ($previouds_short_ids) {
                return !in_array($item['id'], $previouds_short_ids);
            })->shuffle()->take(3);

            // 4. Merge and Shuffle
            $merged = $shorts->merge($filteredTrailers)->shuffle();

            // 5. Add follow status
            $authUser = Auth::user();
            $followedIds = [];
            if ($authUser) {
                $followedIds = $authUser->followings()->pluck('users.id')->toArray();
            }

            $formatted = $merged->map(function ($item) use ($followedIds) {
                if (isset($item['user']) && $item['user']) {
                    $item['user']->is_followed = in_array($item['user']->id, $followedIds);
                }
                return $item;
            });

            // If we ran out of shorts, reset session history
            if ($formatted->isEmpty() && !empty($previouds_short_ids)) {
                session()->forget('previous_short_ids');
                return $this->__invoke($request);
            }

            $newSessionIds = array_merge($previouds_short_ids, $merged->pluck('id')->toArray());
            session(['previous_short_ids' => $newSessionIds]);

            return response()->json([
                'shorts' => $formatted
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    public function canAccess()
    {
        return true;
    }
}
