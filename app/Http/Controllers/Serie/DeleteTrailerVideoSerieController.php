<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;

use App\Traits\Serie\HasDeleteTrailerVideoSerie;
use App\Models\Serie;

class DeleteTrailerVideoSerieController extends Controller
{
    use HasDeleteTrailerVideoSerie;

    public function __invoke(Serie $serie)
    {
        try {
            if (!$this->canAccess()) {
            throw new \Exception(__("You are not authorized to delete a serie trailer video"));
            }

            $this->deleteTrailerVideo($serie);

            return inertiaSuccessHandler(
                __("Success"),
                __("Serie trailer video deleted successfully")
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
