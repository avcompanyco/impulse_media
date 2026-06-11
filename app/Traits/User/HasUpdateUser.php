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

            // Sync roles based on user_type if it is being updated
            if (isset($data['user_type'])) {
                $userType = $data['user_type'];
                $rolesToAssign = [];
                if ($userType === 'spectator') {
                    $rolesToAssign = ['spectator'];
                } elseif ($userType === 'creator') {
                    $rolesToAssign = ['user', 'creator'];
                } elseif ($userType === 'admin') {
                    $rolesToAssign = ['admin'];
                }
                $user->syncRoles($rolesToAssign);
            }

            // Handle plan changes
            if ($oldPlanId !== $newPlanId) {
                $this->updateUserPlan($user, $oldPlanId, $newPlanId, $trialDays);
            }

            return $user;
        });
    }

    private function updateUserPlan(User $user, ?int $oldPlanId, ?int $newPlanId, ?int $trialDays = null)
    {
        try {
            if (env('APP_ENV') !== 'production') {
                $user->update(['plan_id' => $newPlanId]);
                return;
            }

            // Eliminacion del plan al usuario
            if ($newPlanId === null) {
                $this->cancelUserSubscription($user);
                $user->update(['plan_id' => null]);
                return;
            }

            $newPlan = Plan::find($newPlanId);
            if (!$newPlan || !$newPlan->stripe_price_id) {
                throw new \Exception(__("Invalid plan selected"));
            }

            $this->ensureStripeCustomerExists($user);

            if ($user->subscribed('default')) {
                $this->handleExistingSubscription($user, $newPlan);
            } else {
                $this->createNewSubscription($user, $newPlan, $trialDays);
            }

            // Actualizar el plan_id del usuario
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

    /**
     * Cancelar suscripción existente del usuario
     */
    private function cancelUserSubscription(User $user): void
    {
        if ($user->subscribed('default')) {
            $subscription = $user->subscription('default');

            if ($subscription->onTrial() || $subscription->active()) {
                $subscription->cancel();
            }
        }
    }

    /**
     * Asegurar que el usuario existe como cliente en Stripe
     */
    private function ensureStripeCustomerExists(User $user): void
    {
        if (!$user->hasStripeId()) {
            $stripeCustomerData = [
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => [
                    'user_id' => $user->id,
                ],
                'address' => [
                    'line1' => $user->address ?? '',
                    'city' => $user->city ?? '',
                    'state' => $user->state ?? '',
                    'postal_code' => $user->postal_code ?? '',
                    'country' => $user->country ?? 'US',
                ]
            ];

            if ($user->phone) {
                $stripeCustomerData['phone'] = $user->phone;
            }

            $user->createAsStripeCustomer($stripeCustomerData);
        }
    }

    /**
     * Manejar suscripción existente (cambio de plan)
     */
    private function handleExistingSubscription(User $user, Plan $newPlan, ?int $trialDays = null): void
    {
        $subscription = $user->subscription('default');

        if ($subscription->onTrial()) {
            $subscription->extendTrial(now()->addDays($trialDays)->endOfDay());
        }

        $subscription->swap($newPlan->stripe_price_id);
    }

    /**
     * Crear nueva suscripción
     */
    private function createNewSubscription(User $user, Plan $newPlan, ?int $trialDays = null): void
    {
        $trialDays = $trialDays ?? $newPlan->free_days_trial ?? 0;

        $subscriptionBuilder = $user->newSubscription('default', $newPlan->stripe_price_id);

        if ($trialDays > 0) {
            $trialEndDate = now()->addDays($trialDays)->endOfDay();
            $subscriptionBuilder->trialUntil($trialEndDate);
        }

        $subscription = $subscriptionBuilder->create();

        // Log para debugging
        logger()->info('New subscription created', [
            'user_id' => $user->id,
            'plan_id' => $newPlan->id,
            'stripe_price_id' => $newPlan->stripe_price_id,
            'trial_ends_at' => $subscription->trial_ends_at,
        ]);
    }

}
