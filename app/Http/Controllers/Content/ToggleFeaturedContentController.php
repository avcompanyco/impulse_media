<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ToggleFeaturedContentController extends Controller
{
    public function __invoke(Request $request, Content $content)
    {
        $_user = User::find(Auth::id());
        if (!$_user || !$_user->hasRole('admin')) {
            throw new \Exception(__("You are not authorized to perform this action"));
        }

        $content->update([
            'is_featured' => !$content->is_featured,
        ]);

        return back()->with('success', __('Content featured status updated successfully'));
    }
}
