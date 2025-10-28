<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Category\HasImageCategory;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory, HasImageCategory;

    protected $fillable = [
        'name',
        'image',
    ];

    protected $appends = [
        'image_url',
    ];
    
    /**
     * Get all subcategories for the category
     */
    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class);
    }
    
    /**
     * Check if category has subcategories
     */
    public function hasSubcategories(): bool
    {
        return $this->subcategories()->count() > 0;
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
