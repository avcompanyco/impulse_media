<?php

namespace App\Http\Controllers\Follow;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use App\Traits\Follow\HasRemoveFollow;

class RemoveToFollowController extends Controller
{
    use HasRemoveFollow;

    public function __invoke(User $user)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to remove a follow"));
            }

            $this->remove(Auth::user(), $user);

            return inertiaSuccessHandler(
                __("Success"),
                __("Follow removed successfully")
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
        return true;
    }
}
