<?php

namespace App\Models;

use App\Enums\Plan\BillingPeriod;
use App\Enums\Payment\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id', 
        'amount',
        'currency',
        'status',
        'stripe_payment_intent_id',
        'stripe_subscription_id',
        'stripe_invoice_id',
        'stripe_customer_id',
        'payment_method',
        'metadata',
        'billing_period',
        'paid_at',
        'failed_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'billing_period' => BillingPeriod::class,
        'status' => PaymentStatus::class,
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    protected $appends = [
        'amount_formatted',
    ];

    /**
     * Get the user that made the payment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plan that was purchased
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Format amount with currency
     */
    public function amountFormatted(): Attribute
    {
        return Attribute::get(function () {
            return '$' . number_format($this->amount, 2) . ' ' . $this->currency;
        });
    }

    /**
     * Scope for successful payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', PaymentStatus::COMPLETED);
    }

    /**
     * Scope for failed payments
     */
    public function scopeFailed($query)
    {
        return $query->where('status', PaymentStatus::FAILED);
    }

    /**
     * Scope for refunded payments
     */
    public function scopeRefunded($query)
    {
        return $query->where('status', PaymentStatus::REFUNDED);
    }

    /**
     * Scope for payments in a date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('paid_at', [$startDate, $endDate]);
    }

    /**
     * Scope for payments by billing period
     */
    public function scopeByBillingPeriod($query, $billingPeriod)
    {
        return $query->where('billing_period', $billingPeriod);
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => PaymentStatus::COMPLETED,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed()
    {
        $this->update([
            'status' => PaymentStatus::FAILED,
            'failed_at' => now(),
        ]);
    }

    /**
     * Mark payment as refunded
     */
    public function markAsRefunded()
    {
        $this->update([
            'status' => PaymentStatus::REFUNDED,
            'refunded_at' => now(),
        ]);
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === PaymentStatus::COMPLETED;
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return $this->status === PaymentStatus::FAILED;
    }

    /**
     * Check if payment is refunded
     */
    public function isRefunded(): bool
    {
        return $this->status === PaymentStatus::REFUNDED;
    }
}