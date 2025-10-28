<?php

namespace App\Http\Controllers\Channel;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Enums\Content\ContentStatus;
use App\Models\User;
use App\Models\Content;
use App\Enums\Content\ContentType;

class ShowSerieChannelController extends Controller
{
    public function __invoke(User $user)
    {
        $_user = Auth::user();
        if ($_user->id == $user->id) {
            return redirect()->route('channel.serie');
        }

        $page = request()->page ?? 1;
        $perPage = request()->per_page ?? 20;
        $skip = ($page - 1) * $perPage;

        $series = Content::where('user_id', $user->id)
            ->where('type', ContentType::SERIE->value)
            ->where('status', ContentStatus::PUBLISHED->value)
            ->with(['contentable'])
            ->inRandomOrder()
            ->skip($skip)
            ->take($perPage)
            ->get();

        // hidden stripe
        $user->makeHiddenStripe();

        return Inertia::render('user/channel/ShowSeries', [
            'series' => $series,
            'page' => $page,
            'perPage' => $perPage,
            'user' => $user,
        ]);
    }
}
