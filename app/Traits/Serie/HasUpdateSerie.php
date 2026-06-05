<?php

namespace App\Traits\Serie;

use App\Models\Serie;
use App\Enums\Content\ContentStatus;

trait HasUpdateSerie
{
    public function update(Serie $serie, array $data)
    {
        $serieData = collect($data)->except(['ppv_price', 'allow_membership'])->toArray();
        $serie->fill($serieData)->save();
        // verify all fields are filled
        if (!$serie->title || !$serie->description || !$serie->trailer_video || !$serie->horizontal_image || !$serie->vertical_image) {
            throw new \Exception(__("Cannot update this serie"));
        }

        if ($serie->content) {
            $serie->content->update([
                'ppv_price' => $data['ppv_price'] ?? 0.00,
                'allow_membership' => (bool)($data['allow_membership'] ?? true),
            ]);
        }

        return $serie;
    }
}
