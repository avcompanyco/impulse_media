<?php

namespace App\Traits\Serie;

use Illuminate\Support\Facades\DB;
use App\Models\Serie;

trait HasDeleteSerie
{
    public function delete(Serie $serie)
    {

        try {
            DB::beginTransaction();
            $serie->deleteHorizontalImage();
            $serie->deleteVerticalImage();
            $serie->deleteTrailerVideoSerie();

            $serie->content()->delete();
            $serie->watchlist()->delete();
            foreach ($serie->chapters as $chapter) {
                $chapter->deleteThumbnail();
                $chapter->deleteChapterVideo();
                $chapter->delete();
            }
            $serie->seasons()->delete();
            $serie->delete();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
