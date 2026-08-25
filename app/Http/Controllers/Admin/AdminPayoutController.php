<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payout;
use App\Models\Setting;
use App\Models\Purchase;
use App\Models\Payment;
use App\Models\CreatorEarning;
use App\Models\User;
use App\Enums\Payment\PaymentStatus;
use Inertia\Inertia;

class AdminPayoutController extends Controller
{
    public function __invoke(Request $request)
    {
        $pendingPayouts = Payout::with('creator')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $payoutsHistory = Payout::with('creator')
            ->where('status', '!=', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $settings = [
            'revenue_split_ratio' => (float)Setting::get('revenue_split_ratio', 50),
            'min_payout_threshold' => (float)Setting::get('min_payout_threshold', 50.00),
            'membership_discount_rate' => (float)Setting::get('membership_discount_rate', 10),
            'min_ppv_price' => (float)Setting::get('min_ppv_price', 0.99),
            'free_preview_seconds' => (int)Setting::get('free_preview_seconds', 300),
        ];

        // Platform Accounting Stats
        $grossPPVRevenue = (float) Purchase::where('status', 'completed')->sum('amount');
        $platformPPVRevenue = (float) Purchase::where('status', 'completed')->sum('platform_share');
        $totalSubscriptionPayments = (float) Payment::where('status', PaymentStatus::COMPLETED)->sum('amount');
        $creatorMembershipEarnings = (float) CreatorEarning::where('source', 'membership_split')->sum('amount');
        $platformSubscriptionShare = max(0.00, $totalSubscriptionPayments - $creatorMembershipEarnings);
        $platformEarnings = $platformPPVRevenue + $platformSubscriptionShare;

        $totalPaidOut = (float) Payout::where('status', 'approved')->sum('amount');
        $totalPending = (float) Payout::where('status', 'pending')->sum('amount');

        $platformStats = [
            'platform_earnings' => $platformEarnings,
            'gross_ppv_revenue' => $grossPPVRevenue,
            'platform_ppv_revenue' => $platformPPVRevenue,
            'subscription_revenue' => $totalSubscriptionPayments,
            'total_paid_out' => $totalPaidOut,
            'total_pending' => $totalPending,
        ];

        $recentPurchases = Purchase::with(['user', 'content.contentable'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($purchase) {
                $title = 'PPV Video';
                if ($purchase->content && $purchase->content->contentable) {
                    $title = $purchase->content->contentable->title ?? $purchase->content->contentable->name ?? 'Content #' . $purchase->content_id;
                }
                return [
                    'id' => $purchase->id,
                    'user_name' => $purchase->user?->name ?? 'Viewer',
                    'user_username' => $purchase->user?->username ?? 'user',
                    'user_email' => $purchase->user?->email ?? '-',
                    'user_image' => $purchase->user?->image_url ?? '/images/default-avatar.png',
                    'content_title' => $title,
                    'content_type' => $purchase->content?->type ?? 'movie',
                    'amount' => (float) $purchase->amount,
                    'creator_share' => (float) $purchase->creator_share,
                    'platform_share' => (float) $purchase->platform_share,
                    'stripe_payment_intent_id' => $purchase->stripe_payment_intent_id,
                    'created_at' => $purchase->created_at?->format('Y-m-d H:i:s'),
                ];
            });

        $creatorStats = User::where('user_type', \App\Enums\User\UserType::CREATOR)
            ->get()
            ->map(function ($creator) {
                return [
                    'id' => $creator->id,
                    'name' => $creator->name,
                    'username' => $creator->username,
                    'email' => $creator->email,
                    'image_url' => $creator->image_url,
                    'lifetime_earnings' => (float) $creator->lifetime_earnings,
                    'balance' => (float) $creator->creator_balance,
                    'total_paid' => (float) $creator->payouts()->where('status', 'approved')->sum('amount'),
                    'total_pending' => (float) $creator->payouts()->where('status', 'pending')->sum('amount'),
                ];
            });

        return Inertia::render('admin/payouts/IndexPayouts', [
            'pendingPayouts' => $pendingPayouts,
            'payoutsHistory' => $payoutsHistory,
            'recentPurchases' => $recentPurchases,
            'settings' => $settings,
            'platformStats' => $platformStats,
            'creatorStats' => $creatorStats,
        ]);
    }
}
