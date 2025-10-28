<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Movie\HasMovie;
use App\Traits\Movie\HasTrailerMovie;
use App\Traits\Movie\HasHorizontalImageMovie;
use App\Traits\Movie\HasVerticalImageMovie;
use Illuminate\Support\Facades\Auth;

class Movie extends Model
{
    /** @use HasFactory<\Database\Factories\MovieFactory> */
    use HasFactory, HasMovie, HasTrailerMovie, HasHorizontalImageMovie, HasVerticalImageMovie;

    protected $fillable = [
        'title',
        'description',
        'movie_video',
        'trailer_video',
        'horizontal_image',
        'vertical_image',
        'user_id',
        'category_id',
        'subcategory_id',
    ];

    protected $appends = [
        'movie_video_url',
        'trailer_video_url',
        'horizontal_image_url',
        'vertical_image_url',
        'url_path',
    ];

    public function getUrlPathAttribute()
    {
        return route('user.movie.show', $this->id);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function content(): MorphOne
    {
        return $this->morphOne(Content::class, 'contentable');
    }

    public function watchlist(): MorphOne
    {
        $_user = Auth::user();
        $query = $this->morphOne(Watchlist::class, 'watchlistable');
        if ($_user) {
            $query->where('user_id', $_user->id);
        }
        return $query;
    }

    public function watchlists(): MorphMany
    {
        return $this->morphMany(Watchlist::class, 'watchlistable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deleteAll()
    {
        $this->deleteHorizontalImage();
        $this->deleteVerticalImage();
        $this->deleteTrailerMovie();
        $this->deleteMovie();
        $this->content()->delete();
        $this->watchlists()->delete();
    }
}
