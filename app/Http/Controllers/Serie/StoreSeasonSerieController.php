<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;
use App\Traits\Serie\HasStoreSeasonSerie;
use App\Models\Serie;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class StoreSeasonSerieController extends Controller
{
    use HasStoreSeasonSerie;

    public function __invoke(Serie $serie)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to store a season for a serie"));
            }

            $_user = User::find(Auth::user()->id);
            if ($serie->user_id !== $_user->id) {
                throw new \Exception(__("You are not authorized to store a season for a serie"));
            }

            $this->storeSeason($serie);

            return inertiaSuccessHandler(
                __("Success"),
                __("Season stored successfully")
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
