<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Traits\Category\HasCreateCategory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class StoreCategoryController extends Controller
{
    use HasCreateCategory;

    public function __invoke(StoreCategoryRequest $request)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to create a category"));
            }

            $this->create($request->validated());

            return inertiaSuccessHandler(
                __("Success"),
                __("Category created successfully")
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
