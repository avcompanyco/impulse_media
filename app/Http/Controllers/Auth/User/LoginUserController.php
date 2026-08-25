<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use Illuminate\Http\RedirectResponse;

class LoginUserController extends Controller
{

    /**
     * Handle an incoming authentication request.
     */
    public function __invoke(LoginUserRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = auth()->user();
        if ($user && ($user->hasRole('admin') || $user->user_type === \App\Enums\User\UserType::ADMIN)) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
