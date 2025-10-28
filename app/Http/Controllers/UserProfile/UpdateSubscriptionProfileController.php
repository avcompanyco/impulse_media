<?php

namespace App\Http\Controllers\UserProfile;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use App\Services\BinacleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UpdateSubscriptionProfileController extends Controller
{
    public function __construct(private BinacleService $binacleService) {}

    public function __invoke(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = User::find(Auth::user()->id);
        $newPlan = Plan::findOrFail($request->plan_id);

        // Check if user has an active subscription
        if (! $user->subscribed('default')) {
            return redirect()->route('subscription.checkout', ['plan' => $newPlan])
                ->with('flash', [
                    'type' => 'warning',
                    'title' => __('No Subscription'),
                    'message' => __('You need to subscribe first.'),
                ]);
        }

        // Get current subscription
        $subscription = $user->subscription('default');

        // Check if it's the same plan
        if ($user->plan_id == $newPlan->id) {
            return redirect()->back()
                ->with('flash', [
                    'type' => 'warning',
                    'title' => __('Warning'),
                    'message' => __('You are already subscribed to this plan.'),
                ]);
        }

        try {
            // Change the subscription plan using Cashier
            $subscription->swap($newPlan->stripe_price_id);

            // Update user's plan_id
            $oldPlanId = $user->plan_id;
            $user->plan_id = $newPlan->id;
            $user->save();

            // Log subscription change event
            $this->binacleService->logNewSubscription($user, $newPlan->name);

            Log::info('Subscription plan updated', [
                'user_id' => $user->id,
                'old_plan_id' => $oldPlanId,
                'new_plan_id' => $newPlan->id,
                'stripe_price_id' => $newPlan->stripe_price_id,
            ]);

            return redirect()->back()
                ->with('flash', [
                    'type' => 'success',
                    'title' => __('Success'),
                    'message' => __('Your subscription plan has been updated successfully.'),
                ]);

        } catch (\Exception $e) {
            Log::error('Error updating subscription plan', [
                'user_id' => $user->id,
                'plan_id' => $newPlan->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('flash', [
                    'type' => 'error',
                    'title' => __('Error'),
                    'message' => __('There was an error updating your subscription. Please try again.'),
                ]);
        }
    }
}
