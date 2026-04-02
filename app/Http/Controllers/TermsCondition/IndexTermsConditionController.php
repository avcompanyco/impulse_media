<?php

namespace App\Http\Controllers\TermsCondition;

use App\Http\Controllers\Controller;
use App\Models\TermsCondition;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class IndexTermsConditionController extends Controller
{
    public function __invoke()
    {
        if (!$this->canAccess()) {
            throw new \Exception(__("You are not authorized to access this resource"));
        }

        $terms = TermsCondition::orderBy('type')->orderBy('created_at', 'desc')->get();

        return Inertia::render('admin/terms/IndexTerms', [
            'terms' => $terms,
        ]);
    }

    public function canAccess()
    {
        $_user = User::find(Auth::user()->id);
        return $_user && $_user->hasRole('admin');
    }
}
