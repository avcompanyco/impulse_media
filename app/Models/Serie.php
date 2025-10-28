<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

use App\Traits\Serie\HasHorizontalImageSerie;
use App\Traits\Serie\HasVerticalImageSerie;
use App\Traits\Serie\HasTrailerVideoSerie;

class Serie extends Model
{
    /** @use HasFactory<\Database\Factories\SerieFactory> */
    use HasFactory;
    use HasHorizontalImageSerie, HasVerticalImageSerie, HasTrailerVideoSerie;

    protected $fillable = [
        'title',
        'description',
        'vertical_image',
        'horizontal_image',
        'trailer_video',
        'user_id',
        'category_id',
        'subcategory_id',
    ];

    protected $appends = [
        'horizontal_image_url',
        'vertical_image_url',
        'trailer_video_url',
        'url_path',
    ];

    public function content(): MorphOne
    {
        return $this->morphOne(Content::class, 'contentable');
    }

    public function watchlist(): MorphOne
    {
        return $this->morphOne(Watchlist::class, 'watchlistable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(SeasonSerie::class);
    }

    public function chapters(): HasManyThrough
    {
        return $this->hasManyThrough(ChapterSerie::class, SeasonSerie::class, 'serie_id', 'season_id');
    }

    public function getUrlPathAttribute()
    {
        return route('user.serie.show', $this->id);
    }

    public function deleteAll()
    {
        $this->deleteHorizontalImage();
        $this->deleteVerticalImage();
        $this->deleteTrailerVideoSerie();
        $this->content()->delete();
        $this->watchlist()->delete();
        foreach($this->chapters as $chapter) {
            $chapter->deleteAll();
            $chapter->delete();
        }
        $this->seasons()->delete();
    }

}
