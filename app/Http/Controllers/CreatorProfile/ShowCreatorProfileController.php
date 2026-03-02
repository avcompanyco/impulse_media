<?php

namespace App\Http\Controllers\CreatorProfile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Content;
use App\Enums\Content\ContentType;
use App\Enums\Content\ContentStatus;
use Inertia\Inertia;

class ShowCreatorProfileController extends Controller
{
    public function __invoke(User $user)
    {
        // Hide stripe-related fields
        $user->makeHiddenStripe();

        $movies = Content::where('user_id', $user->id)
            ->where('type', ContentType::MOVIE->value)
            ->where('status', ContentStatus::PUBLISHED->value)
            ->with(['contentable'])
            ->latest()
            ->get();

        $series = Content::where('user_id', $user->id)
            ->where('type', ContentType::SERIE->value)
            ->where('status', ContentStatus::PUBLISHED->value)
            ->with(['contentable'])
            ->latest()
            ->get();

        $shorts = Content::where('user_id', $user->id)
            ->where('type', ContentType::SHORT->value)
            ->where('status', ContentStatus::PUBLISHED->value)
            ->with(['contentable'])
            ->latest()
            ->get();

        return Inertia::render('user/creator/ShowCreatorProfile', [
            'creator' => $user,
            'movies' => $movies,
            'series' => $series,
            'shorts' => $shorts,
        ]);
    }
}
