<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\Plan;
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
        $userType = $data['user_type']; // 'spectator' or 'creator'

        try {
            DB::beginTransaction();

            // Find the default plan for this user type
            $defaultPlan = null;
            if ($userType === 'creator') {
                // Assign the Free creator plan
                $defaultPlan = Plan::where('plan_type', 'creator')
                    ->where('price', 0)
                    ->active()
                    ->first();
            }
            // Spectators don't get a plan assigned until they subscribe

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'user_type' => $userType,
                'accepted_terms_at' => now(),
                'plan_id' => $defaultPlan?->id,
            ]);

            // Assign the appropriate Spatie role
            $roleName = $userType === 'spectator' ? 'spectator' : 'user';
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $user->assignRole($role);
            }

            // Also assign the specific type role for easier checking
            $typeRole = Role::where('name', $userType)->first();
            if ($typeRole) {
                $user->assignRole($typeRole);
            }

            event(new Registered($user));

            // Log user registration event
            $this->binacleService->logUserRegistration($user);

            Auth::login($user);

            DB::commit();

            // Redirect based on user type
            if ($userType === 'spectator') {
                return to_route('dashboard');
            }

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
