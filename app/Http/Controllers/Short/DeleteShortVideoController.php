<?php

namespace App\Http\Controllers\Short;   

use App\Http\Controllers\Controller;

use App\Models\Short;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DeleteShortVideoController extends Controller
{

    public function __invoke(Short $short)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to delete a short video for a short"));
            }

            $_user = User::find(Auth::user()->id);
            if ($short->user_id !== $_user->id) {
                throw new \Exception(__("You are not authorized to delete a short video for a short"));
            }

            $short->deleteVideoShort();

            return inertiaSuccessHandler(
                __("Success"),
                __("Short video deleted successfully")
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
