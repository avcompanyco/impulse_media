<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class IndexPaymentController extends Controller
{
    public function __invoke()
    {
        if (!$this->canAccess()) {
            throw new \Exception(__("You are not authorized to access this resource"));
        }

        $payments = Payment::with(['user', 'plan'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return Inertia::render('admin/payment/IndexPayment', [
            'payments' => $payments,
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