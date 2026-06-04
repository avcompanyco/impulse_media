<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class UpdateSettingsController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $data = $request->validate([
                'revenue_split_ratio' => 'required|numeric|min:0|max:100',
                'min_payout_threshold' => 'required|numeric|min:1.00',
                'membership_discount_rate' => 'required|numeric|min:0|max:100',
                'min_ppv_price' => 'required|numeric|min:0.01',
            ]);

            Setting::set('revenue_split_ratio', $data['revenue_split_ratio'], 'float');
            Setting::set('min_payout_threshold', $data['min_payout_threshold'], 'float');
            Setting::set('membership_discount_rate', $data['membership_discount_rate'], 'float');
            Setting::set('min_ppv_price', $data['min_ppv_price'], 'float');

            return back()->with('success', __('Monetization settings updated successfully.'));
        } catch (\Throwable $th) {
            return back()->withErrors(['revenue_split_ratio' => $th->getMessage()]);
        }
    }
}
