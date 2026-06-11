<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Content;
use App\Models\Setting;
use App\Models\Purchase;
use App\Models\CreatorEarning;
use Stripe\StripeClient;

class PPVSuccessController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $sessionId = $request->get('session_id');
            $contentId = $request->get('content_id');

            if (!$sessionId || !$contentId) {
                return redirect()->route('dashboard')->with('error', 'Invalid checkout parameters.');
            }

            $content = Content::findOrFail($contentId);
            $user = Auth::user();

            if (!$user) {
                return redirect()->route('login.user');
            }

            // Retrieve checkout session from Stripe
            $stripe = new StripeClient(config('cashier.secret'));
            $session = $stripe->checkout->sessions->retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return redirect()->route('dashboard')->with('error', 'Payment was not completed.');
            }

            // Get player url
            $playerUrl = route('dashboard');
            if ($content->contentable_type === 'App\Models\Movie') {
                $playerUrl = "/movie/{$content->contentable_id}/player";
            } elseif ($content->contentable_type === 'App\Models\Serie') {
                $playerUrl = "/serie/{$content->contentable_id}/player";
            }

            // Check if already processed
            $paymentIntentId = $session->payment_intent;
            $existing = Purchase::where('stripe_payment_intent_id', $paymentIntentId)->first();
            if ($existing) {
                return redirect()->to($playerUrl)->with('success', 'Purchase confirmed!');
            }

            // Calculate shares
            $amountPaid = (float)($session->amount_total / 100);
            $creatorSharePercent = (float)Setting::get('revenue_split_ratio', 50);
            $creatorShare = round(($amountPaid * $creatorSharePercent) / 100, 2);
            $platformShare = max(0.00, round($amountPaid - $creatorShare, 2));

            // Record purchase
            $purchase = Purchase::create([
                'user_id' => $user->id,
                'content_id' => $content->id,
                'amount' => $amountPaid,
                'creator_share' => $creatorShare,
                'platform_share' => $platformShare,
                'stripe_payment_intent_id' => $paymentIntentId,
                'status' => 'completed',
            ]);

            // Allocate creator earnings
            CreatorEarning::create([
                'creator_id' => $content->user_id,
                'amount' => $creatorShare,
                'source' => 'ppv_purchase',
                'source_id' => $purchase->id,
                'description' => "Pay-Per-View sale: " . ($content->contentable->title ?? 'Premium Content') . " (by @{$user->username})",
            ]);

            return redirect()->to($playerUrl)->with('success', 'Access unlocked successfully!');
        } catch (\Throwable $th) {
            return redirect()->route('dashboard')->with('error', $th->getMessage());
        }
    }
}
