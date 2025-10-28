<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\User;
use App\Services\BinacleService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RegisterUserController extends Controller
{
    public function __construct(private BinacleService $binacleService) {}

    /**
     * Handle an incoming authentication request.
     */
    public function __invoke(RegisterUserRequest $request): RedirectResponse
    {

        $data = $request->validated();

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
            ]);

            $user_role = Role::where('name', 'user')->first();
            $user->assignRole($user_role);

            event(new Registered($user));

            // Log user registration event
            $this->binacleService->logUserRegistration($user);

            Auth::login($user);

            DB::commit();

            return to_route('dashboard');
        } catch (\Throwable $th) {
            DB::rollBack();

            return inertiaErrorHandler(
                __('Error'),
                $th->getMessage()
            );
        }
    }
}
