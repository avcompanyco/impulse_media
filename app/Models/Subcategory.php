<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subcategory extends Model
{
    /** @use HasFactory<\Database\Factories\SubcategoryFactory> */
    use HasFactory;
    
    protected $fillable = [
        'name',
        'category_id',
    ];

    /**
     * Get the category that owns the subcategory
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function movies(): HasMany
    {
        return $this->hasMany(Movie::class);
    }

    public function series(): HasMany
    {
        return $this->hasMany(Serie::class);
    }

    public function hasMovies(): bool
    {
        return $this->movies()->count() > 0;
    }

    public function hasSeries(): bool
    {
        return $this->series()->count() > 0;
    }


}
