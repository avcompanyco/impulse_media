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

            if (empty($roles)) {
                $roles = ['user'];
            }

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
                } else {
                    $plan = Plan::where('price', 0)->first();
                    if ($plan && $plan->stripe_price_id) {
                        $this->createSubscriptionForUser($user, $plan, $trialDays);
                    }
                }
            }

            return $user;
        });
    }

    private function createSubscriptionForUser(User $user, Plan $plan, ?int $trialDays = null)
    {
        try {
            if (env('APP_ENV') == 'production') {
                if (!$user->hasStripeId()) {
                     // Create Stripe customer with address information
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

                $trialDays = $trialDays ?? $plan->free_days_trial ?? 0;

                // Verificar si el plan es gratuito (price = 0)
                if ($plan->price == 0) {
                    // Para planes gratuitos, no necesitas método de pago
                    $subscription = $user->newSubscription('default', $plan->stripe_price_id)
                        ->trialDays($trialDays)
                        ->create();
                } else {
                    // Para planes de pago, necesitas manejar el método de pago
                    // Por ahora, asignar el plan gratuito como fallback
                    $freePlan = Plan::where('price', 0)->first();
                    if ($freePlan) {
                        $subscription = $user->newSubscription('default', $freePlan->stripe_price_id)
                            ->trialDays($trialDays)
                            ->create();
                        $plan = $freePlan; // Actualizar el plan al gratuito
                    } else {
                        throw new \Exception("No free plan available for fallback");
                    }
                }
            }

            $user->update(['plan_id' => $plan->id]);
        } catch (\Exception $e) {
            logger()->error('Failed to create subscription for user', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage()
            ]);

            throw new \Exception(__("User created successfully but failed to assign plan: ") . $e->getMessage());
        }
    }
}
