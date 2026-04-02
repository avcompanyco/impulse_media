<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Laravel\Cashier\Cashier;

class UserHasSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login.user');
        }
        $user = User::find(Auth::user()->id);
        if (env('APP_ENV') !== 'production') {
            // In non-production, check if user has a plan assigned
            $plan = $user->getCurrentPlan();
            if (!$plan) {
                return redirect()->route('subscription.cancel');
            }
        } else {
            if (!$user->subscribed('default')) {
                $plan = $user->getCurrentPlan();
                if (!$plan) {
                    return redirect()->route('subscription.cancel');
                }
            }
        }
        return $next($request);
    }
}
