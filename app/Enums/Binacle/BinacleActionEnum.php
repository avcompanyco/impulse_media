<?php

namespace App\Enums\Binacle;

enum BinacleActionEnum: string
{
    case USER_REGISTERED = 'user_registered';
    case USER_SUBSCRIPTION_NEW = 'user_subscription_new';
    case USER_SUBSCRIPTION_CANCELLED = 'user_subscription_cancelled';
    case CONTENT_MOVIE_UPLOADED = 'content_movie_uploaded';
    case CONTENT_SERIE_UPLOADED = 'content_serie_uploaded';
    case CONTENT_SHORT_UPLOADED = 'content_short_uploaded';
}
