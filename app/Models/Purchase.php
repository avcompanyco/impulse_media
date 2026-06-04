<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    protected $fillable = [
        'user_id',
        'content_id',
        'amount',
        'creator_share',
        'platform_share',
        'stripe_payment_intent_id',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'creator_share' => 'decimal:2',
        'platform_share' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }
}
