<?php

// Example usage of the Binacles Event Logging System
// This demonstrates how events are logged throughout the application

use App\Services\BinacleService;
use App\Models\User;
use App\Enums\Binacle\BinacleActionEnum;

// The BinacleService is automatically injected into controllers via Laravel's service container

// 1. User Registration Event (automatically logged in RegisterUserController)
// When a user registers, the following event is logged:
/*
$binacleService->logUserRegistration($user);
// Creates: "New user registered: John Doe (john@example.com)"
*/

// 2. Subscription Events (logged in subscription controllers and webhooks)
/*
// New subscription
$binacleService->logNewSubscription($user, 'Premium Plan');
// Creates: "User purchased plan: Premium Plan"

// Subscription cancellation
$binacleService->logSubscriptionCancellation($user, 'Premium Plan');
// Creates: "User cancelled plan: Premium Plan"
*/

// 3. Content Upload Events (logged in publishing traits)
/*
// Movie upload (HasPublishMovie trait)
$binacleService->logMovieUpload($user, 'The Amazing Movie');
// Creates: "Movie uploaded: The Amazing Movie"

// Serie upload (HasPublishSerie trait)  
$binacleService->logSerieUpload($user, 'Cool TV Series');
// Creates: "Serie uploaded: Cool TV Series"

// Short upload (HasPublishShort trait)
$binacleService->logShortUpload($user, 'Funny Short Video');
// Creates: "Short uploaded: Funny Short Video"
*/

// 4. Custom event logging (if needed)
/*
$binacleService->logEvent($user, BinacleActionEnum::CUSTOM_ACTION, 'Custom event details');
*/

// All events are stored in the 'binacles' table with:
// - user_id: References the user who performed the action
// - action: The type of action (from BinacleActionEnum)
// - details: Human-readable description of what happened
// - created_at/updated_at: Timestamps

// To query events for a user:
/*
$userEvents = $user->binacles()->orderBy('created_at', 'desc')->get();
foreach ($userEvents as $event) {
    echo "{$event->created_at}: {$event->action->value} - {$event->details}\n";
}
*/