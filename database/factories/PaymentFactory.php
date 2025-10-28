<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use App\Models\Plan;
use App\Enums\Payment\PaymentStatus;
use App\Enums\Plan\BillingPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $billingPeriod = $this->faker->randomElement(BillingPeriod::cases());
        $status = $this->faker->randomElement(PaymentStatus::cases());
        
        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'amount' => $this->faker->randomFloat(2, 5, 100),
            'currency' => 'USD',
            'status' => $status,
            'stripe_payment_intent_id' => 'pi_' . $this->faker->regexify('[a-zA-Z0-9]{24}'),
            'stripe_subscription_id' => 'sub_' . $this->faker->regexify('[a-zA-Z0-9]{24}'),
            'stripe_invoice_id' => 'in_' . $this->faker->regexify('[a-zA-Z0-9]{24}'),
            'stripe_customer_id' => 'cus_' . $this->faker->regexify('[a-zA-Z0-9]{24}'),
            'payment_method' => 'stripe',
            'billing_period' => $billingPeriod,
            'paid_at' => $status === PaymentStatus::COMPLETED ? $this->faker->dateTimeBetween('-1 year', 'now') : null,
            'failed_at' => $status === PaymentStatus::FAILED ? $this->faker->dateTimeBetween('-1 year', 'now') : null,
            'refunded_at' => $status === PaymentStatus::REFUNDED ? $this->faker->dateTimeBetween('-1 year', 'now') : null,
            'metadata' => [
                'invoice_number' => $this->faker->regexify('[A-Z0-9]{8}'),
                'billing_reason' => $this->faker->randomElement(['subscription_cycle', 'subscription_create', 'subscription_update']),
            ],
        ];
    }

    /**
     * Indicate that the payment is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::COMPLETED,
            'paid_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'failed_at' => null,
            'refunded_at' => null,
        ]);
    }

    /**
     * Indicate that the payment failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::FAILED,
            'paid_at' => null,
            'failed_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'refunded_at' => null,
        ]);
    }

    /**
     * Indicate that the payment was refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::REFUNDED,
            'paid_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'failed_at' => null,
            'refunded_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}