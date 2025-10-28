<?php

namespace App\Traits\Movie;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Models\Movie;

trait HasDeleteMovie
{
    public function delete(Movie $movie)
    {

        try {
            DB::beginTransaction();
            $movie->deleteHorizontalImage();
            $movie->deleteVerticalImage();
            $movie->deleteTrailerMovie();
            $movie->deleteMovie();

            $movie->content->delete();
            $movie->watchlists->each(function ($watchlist) {
                $watchlist->delete();
            });

            $movie->delete();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            dd($th);
            throw $th;
        }
    }
}
