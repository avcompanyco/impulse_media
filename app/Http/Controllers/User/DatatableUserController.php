<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\User;
use App\Http\Resources\User\UserCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DatatableUserController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $users = User::query()->orderBy('id', 'desc');

            $page = $request->query('page', 1);
            $perPage = $request->query('perPage', 10);
            $search = $request->query('search', '');

            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to access this resource"));
            }

            if ($search) {
                // name or email
                $users->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });

            }
            $users->whereHas('roles', function ($query){
                // role name user spatie permissions
                $query->where('name', 'user');
            });

            $users->with('roles');

            return new UserCollection($users->paginate($perPage, ['*'], 'page', $page));
        } catch (\Throwable $th) {
            return response()->json([]);
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
