<?php

namespace App\Http\Controllers\Follow;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Models\Follow;

class ShowFollowController extends Controller
{
    public function __invoke($type, $id)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to show a follow"));
            }

            return inertiaSuccessHandler(
                __("Success"),
                __("Follow shown successfully")
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
