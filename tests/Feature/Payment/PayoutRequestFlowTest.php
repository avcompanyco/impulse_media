<?php

namespace Tests\Feature\Payment;

use Tests\TestCase;
use App\Models\User;
use App\Models\Payout;
use App\Models\Setting;
use App\Models\Plan;
use App\Models\CreatorEarning;
use App\Enums\User\UserType;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PayoutRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    protected Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('min_payout_threshold', 50.00, 'float');

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'creator']);
        Role::firstOrCreate(['name' => 'spectator']);
        Role::firstOrCreate(['name' => 'user']);

        $this->plan = Plan::factory()->create([
            'status' => 'active',
            'plan_type' => 'creator',
        ]);
    }

    private function createCreator(array $attributes = []): User
    {
        $creator = User::factory()->create(array_merge([
            'user_type' => UserType::CREATOR,
            'plan_id' => $this->plan->id,
        ], $attributes));
        $creator->assignRole('creator');
        return $creator;
    }

    public function test_spectator_cannot_request_payout()
    {
        $spectator = User::factory()->create([
            'user_type' => UserType::SPECTATOR,
        ]);
        $spectator->assignRole('spectator');

        CreatorEarning::create([
            'creator_id' => $spectator->id,
            'amount' => 100.00,
            'source' => 'ppv_sale',
            'description' => 'Test Earning',
        ]);

        $response = $this->actingAs($spectator)
            ->from('/creator/dashboard')
            ->post('/creator/payout-request', [
                'amount' => 50.00,
                'payout_method' => 'paypal',
                'payout_details' => 'spectator@example.com',
            ]);

        $response->assertRedirect('/creator/dashboard');
        $response->assertSessionHasErrors(['amount']);
        $this->assertDatabaseCount('payouts', 0);
    }

    public function test_creator_cannot_request_below_threshold()
    {
        $creator = $this->createCreator();

        CreatorEarning::create([
            'creator_id' => $creator->id,
            'amount' => 30.00,
            'source' => 'ppv_sale',
            'description' => 'Test Earning',
        ]);

        $response = $this->actingAs($creator)
            ->from('/creator/dashboard')
            ->post('/creator/payout-request', [
                'amount' => 30.00,
                'payout_method' => 'paypal',
                'payout_details' => 'creator@example.com',
            ]);

        $response->assertRedirect('/creator/dashboard');
        $response->assertSessionHasErrors(['amount']);
        $this->assertDatabaseCount('payouts', 0);
    }

    public function test_creator_paypal_validation()
    {
        $creator = $this->createCreator();

        CreatorEarning::create([
            'creator_id' => $creator->id,
            'amount' => 100.00,
            'source' => 'ppv_sale',
            'description' => 'Test Earning',
        ]);

        $response = $this->actingAs($creator)
            ->from('/creator/dashboard')
            ->post('/creator/payout-request', [
                'amount' => 50.00,
                'payout_method' => 'paypal',
                'payout_details' => 'invalid-email-format',
            ]);

        $response->assertRedirect('/creator/dashboard');
        $response->assertSessionHasErrors(['payout_details']);
        $this->assertDatabaseCount('payouts', 0);
    }

    public function test_creator_bank_transfer_validation()
    {
        $creator = $this->createCreator();

        CreatorEarning::create([
            'creator_id' => $creator->id,
            'amount' => 100.00,
            'source' => 'ppv_sale',
            'description' => 'Test Earning',
        ]);

        $response = $this->actingAs($creator)
            ->from('/creator/dashboard')
            ->post('/creator/payout-request', [
                'amount' => 50.00,
                'payout_method' => 'bank_transfer',
                'payout_details' => 'Short info',
            ]);

        $response->assertRedirect('/creator/dashboard');
        $response->assertSessionHasErrors(['payout_details']);
        $this->assertDatabaseCount('payouts', 0);
    }

    public function test_creator_can_submit_valid_payout_request()
    {
        $creator = $this->createCreator();

        CreatorEarning::create([
            'creator_id' => $creator->id,
            'amount' => 150.00,
            'source' => 'ppv_sale',
            'description' => 'Test Earning',
        ]);

        $bankDetails = "Bank: Chase\nAccount: 1234567890\nHolder: Test Creator\nSWIFT: CHASEUS33";

        $response = $this->actingAs($creator)
            ->from('/creator/dashboard')
            ->post('/creator/payout-request', [
                'amount' => 100.00,
                'payout_method' => 'bank_transfer',
                'payout_details' => $bankDetails,
            ]);

        $response->assertRedirect('/creator/dashboard');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('payouts', [
            'creator_id' => $creator->id,
            'amount' => 100.00,
            'status' => 'pending',
            'payout_method' => 'bank_transfer',
            'payout_details' => $bankDetails,
        ]);

        $creator->refresh();
        $this->assertEquals('bank_transfer', $creator->payout_method);
        $this->assertEquals($bankDetails, $creator->payout_details);
    }

    public function test_admin_can_approve_payout_with_receipt()
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'user_type' => UserType::ADMIN,
        ]);
        $admin->assignRole('admin');

        $creator = $this->createCreator();

        CreatorEarning::create([
            'creator_id' => $creator->id,
            'amount' => 200.00,
            'source' => 'ppv_sale',
            'description' => 'Test Earning',
        ]);

        $payout = Payout::create([
            'creator_id' => $creator->id,
            'amount' => 100.00,
            'status' => 'pending',
            'payout_method' => 'paypal',
            'payout_details' => 'creator@example.com',
        ]);

        $receipt = UploadedFile::fake()->create('receipt.pdf', 500, 'application/pdf');

        $response = $this->actingAs($admin)
            ->from('/admin/payouts')
            ->put("/admin/payouts/{$payout->id}", [
                'status' => 'approved',
                'transaction_reference' => 'PAYPAL-TX-99887766',
                'receipt' => $receipt,
            ]);

        $response->assertRedirect('/admin/payouts');
        $response->assertSessionHas('success');

        $payout->refresh();
        $this->assertEquals('approved', $payout->status);
        $this->assertEquals('PAYPAL-TX-99887766', $payout->transaction_reference);
        $this->assertNotNull($payout->receipt_path);
        $this->assertNotNull($payout->processed_at);

        Storage::disk('public')->assertExists($payout->receipt_path);
    }

    public function test_admin_can_reject_payout_with_reason()
    {
        $admin = User::factory()->create([
            'user_type' => UserType::ADMIN,
        ]);
        $admin->assignRole('admin');

        $creator = $this->createCreator();

        CreatorEarning::create([
            'creator_id' => $creator->id,
            'amount' => 200.00,
            'source' => 'ppv_sale',
            'description' => 'Test Earning',
        ]);

        $payout = Payout::create([
            'creator_id' => $creator->id,
            'amount' => 100.00,
            'status' => 'pending',
            'payout_method' => 'paypal',
            'payout_details' => 'creator@example.com',
        ]);

        $response = $this->actingAs($admin)
            ->from('/admin/payouts')
            ->put("/admin/payouts/{$payout->id}", [
                'status' => 'rejected',
                'rejection_reason' => 'Invalid PayPal account provided.',
            ]);

        $response->assertRedirect('/admin/payouts');
        $response->assertSessionHas('success');

        $payout->refresh();
        $this->assertEquals('rejected', $payout->status);
        $this->assertEquals('Invalid PayPal account provided.', $payout->rejection_reason);
        $this->assertNotNull($payout->processed_at);
    }
}
