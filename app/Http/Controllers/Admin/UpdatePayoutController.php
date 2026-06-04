<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payout;

class UpdatePayoutController extends Controller
{
    public function __invoke(Request $request, Payout $payout)
    {
        try {
            if ($payout->status !== 'pending') {
                throw new \Exception(__('This payout request has already been processed.'));
            }

            $data = $request->validate([
                'status' => 'required|string|in:approved,rejected',
                'rejection_reason' => 'required_if:status,rejected|nullable|string|max:1000',
            ]);

            $payout->update([
                'status' => $data['status'],
                'rejection_reason' => $data['status'] === 'rejected' ? $data['rejection_reason'] : null,
                'processed_at' => now(),
            ]);

            $message = $data['status'] === 'approved' 
                ? __('Payout approved successfully.') 
                : __('Payout rejected successfully.');

            return back()->with('success', $message);
        } catch (\Throwable $th) {
            return back()->withErrors(['status' => $th->getMessage()]);
        }
    }
}
