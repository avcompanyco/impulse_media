<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\User;
use App\Models\Serie;
use App\Enums\Content\ContentType;
use App\Enums\Content\ContentStatus;
use App\Models\SeasonSerie;

class GetSeasonSerieController extends Controller
{
    public function __invoke(Serie $serie)
    {
        $_user = User::find(Auth::user()->id);

        if ($serie->user_id !== $_user->id) {
            return inertiaErrorHandler(
                __("Error"),
                __("You are not authorized to get the seasons for a serie")
            );
        }

        // get content with movie morph,
        $seasons = SeasonSerie::where('serie_id', $serie->id)
            ->with('chapters')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json($seasons);
    }
}
