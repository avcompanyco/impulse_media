<?php

namespace App\Traits\Follow;

use App\Models\User;

trait HasRemoveFollow
{
    public function remove(User $from, User $target)
    {
        $from->unfollow($target->id);
    }
}
