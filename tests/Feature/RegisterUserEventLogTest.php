<?php

namespace Tests\Feature;

use App\Enums\Binacle\BinacleActionEnum;
use App\Models\Binacle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegisterUserEventLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create user roles required for registration
        Role::create(['name' => 'user', 'guard_name' => 'web']);
        Role::create(['name' => 'spectator', 'guard_name' => 'web']);
        Role::create(['name' => 'creator', 'guard_name' => 'web']);
    }

    public function test_user_registration_creates_binacle_event(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'username' => 'johndoe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'spectator',
            'accept_terms' => true,
        ];

        $response = $this->post(route('register.user.store'), $userData);

        // Check that user was created
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'username' => 'johndoe',
        ]);

        $user = User::where('email', 'john@example.com')->first();

        // Check that binacle event was logged
        $this->assertDatabaseHas('binacles', [
            'user_id' => $user->id,
            'action' => BinacleActionEnum::USER_REGISTERED->value,
            'details' => "New user registered: {$user->name} ({$user->email})",
        ]);

        // Verify the binacle count
        $this->assertEquals(1, Binacle::where('user_id', $user->id)->count());
    }

    public function test_failed_registration_does_not_create_binacle_event(): void
    {
        // Try to register with invalid data (missing email)
        $userData = [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'spectator',
            'accept_terms' => true,
        ];

        $response = $this->post(route('register.user.store'), $userData);

        // Check that no binacle events were created
        $this->assertEquals(0, Binacle::count());
    }
}
