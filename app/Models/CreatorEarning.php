<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorEarning extends Model
{
    protected $table = 'creator_earnings';

    protected $fillable = [
        'creator_id',
        'amount',
        'source', // ppv, membership
        'source_id',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
