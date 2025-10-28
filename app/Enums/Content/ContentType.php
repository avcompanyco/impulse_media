<?php

namespace App\Enums\Content;

enum ContentType: string
{
    case MOVIE = 'movies';
    case SERIE = 'series';
    case SHORT = 'shorts';
}
