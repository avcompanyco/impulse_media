<?php

namespace App\Http\Controllers\UserProfile;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Requests\UserProfile\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UpdateProfileController extends Controller
{
    public function __invoke(UpdateProfileRequest $request)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to update the profile of a user"));
            }

            $data = $request->validated();

            $_user = User::find(Auth::user()->id);

            $password = $data['password'] ?? null;
            unset($data['password']);
            unset($data['password_confirmation']);

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
        if ($_user && $_user->hasAnyRole(['user', 'spectator', 'creator'])) {
            return true;
        }
        return false;
    }
}
