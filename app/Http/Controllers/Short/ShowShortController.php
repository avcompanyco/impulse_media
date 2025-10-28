<?php

namespace App\Http\Controllers\Short;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\User;
use App\Models\Content;
use App\Models\Short;
use App\Models\Category;
use App\Enums\Content\ContentType;
use App\Enums\Content\ContentStatus;
use Illuminate\Support\Facades\Request;

class ShowShortController extends Controller
{
    public function __invoke(Request $request)
    {
        $short = request('short', null);
        if (!$short) {
            $short = Short::whereHas('content', function ($query) {
                $query->where('status', ContentStatus::PUBLISHED->value);
            })->inRandomOrder()->first();
        } else {
            $short = Short::where('id', $short)->whereHas('content', function ($query) {
                $query->where('status', ContentStatus::PUBLISHED->value);
            })->first();
        }

        if (!$short) {
            return redirect()->route('dashboard')->with('error', 'Short not found');
        }

        $short = $short->load(['content']);

        return Inertia::render('user/short/ShowShort', [
            'short' => $short
        ]);
    }
}
