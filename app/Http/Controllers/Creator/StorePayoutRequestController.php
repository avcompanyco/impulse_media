<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Payout;
use App\Models\Setting;

class StorePayoutRequestController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $user = User::findOrFail(Auth::id());

            if ($user->isSpectator()) {
                throw new \Exception(__("Spectators cannot request payouts."));
            }

            $minThreshold = (float)Setting::get('min_payout_threshold', 50.00);
            $availableBalance = (float)$user->creator_balance;

            $data = $request->validate([
                'amount' => [
                    'required',
                    'numeric',
                    'min:' . $minThreshold,
                    'max:' . $availableBalance,
                ],
                'payout_method' => 'required|string|in:paypal,bank_transfer',
                'payout_details' => 'required|string|max:1000',
            ]);

            // Create payout request
            Payout::create([
                'creator_id' => $user->id,
                'amount' => $data['amount'],
                'status' => 'pending',
                'payout_method' => $data['payout_method'],
                'payout_details' => $data['payout_details'],
            ]);

            // Persist payout preferences on the user profile for future pre-fill
            $user->update([
                'payout_method' => $data['payout_method'],
                'payout_details' => $data['payout_details'],
            ]);

            return back()->with('success', __('Payout requested successfully.'));
        } catch (\Throwable $th) {
            return back()->withErrors(['amount' => $th->getMessage()]);
        }
    }
}
