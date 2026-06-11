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
        ];

        // Platform Accounting Stats
        $platformPPVRevenue = (float) Purchase::where('status', 'completed')->sum('platform_share');
        $totalSubscriptionPayments = (float) Payment::where('status', PaymentStatus::COMPLETED)->sum('amount');
        $creatorMembershipEarnings = (float) CreatorEarning::where('source', 'membership_split')->sum('amount');
        $platformSubscriptionShare = max(0.00, $totalSubscriptionPayments - $creatorMembershipEarnings);
        $platformEarnings = $platformPPVRevenue + $platformSubscriptionShare;

        $totalPaidOut = (float) Payout::where('status', 'approved')->sum('amount');
        $totalPending = (float) Payout::where('status', 'pending')->sum('amount');

        $platformStats = [
            'platform_earnings' => $platformEarnings,
            'total_paid_out' => $totalPaidOut,
            'total_pending' => $totalPending,
        ];

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
            'settings' => $settings,
            'platformStats' => $platformStats,
            'creatorStats' => $creatorStats,
        ]);
    }
}
