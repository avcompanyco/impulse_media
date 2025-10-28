<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\VerificationCodeMail;
use App\Models\VerificationCode;

class SendVerificationCodeController extends Controller
{
    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {

            // check if has verification code for the email and delete it
            VerificationCode::where('email', $request->email)->delete();

            $code = $this->generateVerificationCode();

            $verificationCode = VerificationCode::create([
                'code' => $code,
                'email' => $request->email,
                'expires_at' => now()->addMinutes(15),
            ]);

            Mail::to($request->email)->send(new VerificationCodeMail($verificationCode->code, $request->email));

            return redirect()->route('password.confirm-code.show');
        } catch (\Throwable $th) {
            return back()->with('error', __('An error occurred while sending the verification code.'));
        }
    }


    private function generateVerificationCode()
    {
        $code = random_int(100000, 999999);
        while (VerificationCode::where('code', $code)->exists()) {
            $code = random_int(100000, 999999);
        }
        return $code;
    }
}
