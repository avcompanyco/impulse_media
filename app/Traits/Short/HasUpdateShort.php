<?php

namespace App\Traits\Short;

use App\Models\Short;
use App\Enums\Content\ContentStatus;

trait HasUpdateShort
{
    public function update(Short $short, array $data)
    {
        $short->fill($data)->save();
        // verify all fields are filled
        if (!$short->text_caption || !$short->short_video) {
            throw new \Exception(__("Cannot publish this short"));
        }

        $short->fill($data)->save();

        return $short;
    }
}
