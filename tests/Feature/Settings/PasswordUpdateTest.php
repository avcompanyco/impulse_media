<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
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

    public function test_password_can_be_updated_via_profile_update()
    {
        $user = User::factory()->create(['user_type' => 'spectator']);
        $user->assignRole('spectator');

        $response = $this
            ->actingAs($user)
            ->put(route('user.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_password_confirmation_must_match()
    {
        $user = User::factory()->create(['user_type' => 'spectator']);
        $user->assignRole('spectator');

        $response = $this
            ->actingAs($user)
            ->put(route('user.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'password' => 'new-password',
                'password_confirmation' => 'wrong-confirmation',
            ]);

        $response->assertSessionHasErrors('password');
    }
}

