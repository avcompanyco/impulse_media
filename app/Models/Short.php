<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\Short\HasVideoShort;

class Short extends Model
{
    /** @use HasFactory<\Database\Factories\ShortFactory> */
    use HasFactory, HasVideoShort;

    protected $fillable = [
        'short_video',
        'text_caption',
        'user_id',
    ];

    protected $appends = [
        'short_video_url',
        'url_path',
    ];

    public function content(): MorphOne
    {
        return $this->morphOne(Content::class, 'contentable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlPathAttribute()
    {
        return route('user.short.show', $this->id);
    }

    public function deleteAll()
    {
        $this->deleteVideoShort();
        $this->content()->delete();
    }

}
