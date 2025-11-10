<?php

namespace App\Http\Controllers\UserProfile;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ShowProfileController extends Controller
{
    public function __invoke()
    {
        if (!$this->canAccess()) {
            throw new \Exception(__("You are not authorized to access this resource"));
        }

        $_user = User::find(Auth::user()->id);  // Get the user
        $plan = $_user->plan;

        $subscription = $_user->subscription('default');

        $next_payment_date = null;

        if ($subscription) {
            // Get the Stripe Subscription object
            $stripeSubscription = $subscription->asStripeSubscription();

            $nextPaymentTimestamp = $stripeSubscription->current_period_end;

            $nextPaymentDate = Carbon::createFromTimestamp($nextPaymentTimestamp);

            $next_payment_date = $nextPaymentDate->toFormattedDateString();
        }

        return Inertia::render('user/profile/ShowProfile', [
            'user' => $_user,
            'plan' => $plan,
            'next_payment_date' => $next_payment_date
        ]);
    }

    public function canAccess()
    {
        $_user = User::find(Auth::user()->id);
        if ($_user && $_user->hasRole('user')) {
            return true;
        }
        return false;
    }
}
