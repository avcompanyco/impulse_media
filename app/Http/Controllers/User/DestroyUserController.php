<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Traits\User\HasDeleteUser;
use App\Models\User;

class DestroyUserController extends Controller
{
    use HasDeleteUser;

    public function __invoke(User $user)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to delete a user"));
            }

            $this->delete($user);

            return inertiaSuccessHandler(
                __("Success"),
                __("User deleted successfully")
            );
        } catch (\Throwable $th) {
            return inertiaErrorHandler(
                __("Error"),
                $th->getMessage()
            );
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
