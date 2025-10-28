<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Traits\Category\HasUpdateCategory;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UpdateCategoryController extends Controller
{
    use HasUpdateCategory;

    public function __invoke(UpdateCategoryRequest $request, Category $category)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to update a category"));
            }

            $this->update($category, $request->validated());

            return inertiaSuccessHandler(
                __("Success"),
                __("Category updated successfully")
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
