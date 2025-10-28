<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\Category\CategoryCollection;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class DatatableCategoryController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $categories = Category::query()->orderBy('id', 'desc');

            $page = $request->query('page', 1);
            $perPage = $request->query('perPage', 10);
            $search = $request->query('search', '');

            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to access this resource"));
            }

            if ($search) {
                // name
                $categories->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                });
            }

            return new CategoryCollection($categories->paginate($perPage, ['*'], 'page', $page));
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
