<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Requests\User\StoreUserRequest;
use App\Traits\User\HasCreateUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class StoreUserController extends Controller
{
    use HasCreateUser;

    public function __invoke(StoreUserRequest $request)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to create a user"));
            }

            $this->create($request->validated());

            return inertiaSuccessHandler(
                __("Success"),
                __("User created successfully")
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
