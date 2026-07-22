<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Content;
use App\Enums\Content\ContentStatus;
use App\Enums\Content\ContentType;
use App\Models\Short;
use App\Models\Movie;
use App\Models\Serie;

class DeleteContentController extends Controller
{

    public function __invoke(Content $content)
    {
        try {
            if (!$this->canAccess($content)) {
                throw new \Exception(__("You are not authorized to delete this content"));
            }

            DB::beginTransaction();
            if ($content->type == ContentType::SHORT) {
                $short = $content->contentable;
                if ($short instanceof Short) {
                    $short->deleteVideoShort();
                    $short->delete();
                }
            } else if ($content->type == ContentType::MOVIE) {
                $movie = $content->contentable;
                if ($movie instanceof Movie) {
                    $movie->deleteTrailerMovie();
                    $movie->deleteHorizontalImage();
                    $movie->deleteVerticalImage();
                    $movie->delete();
                }
            } else if ($content->type == ContentType::SERIE) {
                $serie = $content->contentable;
                if ($serie instanceof Serie) {
                    $serie->deleteTrailerVideoSerie();
                    $serie->deleteHorizontalImage();
                    $serie->deleteVerticalImage();
                    foreach($serie->chapters as $chapter) {
                        $chapter->deleteThumbnail();
                        $chapter->deleteChapterVideo();
                        $chapter->delete();
                    }
                    $serie->seasons()->delete();
                    $serie->delete();
                }
            }

            $content->delete();

            DB::commit();

            return inertiaSuccessHandler(
                __("Success"),
                __("Content deleted successfully")
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return inertiaErrorHandler(
                __("Error"),
                $th->getMessage()
            );
        }
    }

    public function canAccess(Content $content = null)
    {
        $userId = Auth::id();
        if (!$userId) return false;
        
        $_user = User::find($userId);
        if ($_user && $_user->hasRole('admin')) {
            return true;
        }

        if ($content && $_user && $_user->id === $content->user_id) {
            return true;
        }

        return false;
    }
}
