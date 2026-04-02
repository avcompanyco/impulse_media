<?php

namespace App\Http\Controllers\Short;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PublicShortsWatchedController extends Controller
{
    public function __invoke(Request $request)
    {
        $count = (int) $request->input('count', 0);
        session(['public_shorts_watched_count' => $count]);

        return response()->json(['ok' => true]);
    }
}
