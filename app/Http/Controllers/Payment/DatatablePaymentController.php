<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\PaymentCollection;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DatatablePaymentController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $payments = Payment::query()
                ->with(['user', 'plan'])
                ->orderBy('id', 'desc');

            $page = $request->query('page', 1);
            $perPage = $request->query('perPage', 10);
            $search = $request->query('search', '');
            $status = $request->query('status', '');

            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to access this resource"));
            }

            if ($search) {
                $payments->whereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                })->orWhere('stripe_payment_intent_id', 'like', '%' . $search . '%');
            }

            if ($status) {
                $payments->where('status', $status);
            }

            return new PaymentCollection($payments->paginate($perPage, ['*'], 'page', $page));
        } catch (\Throwable $th) {
            return response()->json([]);
        }
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