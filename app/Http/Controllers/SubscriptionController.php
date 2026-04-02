<?php

namespace App\Http\Controllers;

use App\Enums\Payment\PaymentStatus;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use App\Services\BinacleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Stripe\StripeClient;
use Stripe\Webhook;

class SubscriptionController extends Controller
{
    public function checkout(Request $request, Plan $plan)
    {
        $user = Auth::user();

        // Asegurar que el usuario tenga un customer ID en Stripe
        if (! $user->hasStripeId()) {
            $user->createAsStripeCustomer();
        }

        return $request->user()
            ->newSubscription('default', $plan->stripe_price_id)
            ->trialUntil(now()->addDays($plan->free_days_trial)->endOfDay())
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('subscription.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('subscription.cancel'),
            ]);
    }

    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');

        if (! $sessionId) {
            return redirect()->route('subscription.cancel')
                ->with('error', 'No se pudo verificar la suscripción.');
        }

        $stripe = new StripeClient(config('cashier.secret'));

        try {
            $session = $stripe->checkout->sessions->retrieve($sessionId, [
                'expand' => ['subscription'],
            ]);
            
            // Verificar que el pago fue exitoso
            if ($session->payment_status === 'paid') {
                
                $user = User::where('stripe_id', $session->customer)->first();
                $plan = $user->getCurrentPlan();
                if (!$plan) {
                    // se verifica a que plan esta suscrito
                    $subscription_plan_product = $session->subscription->plan->product;
                    $_plan = Plan::where('stripe_product_id', $subscription_plan_product)->first();
                    if ($_plan) {
                        $user->plan_id = $_plan->id;
                        $user->save();
                        $plan = $_plan;
                    }
                }

                return Inertia::render('subscription/Success', [
                    'session' => $session,
                    'message' => '¡Suscripción activada con éxito!',
                    'plan' => $plan,
                ]);
            }

            return inertiaErrorHandler(
                __('Error'),
                __('El pago no se completó correctamente.')
            );
        } catch (\Exception $e) {
            return inertiaErrorHandler(
                __('Error'),
                __('Error al verificar la suscripción: ').$e->getMessage()
            );
        }
    }

    public function cancel(Request $request)
    {
        $user = Auth::user();
        $userType = $user ? ($user->user_type ?? 'creator') : 'creator';
        $plans = Plan::where('status', 'active')
            ->where('plan_type', $userType)
            ->get();

        return Inertia::render('subscription/Cancel', [
            'message' => __('Your subscription process was cancelled. You can try again anytime.'),
            'title' => __('Subscription Cancelled'),
            'plans' => $plans,
        ]);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('cashier.webhook.secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid payload in Stripe webhook', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid signature in Stripe webhook', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Manejar los diferentes tipos de eventos de Stripe
        switch ($event['type']) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event['data']['object']);
                break;

            case 'customer.subscription.created':
                $this->handleSubscriptionCreated($event['data']['object']);
                break;

            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event['data']['object']);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event['data']['object']);
                break;

            case 'invoice.payment_succeeded':
                $this->handlePaymentSucceeded($event['data']['object']);
                break;

            case 'invoice.payment_failed':
                $this->handlePaymentFailed($event['data']['object']);
                break;

            case 'customer.subscription.trial_will_end':
                $this->handleTrialWillEnd($event['data']['object']);
                break;

            case 'charge.dispute.created':
            case 'invoice.payment_action_required':
                // Handle disputed charges and payment action required
                break;

            case 'payment_intent.succeeded':
                // Additional handling for one-time payments if needed
                break;

            default:
                Log::info('Unhandled Stripe webhook event', ['type' => $event['type']]);
        }

        return response()->json(['status' => 'success'], 200);
    }

    private function handleCheckoutSessionCompleted($session)
    {
        try {
            $customerId = $session['customer'];
            $subscriptionId = $session['subscription'] ?? null;

            $user = User::where('stripe_id', $customerId)->first();

            if ($user && $subscriptionId) {
                // Sincronizar la suscripción usando Laravel Cashier
                $user->subscriptions()->updateOrCreate(
                    ['stripe_id' => $subscriptionId],
                    [
                        'type' => 'default',
                        'stripe_status' => 'active',
                        'trial_ends_at' => isset($session['subscription']['trial_end'])
                            ? now()->createFromTimestamp($session['subscription']['trial_end'])
                            : null,
                    ]
                );

                Log::info('Checkout session completed', [
                    'user_id' => $user->id,
                    'session_id' => $session['id'],
                    'subscription_id' => $subscriptionId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error handling checkout session completed', ['error' => $e->getMessage()]);
        }
    }

    private function handleSubscriptionCreated($subscription)
    {
        try {
            $customerId = $subscription['customer'];
            $priceId = $subscription['items']['data'][0]['price']['id'];

            $user = User::where('stripe_id', $customerId)->first();
            $plan = Plan::where('stripe_price_id', $priceId)->first();

            if ($user && $plan) {
                // Crear o actualizar la suscripción usando Laravel Cashier
                $user->subscriptions()->updateOrCreate(
                    ['stripe_id' => $subscription['id']],
                    [
                        'type' => 'default',
                        'stripe_status' => $subscription['status'],
                        'stripe_price' => $priceId,
                        'quantity' => $subscription['items']['data'][0]['quantity'] ?? 1,
                        'trial_ends_at' => isset($subscription['trial_end'])
                            ? now()->createFromTimestamp($subscription['trial_end'])
                            : null,
                        'ends_at' => null,
                    ]
                );

                $user->plan_id = $plan->id;
                $user->save();

                // Log new subscription event
                $binacleService = app(BinacleService::class);
                $binacleService->logNewSubscription($user, $plan->name);

                Log::info('Subscription created', [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'subscription_id' => $subscription['id'],
                    'status' => $subscription['status'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error handling subscription created', ['error' => $e->getMessage()]);
        }
    }

    private function handleSubscriptionUpdated($subscription)
    {
        try {
            $customerId = $subscription['customer'];
            $priceId = $subscription['items']['data'][0]['price']['id'];

            $user = User::where('stripe_id', $customerId)->first();
            $plan = Plan::where('stripe_price_id', $priceId)->first();

            if ($user) {
                $userSubscription = $user->subscriptions()->where('stripe_id', $subscription['id'])->first();

                if ($userSubscription) {
                    $userSubscription->update([
                        'stripe_status' => $subscription['status'],
                        'stripe_price' => $priceId,
                        'quantity' => $subscription['items']['data'][0]['quantity'] ?? 1,
                        'trial_ends_at' => isset($subscription['trial_end'])
                            ? now()->createFromTimestamp($subscription['trial_end'])
                            : null,
                        'ends_at' => $subscription['status'] === 'canceled' && isset($subscription['current_period_end'])
                            ? now()->createFromTimestamp($subscription['current_period_end'])
                            : null,
                    ]);

                    if ($plan) {
                        $user->plan_id = $plan->id;
                        $user->save();
                    }
                }

                Log::info('Subscription updated', [
                    'user_id' => $user->id,
                    'plan_id' => $plan?->id,
                    'subscription_id' => $subscription['id'],
                    'status' => $subscription['status'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error handling subscription updated', ['error' => $e->getMessage()]);
        }
    }

    private function handleSubscriptionDeleted($subscription)
    {
        try {
            $customerId = $subscription['customer'];

            $user = User::where('stripe_id', $customerId)->first();

            if ($user) {
                $userSubscription = $user->subscriptions()->where('stripe_id', $subscription['id'])->first();

                if ($userSubscription) {
                    $userSubscription->update([
                        'stripe_status' => 'canceled',
                        'ends_at' => now(),
                    ]);

                    // Log subscription cancellation event
                    $planName = $user->plan ? $user->plan->name : 'Unknown Plan';
                    $binacleService = app(BinacleService::class);
                    $binacleService->logSubscriptionCancellation($user, $planName);

                    $user->plan_id = null;
                    $user->save();
                }

                Log::info('Subscription deleted', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription['id'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error handling subscription deleted', ['error' => $e->getMessage()]);
        }
    }

    private function handlePaymentSucceeded($invoice)
    {
        try {
            $customerId = $invoice['customer'];
            $subscriptionId = $invoice['subscription'] ?? null;

            $user = User::where('stripe_id', $customerId)->first();

            if ($user && $subscriptionId) {
                $userSubscription = $user->subscriptions()->where('stripe_id', $subscriptionId)->first();

                if ($userSubscription && $userSubscription->stripe_status !== 'active') {
                    $userSubscription->update([
                        'stripe_status' => 'active',
                        'ends_at' => null,
                    ]);

                    // Restore plan_id if it was cleared
                    $plan = Plan::where('stripe_price_id', $userSubscription->stripe_price)->first();
                    if ($plan && !$user->plan_id) {
                        $user->plan_id = $plan->id;
                        $user->save();
                    }
                }

                // Record the payment
                $plan = Plan::where('stripe_price_id', $userSubscription?->stripe_price)->first();

                if ($plan) {
                    Payment::create([
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                        'amount' => $invoice['amount_paid'] / 100, // Convert from cents
                        'currency' => strtoupper($invoice['currency']),
                        'status' => PaymentStatus::COMPLETED,
                        'stripe_payment_intent_id' => $invoice['payment_intent'] ?? null,
                        'stripe_subscription_id' => $subscriptionId,
                        'stripe_invoice_id' => $invoice['id'],
                        'stripe_customer_id' => $customerId,
                        'payment_method' => 'stripe',
                        'billing_period' => $plan->billing_period,
                        'paid_at' => now(),
                        'metadata' => [
                            'invoice_number' => $invoice['number'] ?? null,
                            'billing_reason' => $invoice['billing_reason'] ?? null,
                        ],
                    ]);
                }

                Log::info('Payment succeeded', [
                    'user_id' => $user->id,
                    'invoice_id' => $invoice['id'],
                    'subscription_id' => $subscriptionId,
                    'amount' => $invoice['amount_paid'] / 100,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error handling payment succeeded', ['error' => $e->getMessage()]);
        }
    }

    private function handlePaymentFailed($invoice)
    {
        try {
            $customerId = $invoice['customer'];
            $subscriptionId = $invoice['subscription'] ?? null;

            $user = User::where('stripe_id', $customerId)->first();

            if ($user && $subscriptionId) {
                $userSubscription = $user->subscriptions()->where('stripe_id', $subscriptionId)->first();

                if ($userSubscription) {
                    $userSubscription->update([
                        'stripe_status' => 'past_due',
                    ]);
                }

                // Record the failed payment
                $plan = Plan::where('stripe_price_id', $userSubscription?->stripe_price)->first();

                if ($plan) {
                    Payment::create([
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                        'amount' => $invoice['amount_due'] / 100, // Convert from cents
                        'currency' => strtoupper($invoice['currency']),
                        'status' => PaymentStatus::FAILED,
                        'stripe_payment_intent_id' => $invoice['payment_intent'] ?? null,
                        'stripe_subscription_id' => $subscriptionId,
                        'stripe_invoice_id' => $invoice['id'],
                        'stripe_customer_id' => $customerId,
                        'payment_method' => 'stripe',
                        'billing_period' => $plan->billing_period,
                        'failed_at' => now(),
                        'metadata' => [
                            'invoice_number' => $invoice['number'] ?? null,
                            'billing_reason' => $invoice['billing_reason'] ?? null,
                            'attempt_count' => $invoice['attempt_count'] ?? null,
                        ],
                    ]);
                }

                Log::warning('Payment failed', [
                    'user_id' => $user->id,
                    'invoice_id' => $invoice['id'],
                    'subscription_id' => $subscriptionId,
                    'amount' => $invoice['amount_due'] / 100,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error handling payment failed', ['error' => $e->getMessage()]);
        }
    }

    private function handleTrialWillEnd($subscription)
    {
        try {
            $customerId = $subscription['customer'];

            $user = User::where('stripe_id', $customerId)->first();

            if ($user) {
                Log::info('Trial will end', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription['id'],
                    'trial_end' => $subscription['trial_end'],
                ]);

                // Aquí podrías enviar un email de notificación al usuario
                // $user->notify(new TrialEndingSoonNotification());
            }
        } catch (\Exception $e) {
            Log::error('Error handling trial will end', ['error' => $e->getMessage()]);
        }
    }
}
