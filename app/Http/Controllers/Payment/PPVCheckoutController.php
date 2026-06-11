<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Content;
use App\Models\Setting;
use Inertia\Inertia;

class PPVCheckoutController extends Controller
{
    public function __invoke(Request $request, Content $content)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                throw new \Exception(__("You must be logged in."));
            }

            // Check if already purchased
            if ($content->isPurchasedBy($user)) {
                throw new \Exception(__("You have already purchased this content."));
            }

            // Determine if spectator has access for free via membership
            $isMember = $user->isImpulseMember();
            if ($content->allow_membership && $isMember) {
                throw new \Exception(__("You have access to this content through your membership."));
            }

            $rawPpvPrice = (float)($content->ppv_price ?? 0);
            if ($rawPpvPrice <= 0) {
                throw new \Exception(__("This content is free."));
            }

            $discountRate = (float)Setting::get('membership_discount_rate', 10);
            $ppvPrice = $rawPpvPrice;
            if ($isMember && $discountRate > 0) {
                $ppvPrice = max(0.00, round($rawPpvPrice * (1 - ($discountRate / 100)), 2));
            }

            $amountInCents = (int)round($ppvPrice * 100);
            if ($amountInCents <= 0) {
                throw new \Exception(__("Calculated price is invalid."));
            }

            // Create dynamic Checkout Charge session using Cashier
            try {
                $checkout = $user->checkoutCharge(
                    $amountInCents,
                    "PPV Access: " . ($content->contentable->title ?? 'Premium Content'),
                    1,
                    [
                        'success_url' => route('user.ppv.success') . '?session_id={CHECKOUT_SESSION_ID}&content_id=' . $content->id,
                        'cancel_url' => url()->previous() ?: route('dashboard'),
                    ]
                );
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                if (str_contains($e->getMessage(), 'No such customer')) {
                    $user->stripe_id = null;
                    $user->save();

                    $user->createAsStripeCustomer();

                    $checkout = $user->checkoutCharge(
                        $amountInCents,
                        "PPV Access: " . ($content->contentable->title ?? 'Premium Content'),
                        1,
                        [
                            'success_url' => route('user.ppv.success') . '?session_id={CHECKOUT_SESSION_ID}&content_id=' . $content->id,
                            'cancel_url' => url()->previous() ?: route('dashboard'),
                        ]
                    );
                } else {
                    throw $e;
                }
            }

            // Inertia external redirect
            return Inertia::location($checkout->url);
        } catch (\Throwable $th) {
            return back()->withErrors(['message' => $th->getMessage()]);
        }
    }
}
