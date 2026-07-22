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
                'payout_details' => [
                    'required',
                    'string',
                    'max:1000',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->input('payout_method') === 'paypal') {
                            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                $fail(__('For PayPal, the payout details must be a valid email address.'));
                            }
                        } else if ($request->input('payout_method') === 'bank_transfer') {
                            if (strlen($value) < 15) {
                                $fail(__('For Bank Transfer, please provide complete bank details (e.g., Bank Name, Account Holder Name, IBAN/Account Number, and Swift/BIC code).'));
                            }
                        }
                    }
                ],
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
        } catch (\Illuminate\Validation\ValidationException $ve) {
            throw $ve;
        } catch (\Throwable $th) {
            return back()->withErrors(['amount' => $th->getMessage()]);
        }
    }
}
