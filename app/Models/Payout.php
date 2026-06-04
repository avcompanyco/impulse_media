<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payout extends Model
{
    protected $fillable = [
        'creator_id',
        'amount',
        'status',
        'payout_method',
        'payout_details',
        'rejection_reason',
        'transaction_reference',
        'receipt_path',
        'processed_at',
    ];

    protected $appends = [
        'receipt_url',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_path ? \Illuminate\Support\Facades\Storage::url($this->receipt_path) : null;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
