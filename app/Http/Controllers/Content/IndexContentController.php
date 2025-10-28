<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class IndexContentController extends Controller
{
    public function __invoke()
    {
        if (!$this->canAccess()) {
            throw new \Exception(__("You are not authorized to access this page"));
        }

        $categories = Category::with('subcategories')->orderBy('id', 'desc')->get();

        return Inertia::render('admin/content/IndexContent', [
            'categories' => $categories,
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
