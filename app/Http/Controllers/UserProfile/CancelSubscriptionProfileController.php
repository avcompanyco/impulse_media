<?php

namespace App\Http\Controllers\UserProfile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BinacleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CancelSubscriptionProfileController extends Controller
{
    public function __construct(private BinacleService $binacleService) {}

    public function __invoke(Request $request)
    {
        $user = User::find(Auth::user()->id);

        // Check if user has an active subscription
        if (! $user->subscribed('default')) {
            return redirect()->back()
                ->with('flash', [
                    'type' => 'warning',
                    'title' => __('Warning'),
                    'message' => __('You do not have an active subscription to cancel.'),
                ]);
        }

        // Get current subscription
        $subscription = $user->subscription('default');

        // Check if subscription is already cancelled
        if ($subscription->canceled()) {
            return redirect()->back()
                ->with('flash', [
                    'type' => 'info',
                    'title' => __('Already Cancelled'),
                    'message' => __('Your subscription is already cancelled and will end on :date.', [
                        'date' => $subscription->ends_at->format('F j, Y'),
                    ]),
                ]);
        }

        try {
            // Cancel the subscription at the end of the billing period
            $subscription->cancel();

            // Log subscription cancellation event
            $planName = $user->plan ? $user->plan->name : 'Unknown Plan';
            $this->binacleService->logSubscriptionCancellation($user, $planName);

            Log::info('Subscription cancelled', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->stripe_id,
                'ends_at' => $subscription->ends_at,
            ]);

            return redirect()->back()
                ->with('flash', [
                    'type' => 'success',
                    'title' => __('Subscription Cancelled'),
                    'message' => __('Your subscription has been cancelled. You will continue to have access until :date.', [
                        'date' => $subscription->ends_at->format('F j, Y'),
                    ]),
                ]);

        } catch (\Exception $e) {
            Log::error('Error cancelling subscription', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('flash', [
                    'type' => 'error',
                    'title' => __('Error'),
                    'message' => __('There was an error cancelling your subscription. Please try again.'),
                ]);
        }
    }
}
