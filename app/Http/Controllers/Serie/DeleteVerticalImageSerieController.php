<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;

use App\Traits\Serie\HasDeleteVerticalImageSerie;
use App\Models\Serie;

class DeleteVerticalImageSerieController extends Controller
{
    use HasDeleteVerticalImageSerie;

    public function __invoke(Serie $serie)
    {
        try {
            if (!$this->canAccess()) {
            throw new \Exception(__("You are not authorized to delete a serie vertical image"));
            }

            $this->deleteVerticalImage($serie);

            return inertiaSuccessHandler(
                __("Success"),
                __("Serie vertical image deleted successfully")
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
