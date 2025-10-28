<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Plan;
use App\Enums\User\UserStatusEnum;
use Illuminate\Support\Facades\Auth;

class IndexUserController extends Controller
{
    public function __invoke()
    {
        if (!$this->canAccess()) {
            throw new \Exception(__("You are not authorized to access this resource"));
        }

        $plans = Plan::active()->get(['id', 'name', 'price', 'billing_period']);
        $statusOptions = UserStatusEnum::labels();
        
        return Inertia::render('admin/user/IndexUser', [
            'plans' => $plans,
            'statusOptions' => $statusOptions,
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
