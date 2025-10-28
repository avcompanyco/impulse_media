<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::create([
            'name' => 'Free',
            'description' => 'Free plan',
            'price' => 0,
            'billing_period' => 'monthly',
            'free_days_trial' => 0,
            'is_unlimited_content' => false,
            'movies_upload_count' => 1,
            'series_upload_count' => 0,
            'shorts_upload_count' => 3,
        ]);

        // silver: movies 5, series 2, shorts 20
        Plan::create([
            'name' => 'Silver',
            'description' => 'Limited contents to enjoy and upload in your account.',
            'price' => 25,
            'billing_period' => 'monthly',
            'free_days_trial' => 0,
            'is_unlimited_content' => false,
            'movies_upload_count' => 5,
            'series_upload_count' => 2,
            'shorts_upload_count' => 20,
        ]);

        // gold: movies 10, series 5, shorts 50
        Plan::create([
            'name' => 'Gold',
            'description' => 'Unlimited contents to enjoy and upload in your account.',
            'price' => 50,
            'billing_period' => 'monthly',
            'free_days_trial' => 0,
            'is_unlimited_content' => true,
            'movies_upload_count' => 0,
            'series_upload_count' => 0,
            'shorts_upload_count' => 0,
        ]);

    }
}
