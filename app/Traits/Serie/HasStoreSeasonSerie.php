<?php

namespace App\Traits\Serie;

use App\Models\Serie;
use App\Enums\Content\ContentStatus;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\SeasonSerie;

trait HasStoreSeasonSerie
{
    public function storeSeason(Serie $serie)
    {

        $_user = User::find(Auth::user()->id);
        if ($serie->user_id !== $_user->id) {
            throw new \Exception(__("You are not authorized to store a season for a serie"));
        }

        $season = SeasonSerie::create([
            'serie_id' => $serie->id,
            'user_id' => $_user->id,
        ]);

        return $season;
    }
}
