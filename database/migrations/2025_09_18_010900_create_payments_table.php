<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            // User and plan relationships
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('plan_id')
                ->constrained('plans')
                ->onDelete('cascade');
            
            // Payment details
            $table->decimal('amount', 10, 2); // Amount in the currency
            $table->string('currency', 3)->default('USD'); // ISO currency code
            $table->string('status'); // completed, failed, pending, refunded, cancelled
            
            // Stripe-specific fields
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->string('stripe_invoice_id')->nullable();
            $table->string('stripe_customer_id')->nullable();
            
            // Payment metadata
            $table->string('payment_method')->default('stripe'); // For future payment providers
            $table->json('metadata')->nullable(); // Additional data like refund reasons, etc.
            
            // Billing period for analytics
            $table->string('billing_period'); // daily, monthly, yearly
            
            // Timestamps for payment events
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['plan_id', 'status']);
            $table->index(['status', 'paid_at']);
            $table->index(['billing_period', 'paid_at']);
            $table->index('stripe_payment_intent_id');
            $table->index('stripe_subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};