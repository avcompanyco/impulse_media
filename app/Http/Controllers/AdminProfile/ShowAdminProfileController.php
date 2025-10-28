<?php

namespace App\Http\Controllers\AdminProfile;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ShowAdminProfileController extends Controller
{
    public function __invoke()
    {
        if (!$this->canAccess()) {
            throw new \Exception(__("You are not authorized to access this resource"));
        }

        $_user = User::find(Auth::user()->id);  // Get the user
        $plan = $_user->plan;

        
        return Inertia::render('admin/profile/ShowProfile', [
            'user' => $_user,
            'plan' => $plan,
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
