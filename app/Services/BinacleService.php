<?php

namespace App\Services;

use App\Enums\Binacle\BinacleActionEnum;
use App\Models\Binacle;
use App\Models\User;

class BinacleService
{
    /**
     * Log an event in the binacles table
     */
    public function logEvent(User $user, BinacleActionEnum $action, string $details): Binacle
    {
        return Binacle::create([
            'user_id' => $user->id,
            'action' => $action,
            'details' => $details,
        ]);
    }

    /**
     * Log user registration event
     */
    public function logUserRegistration(User $user): Binacle
    {
        return $this->logEvent(
            $user,
            BinacleActionEnum::USER_REGISTERED,
            "New user registered: {$user->name} ({$user->email})"
        );
    }

    /**
     * Log new subscription event
     */
    public function logNewSubscription(User $user, string $planName): Binacle
    {
        return $this->logEvent(
            $user,
            BinacleActionEnum::USER_SUBSCRIPTION_NEW,
            "User purchased plan: {$planName}"
        );
    }

    /**
     * Log subscription cancellation event
     */
    public function logSubscriptionCancellation(User $user, string $planName): Binacle
    {
        return $this->logEvent(
            $user,
            BinacleActionEnum::USER_SUBSCRIPTION_CANCELLED,
            "User cancelled plan: {$planName}"
        );
    }

    /**
     * Log movie upload event
     */
    public function logMovieUpload(User $user, string $movieTitle): Binacle
    {
        return $this->logEvent(
            $user,
            BinacleActionEnum::CONTENT_MOVIE_UPLOADED,
            "Movie uploaded: {$movieTitle}"
        );
    }

    /**
     * Log serie upload event
     */
    public function logSerieUpload(User $user, string $serieTitle): Binacle
    {
        return $this->logEvent(
            $user,
            BinacleActionEnum::CONTENT_SERIE_UPLOADED,
            "Serie uploaded: {$serieTitle}"
        );
    }

    /**
     * Log short upload event
     */
    public function logShortUpload(User $user, string $shortTitle): Binacle
    {
        return $this->logEvent(
            $user,
            BinacleActionEnum::CONTENT_SHORT_UPLOADED,
            "Short uploaded: {$shortTitle}"
        );
    }
}
