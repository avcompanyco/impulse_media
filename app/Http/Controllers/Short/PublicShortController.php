<?php

namespace App\Http\Controllers\Short;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PublicShortController extends Controller
{
    public function __invoke()
    {
        // Redirect already-authenticated users to their dashboard
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('dashboard');
        }

        // Clear previous session so every new visit starts fresh
        session()->forget('public_previous_short_ids');

        return Inertia::render('PublicShorts');
    }
}
