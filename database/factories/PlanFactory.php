<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true) . ' Plan',
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 5, 100),
            'billing_period' => $this->faker->randomElement(['daily', 'monthly', 'yearly']),
            'free_days_trial' => $this->faker->numberBetween(0, 30),
            'is_unlimited_content' => $this->faker->boolean(30),
            'movies_upload_count' => $this->faker->numberBetween(1, 100),
            'series_upload_count' => $this->faker->numberBetween(1, 50),
            'shorts_upload_count' => $this->faker->numberBetween(1, 200),
            'stripe_product_id' => 'prod_' . $this->faker->regexify('[a-zA-Z0-9]{24}'),
            'stripe_price_id' => 'price_' . $this->faker->regexify('[a-zA-Z0-9]{24}'),
            'status' => 'active',
        ];
    }
}
