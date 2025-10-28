<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;

use App\Traits\Serie\HasDeleteSeason;
use App\Models\Serie;
use App\Models\SeasonSerie;

class DestroySeasonSerieController extends Controller
{
    use HasDeleteSeason;

    public function __invoke(Serie $serie, SeasonSerie $season)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to delete a season"));
            }

            if ($season->serie_id !== $serie->id) {
                throw new \Exception(__("Season not found in this serie"));
            }

            $this->delete($season);

            return inertiaSuccessHandler(
                __("Success"),
                __("Season deleted successfully")
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
