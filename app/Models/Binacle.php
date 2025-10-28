<?php

namespace App\Models;

use App\Enums\Binacle\BinacleActionEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Binacle extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'details',
    ];

    protected $casts = [
        'action' => BinacleActionEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
