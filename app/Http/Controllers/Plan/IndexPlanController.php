<?php

namespace App\Http\Controllers\Plan;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class IndexPlanController extends Controller
{
    public function __invoke()
    {
        if (!$this->canAccess()) {
            throw new \Exception(__("You are not authorized to access this resource"));
        }

        $plans = Plan::orderBy('id', 'desc')->where('status', 'active')->get();
        
        return Inertia::render('admin/plan/IndexPlan', [
            'plans' => $plans,
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
