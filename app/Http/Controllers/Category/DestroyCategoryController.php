<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;

use App\Traits\Category\HasDeleteCategory;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class DestroyCategoryController extends Controller
{
    use HasDeleteCategory;

    public function __invoke(Category $category)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to delete a category"));
            }

            $this->delete($category);

            return inertiaSuccessHandler(
                __("Success"),
                __("Category deleted successfully")
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
