<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginAdminRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use Illuminate\Validation\ValidationException;


class LoginAdminController extends Controller
{
    public function __invoke(LoginAdminRequest $request): RedirectResponse
    {
        $user = User::where('email', $request->email)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->first();
        if (!$user) {
            return inertiaErrorHandler(
                __("Error"),
                __("Invalid email or password")
            );
        }
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }
}
