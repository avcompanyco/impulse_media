<?php

namespace App\Http\Controllers\Short;

use App\Http\Controllers\Controller;
use App\Enums\Content\ContentStatus;
use App\Models\Short;
use Illuminate\Http\Request;

class PublicRandomShortController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $previousShortIds = session('public_previous_short_ids', []);

            $shorts = Short::whereNotIn('id', $previousShortIds)
                ->inRandomOrder()
                ->whereHas('content', function ($query) {
                    $query->where('status', ContentStatus::PUBLISHED->value);
                })
                ->with(['content', 'user' => function ($query) {
                    $query->select('id', 'name', 'username', 'image');
                }])
                ->limit(10)
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
                    ->limit(10)
                    ->get();
            }

            $newIds = array_merge($previousShortIds, $shorts->pluck('id')->toArray());
            session(['public_previous_short_ids' => $newIds]);

            return response()->json([
                'shorts' => $shorts,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['shorts' => []]);
        }
    }
}
