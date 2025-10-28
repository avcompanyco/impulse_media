<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;
use App\Traits\Serie\HasDeleteSerie;
use App\Models\Serie;

class DestroySerieController extends Controller
{
    use HasDeleteSerie;

    public function __invoke(Serie $serie)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to delete a serie"));
            }

            $this->delete($serie);

            return redirect()->route('channel.serie')
                ->with('success', __("Serie deleted successfully"));

        } catch (\Throwable $th) {
            return redirect()->route('channel.serie')
                ->with('error', $th->getMessage());
        }
    }

    public function canAccess()
    {
        return true;
    }
}
