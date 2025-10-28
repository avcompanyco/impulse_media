<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;
use App\Http\Requests\Serie\UpdateChapterRequest;
use App\Traits\Serie\HasUpdateChapter;

use App\Models\Serie;
use App\Models\SeasonSerie;
use App\Models\ChapterSerie;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UpdateChapterController extends Controller
{
    use HasUpdateChapter;

    public function __invoke(UpdateChapterRequest $request, Serie $serie, SeasonSerie $season, ChapterSerie $chapter)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to update a chapter"));
            }

            $_user = User::find(Auth::user()->id);
            if ($_user->id !== $chapter->user_id) {
                throw new \Exception(__("You are not authorized to update this chapter"));
            }

            if ($season->id !== $chapter->season_id) {
                throw new \Exception(__("Chapter not found"));
            }

            $data = $request->validated();
            $this->update($chapter, $data);
            return inertiaSuccessHandler(
                __("Success"),
                __("Chapter updated successfully")
            );
        } catch (\Throwable $th) {
            return inertiaErrorHandler(
                __("Error"),
                $th->getMessage()
            );
        }
    }

    public function canAccess()
    {
        return true;
    }
}
