<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\User;
use App\Models\Content;
use App\Models\Serie;
use App\Models\Category;
use App\Enums\Content\ContentType;
use App\Enums\Content\ContentStatus;

class EditSerieController extends Controller
{
    public function __invoke()
    {
        $_user = User::find(Auth::user()->id);

        // get content with movie morph,
        $series = Serie::where('user_id', $_user->id)
            ->whereHas('content', function ($query) {
                $query->where('type', ContentType::SERIE->value)
                    ->where('status', ContentStatus::PUBLISHED->value);
            })
            ->with('content')
            ->get();

        return Inertia::render('user/serie/EditSerie', [
            'series' => Inertia::always($series),
        ]);
    }
}
