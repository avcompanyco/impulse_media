<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payout;
use App\Models\Setting;
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

        return Inertia::render('admin/payouts/IndexPayouts', [
            'pendingPayouts' => $pendingPayouts,
            'payoutsHistory' => $payoutsHistory,
            'settings' => $settings,
        ]);
    }
}
