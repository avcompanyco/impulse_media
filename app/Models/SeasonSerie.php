<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\Content\ChapterStatus;

class SeasonSerie extends Model
{
    /** @use HasFactory<\Database\Factories\SeasonSerieFactory> */
    use HasFactory;

    protected $fillable = [
        'serie_id',
        'user_id',
    ];

    public function serie(): BelongsTo
    {
        return $this->belongsTo(Serie::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(ChapterSerie::class, 'season_id', 'id')
            ->orderBy('chapter_number', 'asc')
            ->where('status', ChapterStatus::PUBLISHED->value);
    }

    public function allChapters()
    {
        return $this->hasMany(ChapterSerie::class, 'season_id', 'id');
    }
}
