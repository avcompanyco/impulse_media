<?php

namespace App\Http\Controllers\Short;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Request;
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

            $shorts = Short::whereNotIn('id', $previouds_short_ids)->inRandomOrder()->whereHas('content', function ($query) {
                $query->where('status', ContentStatus::PUBLISHED->value);
            })->with(['content', 'user' => function ($query) {
                $query->select('id', 'name', 'username', 'image');
            }])->limit(10)->get();

            $previous_short_ids = array_merge($previouds_short_ids, $shorts->pluck('id')->toArray());
            session(['previous_short_ids' => $previous_short_ids]);

            return response()->json([
                'shorts' => $shorts
            ]);
        } catch (\Throwable $th) {
            return response()->json([]);
        }
    }

    public function canAccess()
    {
        return true;
    }
}
