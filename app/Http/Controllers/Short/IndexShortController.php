<?php

namespace App\Http\Controllers\Short;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\Short;
use App\Models\Content;
use App\Enums\Content\ContentStatus;

class IndexShortController extends Controller
{
    public function __invoke(Request $request)
    {
        // clear session previous_short_ids
        session()->forget('previous_short_ids');

        return Inertia::render('user/short/ShowShort');
    }
}
