<?php

namespace App\Http\Controllers\Channel;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Enums\Content\ContentStatus;
use App\Models\User;
use App\Models\Content;
use App\Enums\Content\ContentType;

class SerieChannelController extends Controller
{
    public function __invoke()
    {
        $_user = User::find(Auth::user()->id);

        $page = request()->page ?? 1;
        $perPage = request()->per_page ?? 20;
        $skip = ($page - 1) * $perPage;

        $series = Content::where('user_id', $_user->id)
            ->where('type', ContentType::SERIE->value)
            ->where('status', ContentStatus::PUBLISHED->value)
            ->with(['contentable'])
            ->inRandomOrder()
            ->skip($skip)
            ->take($perPage)
            ->get();

        return Inertia::render('user/channel/MySeries', [
            'series' => $series,
            'page' => $page,
            'perPage' => $perPage,
        ]);
    }
}
