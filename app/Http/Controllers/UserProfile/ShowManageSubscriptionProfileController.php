<?php

namespace App\Http\Controllers\UserProfile;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Plan;

class ShowManageSubscriptionProfileController extends Controller
{
    public function __invoke()
    {
        if (!$this->canAccess()) {
            throw new \Exception(__("You are not authorized to access this resource"));
        }

        $_user = User::find(Auth::user()->id);  // Get the user

        // Filter plans by user type so they only see relevant plans
        $plans = Plan::where('status', 'active')
            ->where('plan_type', $_user->user_type ?? 'creator')
            ->get();
        $my_plan = $_user->getCurrentPlan();
        
        // Get subscription information
        $subscription = null;
        $subscriptionStatus = 'inactive';
        
        if ($_user->subscribed('default')) {
            $subscription = $_user->subscription('default');
            $subscriptionStatus = $subscription->stripe_status;
        }

        return Inertia::render('user/profile/ManageSubscriptionProfile', [
            'user' => $_user,
            'plans' => $plans,
            'my_plan' => $my_plan,
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'stripe_id' => $subscription->stripe_id,
                'stripe_status' => $subscription->stripe_status,
                'ends_at' => $subscription->ends_at,
                'trial_ends_at' => $subscription->trial_ends_at,
                'on_trial' => $subscription->onTrial(),
                'cancelled' => $subscription->canceled(),
                'active' => $subscription->active(),
            ] : null,
            'subscription_status' => $subscriptionStatus,
        ]);
    }

    public function canAccess()
    {
        $_user = User::find(Auth::user()->id);
        if ($_user && $_user->hasAnyRole(['user', 'spectator', 'creator'])) {
            return true;
        }
        return false;
    }
}
