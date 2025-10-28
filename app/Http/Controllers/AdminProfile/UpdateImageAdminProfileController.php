<?php

namespace App\Http\Controllers\AdminProfile;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Requests\AdminProfile\UpdateImageProfileRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UpdateImageAdminProfileController extends Controller
{
    public function __invoke(UpdateImageProfileRequest $request)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to update the image of an admin"));
            }

            $data = $request->validated();

            $_user = User::find(Auth::user()->id);

            if ($data['image']) {
                $_user->updateImage($data['image']);
            }

            return inertiaSuccessHandler(
                __("Success"),
                __("Image updated successfully for admin")
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
