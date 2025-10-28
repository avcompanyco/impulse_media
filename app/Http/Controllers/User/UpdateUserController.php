<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Requests\User\UpdateUserRequest;
use App\Traits\User\HasUpdateUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UpdateUserController extends Controller
{
    use HasUpdateUser;

    public function __invoke(UpdateUserRequest $request, User $user)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to update a user"));
            }

            $this->update($user, $request->validated());

            return inertiaSuccessHandler(
                __("Success"),
                __("User updated successfully")
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
