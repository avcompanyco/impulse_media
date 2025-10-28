<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\Serie\HasThumbnailChapter;
use App\Traits\Serie\HasVideoChapter;
use App\Enums\Content\ChapterStatus;

class ChapterSerie extends Model
{
    /** @use HasFactory<\Database\Factories\ChapterSerieFactory> */
    use HasFactory;
    use HasThumbnailChapter, HasVideoChapter;

    protected $fillable = [
        'chapter_number',
        'title',
        'thumbnail',
        'chapter_video',
        'status',
        'season_id',
        'user_id',
    ];

    protected $appends = [
        'chapter_video_url',
        'thumbnail_url',
    ];

    protected $casts = [
        'status' => ChapterStatus::class,
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(SeasonSerie::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deleteAll()
    {
        $this->deleteThumbnail();
        $this->deleteChapterVideo();
    }
}
