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
        // 1. Add fields to contents table
        Schema::table('contents', function (Blueprint $table) {
            $table->decimal('ppv_price', 8, 2)->default(0.00);
            $table->boolean('allow_membership')->default(true);
        });

        // 2. Create settings table
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, float, boolean
            $table->timestamps();
        });

        // 3. Create purchases table (PPV transactions)
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
            $table->decimal('amount', 8, 2);
            $table->decimal('creator_share', 8, 2);
            $table->decimal('platform_share', 8, 2);
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('status')->default('completed'); // completed, refunded
            $table->timestamps();
        });

        // 4. Create watch_logs table (used to compute watch time ratio for membership revenue distribution)
        Schema::create('watch_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
            $table->unsignedInteger('duration_seconds'); // duration in seconds (e.g. 10s segments)
            $table->timestamps();
        });

        // 5. Create payouts table (creator withdrawal requests)
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 8, 2);
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->string('payout_method'); // paypal, bank_transfer, etc.
            $table->text('payout_details'); // e.g. PayPal email or bank details
            $table->text('rejection_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // 6. Create creator_earnings table (transaction log of all creator earnings)
        Schema::create('creator_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 8, 2);
            $table->string('source'); // ppv, membership
            $table->unsignedBigInteger('source_id')->nullable(); // reference to purchase_id or monthly calc log
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creator_earnings');
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('watch_logs');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('settings');

        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn(['ppv_price', 'allow_membership']);
        });
    }
};
