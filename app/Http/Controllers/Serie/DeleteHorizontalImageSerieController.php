<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;

use App\Traits\Serie\HasDeleteHorizontalImageSerie;
use App\Models\Serie;

class DeleteHorizontalImageSerieController extends Controller
{
    use HasDeleteHorizontalImageSerie;

    public function __invoke(Serie $serie)
    {
        try {
            if (!$this->canAccess()) {
            throw new \Exception(__("You are not authorized to delete a serie horizontal image"));
            }

            $this->deleteHorizontalImage($serie);

            return inertiaSuccessHandler(
                __("Success"),
                __("Serie horizontal image deleted successfully")
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
