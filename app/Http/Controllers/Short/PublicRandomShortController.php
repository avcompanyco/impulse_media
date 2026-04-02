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

            $shorts = Short::whereNotIn('id', $previousShortIds)
                ->inRandomOrder()
                ->whereHas('content', function ($query) {
                    $query->where('status', ContentStatus::PUBLISHED->value);
                })
                ->with(['content', 'user' => function ($query) {
                    $query->select('id', 'name', 'username', 'image');
                }])
                ->limit($batchSize)
                ->get();

            // If we've shown all shorts, reset and start over
            if ($shorts->isEmpty() && !empty($previousShortIds)) {
                session()->forget('public_previous_short_ids');
                $shorts = Short::inRandomOrder()
                    ->whereHas('content', function ($query) {
                        $query->where('status', ContentStatus::PUBLISHED->value);
                    })
                    ->with(['content', 'user' => function ($query) {
                        $query->select('id', 'name', 'username', 'image');
                    }])
                    ->limit($batchSize)
                    ->get();
            }

            $newIds = array_merge($previousShortIds, $shorts->pluck('id')->toArray());
            session(['public_previous_short_ids' => $newIds]);

            return response()->json([
                'shorts' => $shorts,
                'limit_reached' => false,
                'watched_count' => $watchedCount,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['shorts' => [], 'limit_reached' => false, 'watched_count' => 0]);
        }
    }
}
