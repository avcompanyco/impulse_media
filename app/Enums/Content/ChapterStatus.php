<?php

namespace App\Enums\Content;

enum ChapterStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case PAUSED = 'paused';
}
