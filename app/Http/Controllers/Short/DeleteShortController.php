<?php

namespace App\Http\Controllers\Short;

use App\Http\Controllers\Controller;

use App\Traits\Short\HasDeleteShort;
use App\Models\Short;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DeleteShortController extends Controller
{
    use HasDeleteShort;

    public function __invoke(Short $short)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to delete a short"));
            }

            $_user = User::find(Auth::user()->id);
            if ($short->user_id !== $_user->id) {
                throw new \Exception(__("You are not authorized to delete a short"));
            }

            $this->delete($short);

            return inertiaSuccessHandler(
                __("Success"),
                __("Short deleted successfully")
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
