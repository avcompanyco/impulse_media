<?php

namespace App\Traits\User;

use App\Models\User;
use App\Models\Plan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

trait HasUpdateUser
{
    public function update(User $user, array $data)
    {
        if (User::where('email', $data['email'])->where('id', '!=', $user->id)->exists()) {
            throw new \Exception(__("User with this email already exists"));
        }

        if (isset($data['username']) && User::where('username', $data['username'])->where('id', '!=', $user->id)->exists()) {
            throw new \Exception(__("User with this username already exists"));
        }

        return DB::transaction(function () use ($user, $data) {
            $oldPlanId = $user->plan_id;
            $newPlanId = $data['plan_id'] ?? null;
            $trialDays = $data['trial_days'] ?? null;

            // Remove non-user fields for update
            unset($data['trial_days']);

            // Hash the password if provided
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                // Remove password from data if not provided
                unset($data['password']);
            }

            // Handle image upload if present
            if (isset($data['image']) && $data['image']) {
                // Delete old image if exists
                if ($user->image && Storage::disk('public')->exists($user->image)) {
                    Storage::disk('public')->delete($user->image);
                }
                
                $imagePath = $data['image']->store('users', 'public');
                $data['image'] = $imagePath;
            }

            // Update user basic data
            $user->update($data);

            // Handle plan changes
            if ($oldPlanId !== $newPlanId) {
                $this->updateUserPlan($user, $oldPlanId, $newPlanId, $trialDays);
            }

            // Sync roles if provided
            if (isset($data['roles'])) {
                if (is_array($data['roles']) && !empty($data['roles'])) {
                    $user->syncRoles($data['roles']);
                } else {
                    $user->syncRoles([]);
                }
            }

            return $user;
        });
    }

    private function updateUserPlan(User $user, ?int $oldPlanId, ?int $newPlanId, ?int $trialDays = null)
    {
        try {
            if (env('APP_ENV') == 'production') {
                // If removing plan (setting to null)
                if ($newPlanId === null) {
                    // Cancel current subscription if exists
                    if ($user->subscribed('default')) {
                        $user->subscription('default')->cancelNow();
                    }
                    $user->update(['plan_id' => null]);
                    return;
                }
    
                // Get the new plan
                $newPlan = Plan::find($newPlanId);
                if (!$newPlan || !$newPlan->stripe_price_id) {
                    throw new \Exception(__("Invalid plan selected"));
                }
    
                // Ensure user has Stripe customer ID
                if (!$user->hasStripeId()) {
                    $user->createAsStripeCustomer();
                }
    
                // Handle subscription change
                if ($user->subscribed('default')) {
                    // Update existing subscription
                    $subscription = $user->subscription('default');
                    $subscription->swap($newPlan->stripe_price_id);
                } else {
                    // Create new subscription
                    $trialDays = $trialDays ?? $newPlan->free_days_trial ?? 0;
                    
                    $subscriptionBuilder = $user->newSubscription('default', $newPlan->stripe_price_id);
                    
                    if ($trialDays > 0) {
                        $subscriptionBuilder->trialDays($trialDays);
                    }
    
                    $subscriptionBuilder->create();
                }
            }

            $user->update(['plan_id' => $newPlanId]);

        } catch (\Exception $e) {
            logger()->error('Failed to update user plan', [
                'user_id' => $user->id,
                'old_plan_id' => $oldPlanId,
                'new_plan_id' => $newPlanId,
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception(__("Failed to update user plan: ") . $e->getMessage());
        }
    }
}
