<?php

namespace App\Http\Controllers\Follow;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Traits\Follow\HasAddFollow;

class AddToFollowController extends Controller
{
    use HasAddFollow;

    public function __invoke(User $user)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to add a follow"));
            }

            $this->add(Auth::user(), $user);

            return inertiaSuccessHandler(
                __("Success"),
                __("Follow added successfully")
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
