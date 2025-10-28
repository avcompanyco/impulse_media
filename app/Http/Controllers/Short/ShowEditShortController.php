<?php

namespace App\Http\Controllers\Short;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\User;
use App\Models\Content;
use App\Models\Short;
use App\Models\Category;
use App\Enums\Content\ContentType;
use App\Enums\Content\ContentStatus;

class ShowEditShortController extends Controller
{
    public function __invoke(Short $short)
    {
        $_user = User::find(Auth::user()->id);

        if ($short->user_id != $_user->id) {
            return inertiaErrorHandler(
                __("Error"),
                __("Short not found")
            );
        }

        $short->load('content');
        
        if (!$short->content || $short->content->status == ContentStatus::DRAFT) {
            return redirect()->route('user.upload.short');
        }

        return Inertia::render('user/short/ShowEditShort', [
            'short' => $short,
        ]);
    }
}
