<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Content;
use App\Models\Serie;
use App\Models\Category;
use App\Enums\Content\ChapterStatus;
use App\Enums\Content\ContentStatus;
use App\Models\SeasonSerie;
use App\Models\ChapterSerie;

class CreateChapterController extends Controller
{
    public function __invoke(Serie $serie, SeasonSerie $season)
    {
        $_user = User::find(Auth::user()->id);

        // get content with movie morph,
        $chapter = ChapterSerie::where('user_id', $_user->id)
            ->where('status', ContentStatus::DRAFT->value)
            ->where('season_id', $season->id)
            ->first();

        if (!$chapter) {
            $chapter = ChapterSerie::create([
                'chapter_number' => 0,
                'title' => '',
                'thumbnail' => '',
                'chapter_video' => '',
                'status' => ChapterStatus::DRAFT->value,
                'season_id' => $season->id,
                'user_id' => $_user->id,
            ]);
        }

        return response()->json($chapter);
    }
}
