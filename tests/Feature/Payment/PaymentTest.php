<?php

namespace Tests\Feature\Payment;

use App\Models\User;
use App\Models\Plan;
use App\Models\Payment;
use App\Enums\Payment\PaymentStatus;
use App\Enums\Plan\BillingPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin role if it doesn't exist
        if (!class_exists('Spatie\Permission\Models\Role')) {
            $this->markTestSkipped('Spatie Permission package not fully configured for testing');
        }
    }

    public function test_payment_can_be_created()
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create([
            'billing_period' => BillingPeriod::MONTHLY,
            'price' => 9.99
        ]);

        $payment = Payment::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'amount' => 9.99,
            'currency' => 'USD',
            'status' => PaymentStatus::COMPLETED,
            'payment_method' => 'stripe',
            'billing_period' => BillingPeriod::MONTHLY,
            'paid_at' => now(),
        ]);

        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'amount' => 9.99,
            'status' => PaymentStatus::COMPLETED->value,
        ]);
    }

    public function test_payment_relationships()
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create();
        
        $payment = Payment::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        $this->assertTrue($payment->user->is($user));
        $this->assertTrue($payment->plan->is($plan));
        $this->assertTrue($user->payments()->first()->is($payment));
        $this->assertTrue($plan->payments()->first()->is($payment));
    }

    public function test_payment_status_methods()
    {
        $completedPayment = Payment::factory()->create([
            'status' => PaymentStatus::COMPLETED
        ]);
        
        $failedPayment = Payment::factory()->create([
            'status' => PaymentStatus::FAILED
        ]);

        $this->assertTrue($completedPayment->isCompleted());
        $this->assertFalse($completedPayment->isFailed());
        
        $this->assertTrue($failedPayment->isFailed());
        $this->assertFalse($failedPayment->isCompleted());
    }

    public function test_payment_can_be_marked_as_completed()
    {
        $payment = Payment::factory()->create([
            'status' => PaymentStatus::PENDING
        ]);

        $payment->markAsCompleted();

        $this->assertEquals(PaymentStatus::COMPLETED, $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
    }

    public function test_payment_scopes()
    {
        Payment::factory()->create(['status' => PaymentStatus::COMPLETED]);
        Payment::factory()->create(['status' => PaymentStatus::FAILED]);
        Payment::factory()->create(['status' => PaymentStatus::REFUNDED]);

        $this->assertEquals(1, Payment::completed()->count());
        $this->assertEquals(1, Payment::failed()->count());
        $this->assertEquals(1, Payment::refunded()->count());
    }
}