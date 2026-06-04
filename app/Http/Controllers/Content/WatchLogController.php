<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Content;
use App\Models\WatchLog;

class WatchLogController extends Controller
{
    public function __invoke(Request $request, Content $content)
    {
        try {
            $data = $request->validate([
                'duration_seconds' => 'required|integer|min:1|max:60',
            ]);

            WatchLog::create([
                'user_id' => Auth::id(),
                'content_id' => $content->id,
                'duration_seconds' => $data['duration_seconds'],
            ]);

            return response()->json(['status' => 'logged']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 400);
        }
    }
}
