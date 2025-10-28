<?php

namespace App\Traits\User;

use App\Models\User;
use App\Models\Plan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

trait HasCreateUser
{
    public function create(array $data)
    {
        if (User::where('email', $data['email'])->exists()) {
            throw new \Exception(__("User with this email already exists"));
        }

        if (User::where('username', $data['username'])->exists()) {
            throw new \Exception(__("User with this username already exists"));
        }

        return DB::transaction(function () use ($data) {
            // Extract non-user data
            $roles = $data['roles'] ?? [];
            $trialDays = $data['trial_days'] ?? null;
            $planId = $data['plan_id'] ?? null;

            // Remove non-user fields
            unset($data['roles'], $data['trial_days']);

            // Hash the password
            $data['password'] = Hash::make($data['password']);

            // Handle image upload if present
            $image = null;
            if (isset($data['image']) && $data['image']) {
                $image = $data['image'];
                unset($data['image']);
            }
            $data['image'] = "";

            // Create the user
            $user = User::create($data);

            // Handle image upload
            if ($image) {
                $user->updateImage($image);
            }

            // Assign roles if provided
            if (is_array($roles) && !empty($roles)) {
                $user->assignRole($roles);
            }

            // Handle plan assignment and subscription creation
            if ($planId) {
                $plan = Plan::find($planId);
                if ($plan && $plan->stripe_price_id) {
                    $this->createSubscriptionForUser($user, $plan, $trialDays);
                }
            }

            return $user;
        });
    }

    private function createSubscriptionForUser(User $user, Plan $plan, ?int $trialDays = null)
    {
        try {
            // Ensure the user has a Stripe customer ID
            if (!$user->hasStripeId()) {
                $user->createAsStripeCustomer();
            }

            // Use plan's trial days if not specified
            $trialDays = $trialDays ?? $plan->free_days_trial ?? 0;

            // Create subscription through Laravel Cashier
            $subscriptionBuilder = $user->newSubscription('default', $plan->stripe_price_id);
            
            if ($trialDays > 0) {
                $subscriptionBuilder->trialDays($trialDays);
            }

            // Create the subscription
            $subscription = $subscriptionBuilder->create();

            // Update user's plan_id
            $user->update(['plan_id' => $plan->id]);

        } catch (\Exception $e) {
            // If subscription creation fails, we still keep the user but without a plan
            logger()->error('Failed to create subscription for user', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception(__("User created successfully but failed to assign plan: ") . $e->getMessage());
        }
    }
}
