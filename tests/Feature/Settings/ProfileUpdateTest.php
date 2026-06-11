<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create user roles required for authorization
        Role::create(['name' => 'user', 'guard_name' => 'web']);
        Role::create(['name' => 'spectator', 'guard_name' => 'web']);
        Role::create(['name' => 'creator', 'guard_name' => 'web']);
    }

    public function test_profile_page_is_displayed()
    {
        $user = User::factory()->create(['user_type' => 'spectator']);
        $user->assignRole('spectator');

        $response = $this
            ->actingAs($user)
            ->get(route('user.profile.manage'));

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated()
    {
        $user = User::factory()->create(['user_type' => 'spectator']);
        $user->assignRole('spectator');

        $response = $this
            ->actingAs($user)
            ->put(route('user.profile.update'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'username' => 'testuser',
            ]);

        $response->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertSame('testuser', $user->username);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged()
    {
        $user = User::factory()->create(['user_type' => 'spectator']);
        $user->assignRole('spectator');

        $response = $this
            ->actingAs($user)
            ->put(route('user.profile.update'), [
                'name' => 'Test User',
                'email' => $user->email,
                'username' => $user->username,
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }
}

