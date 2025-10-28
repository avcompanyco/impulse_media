<?php

namespace App\Http\Controllers\Binacle;

use App\Http\Controllers\Controller;
use App\Models\Binacle;
use App\Http\Resources\Binacle\BinacleCollection;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class DatatableBinacleController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $binacles = Binacle::query()->with('user')->orderBy('id', 'desc');

            $page = $request->query('page', 1);
            $perPage = $request->query('perPage', 10);
            $search = $request->query('search', '');

            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to access this resource"));
            }

            return new BinacleCollection($binacles->paginate($perPage, ['*'], 'page', $page));
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
