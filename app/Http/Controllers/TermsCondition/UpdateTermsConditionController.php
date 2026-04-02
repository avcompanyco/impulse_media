<?php

namespace App\Http\Controllers\TermsCondition;

use App\Http\Controllers\Controller;
use App\Models\TermsCondition;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateTermsConditionController extends Controller
{
    public function __invoke(Request $request, TermsCondition $termsCondition)
    {
        if (!$this->canAccess()) {
            throw new \Exception(__("You are not authorized to update this resource"));
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'version' => 'required|string|max:50',
        ]);

        $termsCondition->update($validated);

        return inertiaSuccessHandler(
            __("Success"),
            __("Terms & Conditions updated successfully")
        );
    }

    public function canAccess()
    {
        $_user = User::find(Auth::user()->id);
        return $_user && $_user->hasRole('admin');
    }
}
