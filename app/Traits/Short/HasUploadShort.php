<?php

namespace App\Traits\Short;

use App\Models\Short;

trait HasUploadShort
{
    public function uploadShort(Short $short, array $data)
    {
        $short->update($data);
        return $short;
    }
}
