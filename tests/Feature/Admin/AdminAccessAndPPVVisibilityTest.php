<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Purchase;
use App\Models\Content;
use App\Models\Movie;
use App\Models\Payment;
use App\Enums\User\UserType;
use App\Enums\Payment\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAccessAndPPVVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'creator']);
        Role::firstOrCreate(['name' => 'spectator']);
    }

    public function test_admin_user_can_access_admin_dashboard_without_subscription_interception(): void
    {
        $admin = User::factory()->create([
            'user_type' => UserType::ADMIN,
            'plan_id' => null,
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('admin/dashboard/IndexAdmin'));
    }

    public function test_admin_visiting_user_dashboard_is_not_redirected_to_select_plan(): void
    {
        $admin = User::factory()->create([
            'user_type' => UserType::ADMIN,
            'plan_id' => null,
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertStatus(200);
    }

    public function test_admin_visiting_subscription_page_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'user_type' => UserType::ADMIN,
            'plan_id' => null,
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('subscription.cancel'));
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_payouts_page_displays_ppv_recent_purchases(): void
    {
        $admin = User::factory()->create([
            'user_type' => UserType::ADMIN,
            'plan_id' => null,
        ]);
        $admin->assignRole('admin');

        $buyer = User::factory()->create([
            'name' => 'Joseph Dye',
            'email' => 'gojo.pixar@gmail.com',
            'user_type' => UserType::CREATOR,
        ]);

        $creator = User::factory()->create([
            'user_type' => UserType::CREATOR,
        ]);

        $movie = Movie::create([
            'title' => 'Test PPV Movie',
            'description' => 'Test Description',
            'movie_video' => 'movies/test.mp4',
            'horizontal_image' => 'images/h.jpg',
            'vertical_image' => 'images/v.jpg',
            'user_id' => $creator->id,
        ]);

        $content = $movie->content()->create([
            'type' => 'movies',
            'user_id' => $creator->id,
            'ppv_price' => 5.00,
            'status' => 'published',
        ]);

        $purchase = Purchase::create([
            'user_id' => $buyer->id,
            'content_id' => $content->id,
            'amount' => 5.00,
            'creator_share' => 3.00,
            'platform_share' => 2.00,
            'stripe_payment_intent_id' => 'pi_test_joseph_123',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.payouts.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('admin/payouts/IndexPayouts')
                ->has('recentPurchases')
                ->where('recentPurchases.0.user_email', 'gojo.pixar@gmail.com')
                ->where('recentPurchases.0.amount', 5)
                ->where('recentPurchases.0.stripe_payment_intent_id', 'pi_test_joseph_123')
        );
    }
}
