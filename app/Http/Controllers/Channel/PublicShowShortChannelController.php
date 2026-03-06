<?php

namespace App\Http\Controllers\Channel;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Enums\Content\ContentStatus;
use App\Models\User;
use App\Models\Content;
use App\Enums\Content\ContentType;

class PublicShowShortChannelController extends Controller
{
    public function __invoke(User $user)
    {
        // If the authenticated user is viewing their own channel, redirect to the private version
        if (Auth::check() && Auth::user()->id == $user->id) {
            return redirect()->route('channel.short');
        }

        $page = request()->page ?? 1;
        $perPage = request()->per_page ?? 20;
        $skip = ($page - 1) * $perPage;

        $shorts = Content::where('user_id', $user->id)
            ->where('type', ContentType::SHORT->value)
            ->where('status', ContentStatus::PUBLISHED->value)
            ->with(['contentable'])
            ->inRandomOrder()
            ->skip($skip)
            ->take($perPage)
            ->get();

        // hidden stripe
        $user->makeHiddenStripe();

        return Inertia::render('public-channel/PublicShowShorts', [
            'shorts' => $shorts,
            'page' => $page,
            'perPage' => $perPage,
            'user' => $user,
        ]);
    }
}
