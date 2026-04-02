<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;

class IncrementContentViewController extends Controller
{
    public function __invoke(Request $request, Content $content)
    {
        // Rate-limit: one view per content per session per 30 seconds
        $sessionKey = 'content_view_' . $content->id;
        $lastViewed = session($sessionKey);

        if ($lastViewed && now()->diffInSeconds($lastViewed) < 30) {
            return response()->json(['views_count' => $content->views_count]);
        }

        $content->increment('views_count');
        session([$sessionKey => now()]);

        return response()->json(['views_count' => $content->views_count]);
    }
}
