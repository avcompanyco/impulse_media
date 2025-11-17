<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Content;
use App\Enums\Content\ContentType;

class ViewContentController extends Controller
{
    public function __invoke(Content $content)
    {
        if (!$this->canAccess()) {
            throw new \Exception(__("You are not authorized to access this page"));
        }

        $content = $content->load(['contentable', 'user']);

        if ($content->type === ContentType::SERIE) {
            // load seasons and chapters
            $content->load(['contentable.seasons', 'contentable.seasons.chapters']);
        }
        return Inertia::render('admin/content/ViewContent', [
            'content' => $content,
        ]);
    }

    public function canAccess()
    {
        $_user = User::find(Auth::user()->id);
        if ($_user && $_user->hasRole('admin')) {
            return true;
        }
        return false;
    }
}
