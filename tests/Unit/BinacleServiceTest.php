<?php

namespace Tests\Unit;

use App\Enums\Binacle\BinacleActionEnum;
use App\Models\Binacle;
use App\Models\User;
use App\Services\BinacleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BinacleServiceTest extends TestCase
{
    use RefreshDatabase;

    private BinacleService $binacleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->binacleService = new BinacleService;
    }

    public function test_can_log_user_registration_event(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $binacle = $this->binacleService->logUserRegistration($user);

        $this->assertInstanceOf(Binacle::class, $binacle);
        $this->assertEquals($user->id, $binacle->user_id);
        $this->assertEquals(BinacleActionEnum::USER_REGISTERED, $binacle->action);
        $this->assertEquals("New user registered: {$user->name} ({$user->email})", $binacle->details);
    }

    public function test_can_log_new_subscription_event(): void
    {
        $user = User::factory()->create();
        $planName = 'Premium Plan';

        $binacle = $this->binacleService->logNewSubscription($user, $planName);

        $this->assertInstanceOf(Binacle::class, $binacle);
        $this->assertEquals($user->id, $binacle->user_id);
        $this->assertEquals(BinacleActionEnum::USER_SUBSCRIPTION_NEW, $binacle->action);
        $this->assertEquals("User purchased plan: {$planName}", $binacle->details);
    }

    public function test_can_log_subscription_cancellation_event(): void
    {
        $user = User::factory()->create();
        $planName = 'Premium Plan';

        $binacle = $this->binacleService->logSubscriptionCancellation($user, $planName);

        $this->assertInstanceOf(Binacle::class, $binacle);
        $this->assertEquals($user->id, $binacle->user_id);
        $this->assertEquals(BinacleActionEnum::USER_SUBSCRIPTION_CANCELLED, $binacle->action);
        $this->assertEquals("User cancelled plan: {$planName}", $binacle->details);
    }

    public function test_can_log_movie_upload_event(): void
    {
        $user = User::factory()->create();
        $movieTitle = 'Awesome Movie';

        $binacle = $this->binacleService->logMovieUpload($user, $movieTitle);

        $this->assertInstanceOf(Binacle::class, $binacle);
        $this->assertEquals($user->id, $binacle->user_id);
        $this->assertEquals(BinacleActionEnum::CONTENT_MOVIE_UPLOADED, $binacle->action);
        $this->assertEquals("Movie uploaded: {$movieTitle}", $binacle->details);
    }

    public function test_can_log_serie_upload_event(): void
    {
        $user = User::factory()->create();
        $serieTitle = 'Amazing Series';

        $binacle = $this->binacleService->logSerieUpload($user, $serieTitle);

        $this->assertInstanceOf(Binacle::class, $binacle);
        $this->assertEquals($user->id, $binacle->user_id);
        $this->assertEquals(BinacleActionEnum::CONTENT_SERIE_UPLOADED, $binacle->action);
        $this->assertEquals("Serie uploaded: {$serieTitle}", $binacle->details);
    }

    public function test_can_log_short_upload_event(): void
    {
        $user = User::factory()->create();
        $shortTitle = 'Cool Short Video';

        $binacle = $this->binacleService->logShortUpload($user, $shortTitle);

        $this->assertInstanceOf(Binacle::class, $binacle);
        $this->assertEquals($user->id, $binacle->user_id);
        $this->assertEquals(BinacleActionEnum::CONTENT_SHORT_UPLOADED, $binacle->action);
        $this->assertEquals("Short uploaded: {$shortTitle}", $binacle->details);
    }

    public function test_user_can_have_multiple_binacles(): void
    {
        $user = User::factory()->create();

        $this->binacleService->logUserRegistration($user);
        $this->binacleService->logNewSubscription($user, 'Premium Plan');
        $this->binacleService->logMovieUpload($user, 'Movie 1');

        $this->assertEquals(3, $user->binacles()->count());
    }
}
