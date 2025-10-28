<?php

namespace App\Traits\Serie;

use App\Models\Serie;
use App\Enums\Content\ContentStatus;

trait HasUpdateSerie
{
    public function update(Serie $serie, array $data)
    {
        $serie->fill($data)->save();
        // verify all fields are filled
        if (!$serie->title || !$serie->description || !$serie->trailer_video || !$serie->horizontal_image || !$serie->vertical_image) {
            throw new \Exception(__("Cannot update this serie"));
        }

        return $serie;
    }
}
