<?php

namespace App\Http\Controllers\UserProfile;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Requests\UserProfile\UpdateImageProfileRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UpdateImageProfileController extends Controller
{
    public function __invoke(UpdateImageProfileRequest $request)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to update the image of a user"));
            }

            $data = $request->validated();

            $_user = User::find(Auth::user()->id);

            if ($data['image']) {
                $_user->updateImage($data['image']);
            }

            return inertiaSuccessHandler(
                __("Success"),
                __("Image updated successfully")
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
        if ($_user && $_user->hasRole('user')) {
            return true;
        }
        return false;
    }
}
