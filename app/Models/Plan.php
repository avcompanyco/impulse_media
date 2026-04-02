<?php

namespace App\Models;

use App\Enums\Plan\BillingPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Plan extends Model
{
    /** @use HasFactory<\Database\Factories\PlanFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_period',
        'free_days_trial',
        'is_unlimited_content',
        'movies_upload_count',
        'series_upload_count',
        'shorts_upload_count',
        'stripe_product_id',
        'stripe_price_id',
        'status',
        'plan_type',
        'has_ads',
    ];

    protected $casts = [
        'is_unlimited_content' => 'boolean',
        'has_ads' => 'boolean',
        'price' => 'decimal:2',
        'billing_period' => BillingPeriod::class,
    ];
    
    protected $appends = [
        'price_formatted',
    ];

    public function priceFormatted(): Attribute
    {
        return Attribute::get(function () {
            return '$' . ' ' . number_format($this->price, 2) . ' ' . config('cashier.currency');
        });
    }

    /**
     * Check if plan has unlimited content
     */
    public function hasUnlimitedContent(): bool
    {
        return $this->is_unlimited_content;
    }

    /**
     * Get billing period options
     */
    public static function getBillingPeriodOptions(): array
    {
        return [
            'daily' => __('Daily'),
            'monthly' => __('Monthly'),
            'yearly' => __('Yearly'),
        ];
    }

    /**
     * Scope for active plans
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for spectator plans
     */
    public function scopeForSpectators($query)
    {
        return $query->where('plan_type', 'spectator');
    }

    /**
     * Scope for creator plans
     */
    public function scopeForCreators($query)
    {
        return $query->where('plan_type', 'creator');
    }

    /**
     * Get the plan's payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}

