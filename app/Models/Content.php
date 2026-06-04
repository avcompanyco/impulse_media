<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

use App\Enums\Content\ContentType;
use App\Enums\Content\ContentStatus;

class Content extends Model
{

    protected $fillable = [
        'status',
        'type',
        'user_id',
        'views_count',
        'ppv_price',
        'allow_membership',
    ];

    protected $casts = [
        'type' => ContentType::class,
        'status' => ContentStatus::class,
        'ppv_price' => 'decimal:2',
        'allow_membership' => 'boolean',
    ];

    protected $appends = [
        'type_formatted',
        'status_formatted',
    ];

    public function typeFormatted(): Attribute
    {
        return Attribute::get(function () {
            return $this->type->value;
        });
    }

    public function statusFormatted(): Attribute
    {
        return Attribute::get(function () {
            return $this->status->value;
        });
    }

    public function contentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function isPurchasedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $this->purchases()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->exists();
    }
    
}
