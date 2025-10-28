<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Watchlist extends Model
{
    /** @use HasFactory<\Database\Factories\WatchlistFactory> */
    use HasFactory;

    protected $fillable = [
        'watchlistable_id',
        'watchlistable_type',
        'user_id',
    ];

    public function watchlistable()
    {
        return $this->morphTo();
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
