<?php

namespace Tests\Feature\Payment;

use App\Models\User;
use App\Models\Plan;
use App\Models\Payment;
use App\Enums\Payment\PaymentStatus;
use App\Enums\Plan\BillingPeriod;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RevenueAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin role and user
        $this->artisan('permission:create-role admin');
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_admin_can_access_revenue_endpoint()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/payments/revenue');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'month',
                'revenue'
            ]
        ]);
    }

    public function test_non_admin_cannot_access_revenue_endpoint()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->getJson('/admin/payments/revenue');

        $response->assertStatus(500); // Exception thrown for unauthorized access
    }

    public function test_monthly_revenue_calculation()
    {
        $plan = Plan::factory()->create(['price' => 10.00]);
        
        // Create payments for different months
        Payment::factory()->completed()->create([
            'plan_id' => $plan->id,
            'amount' => 10.00,
            'paid_at' => Carbon::create(2025, 1, 15),
        ]);
        
        Payment::factory()->completed()->create([
            'plan_id' => $plan->id,
            'amount' => 10.00,
            'paid_at' => Carbon::create(2025, 1, 20),
        ]);
        
        Payment::factory()->completed()->create([
            'plan_id' => $plan->id,
            'amount' => 15.00,
            'paid_at' => Carbon::create(2025, 2, 10),
        ]);

        // Failed payment should not be counted
        Payment::factory()->failed()->create([
            'plan_id' => $plan->id,
            'amount' => 20.00,
            'paid_at' => null,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/admin/payments/revenue?period=monthly&year=2025');

        $response->assertStatus(200);
        
        $data = $response->json();
        
        // Find January and February data
        $janData = collect($data)->firstWhere('month', 'Jan');
        $febData = collect($data)->firstWhere('month', 'Feb');
        
        $this->assertEquals(20.0, $janData['revenue']); // 10 + 10
        $this->assertEquals(15.0, $febData['revenue']); // 15
    }

    public function test_daily_revenue_calculation()
    {
        $plan = Plan::factory()->create(['price' => 10.00]);
        
        // Create payments for different days in January 2025
        Payment::factory()->completed()->create([
            'plan_id' => $plan->id,
            'amount' => 10.00,
            'paid_at' => Carbon::create(2025, 1, 1),
        ]);
        
        Payment::factory()->completed()->create([
            'plan_id' => $plan->id,
            'amount' => 15.00,
            'paid_at' => Carbon::create(2025, 1, 1),
        ]);
        
        Payment::factory()->completed()->create([
            'plan_id' => $plan->id,
            'amount' => 20.00,
            'paid_at' => Carbon::create(2025, 1, 2),
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/admin/payments/revenue?period=daily&year=2025&month=1');

        $response->assertStatus(200);
        
        $data = $response->json();
        
        // Find day 1 and day 2 data
        $day1Data = collect($data)->firstWhere('day', 1);
        $day2Data = collect($data)->firstWhere('day', 2);
        
        $this->assertEquals(25.0, $day1Data['revenue']); // 10 + 15
        $this->assertEquals(20.0, $day2Data['revenue']); // 20
    }

    public function test_annual_revenue_calculation()
    {
        $plan = Plan::factory()->create(['price' => 10.00]);
        
        // Create payments for different years
        Payment::factory()->completed()->create([
            'plan_id' => $plan->id,
            'amount' => 100.00,
            'paid_at' => Carbon::create(2024, 6, 15),
        ]);
        
        Payment::factory()->completed()->create([
            'plan_id' => $plan->id,
            'amount' => 200.00,
            'paid_at' => Carbon::create(2025, 6, 15),
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/admin/payments/revenue?period=annually');

        $response->assertStatus(200);
        
        $data = $response->json();
        
        // Find 2024 and 2025 data
        $data2024 = collect($data)->firstWhere('year', 2024);
        $data2025 = collect($data)->firstWhere('year', 2025);
        
        $this->assertEquals(100.0, $data2024['revenue']);
        $this->assertEquals(200.0, $data2025['revenue']);
    }
}