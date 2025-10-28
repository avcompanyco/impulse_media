<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\VerificationCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class ConfirmCodeController extends Controller
{

    /**
     * Confirm the user's password.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate([
            'verificationCode' => 'required|string|max:6',
        ]);

        $verificationCode = VerificationCode::where('code', $request->verificationCode)->first();

        // error if the verification code is not found
        if (!$verificationCode) {
            return back()->with('error', __('Invalid verification code.'));
        }

        // error if the verification code is expired
        if ($verificationCode->expires_at < now()) {
            return back()->with('error', __('Verification code expired.'));
        }

        // check if the email has a password reset token
        DB::table('password_reset_tokens')->where('email', $verificationCode->email)->delete();

        // create password reset token and redirect to the reset password page
        $user = User::where('email', $verificationCode->email)->first();
        $token = Password::createToken($user);
   
        $verificationCode->delete();

        return redirect()->route('password.reset', [
            'token' => $token,
            'email' => $verificationCode->email,
        ]);
    }
}
