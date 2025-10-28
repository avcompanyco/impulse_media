<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ShowPaymentController extends Controller
{
    public function __invoke(Payment $payment)
    {
        if (!$this->canAccess()) {
            throw new \Exception(__("You are not authorized to access this resource"));
        }

        $payment->load(['user', 'plan']);

        return Inertia::render('admin/payment/ShowPayment', [
            'payment' => $payment,
        ]);
    }

    public function canAccess()
    {
        $_user = User::find(Auth::user()->id);
        if ($_user && $_user->hasRole('admin')) {
            return true;
        }
        return false;
    }
}