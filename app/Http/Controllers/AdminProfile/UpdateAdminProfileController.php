<?php

namespace App\Http\Controllers\AdminProfile;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Requests\AdminProfile\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UpdateAdminProfileController extends Controller
{
    public function __invoke(UpdateProfileRequest $request)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to update the profile of an admin"));
            }

            $data = $request->validated();

            $_user = User::find(Auth::user()->id);

            $password = null;
            if (isset($data['password'])) {
                $password = $data['password'];
                unset($data['password']);
            }

            $_user->fill($data)->save();

            if ($password) {
                $_user->password = Hash::make($password);
                $_user->save();
            }

            return inertiaSuccessHandler(
                __("Success"),
                __("Profile updated successfully")
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
