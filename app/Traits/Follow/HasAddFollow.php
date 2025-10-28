<?php

namespace App\Traits\Follow;

use App\Models\User;

trait HasAddFollow
{
    public function add(User $from, User $target)
    {
        $from->follow($target->id);
    }
}
