<?php

namespace App\Enums\User;

enum UserType: string
{
    case SPECTATOR = 'spectator';
    case CREATOR = 'creator';
}
