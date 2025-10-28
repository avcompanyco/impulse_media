<?php

namespace App\Http\Controllers\Subcategory;

use App\Http\Controllers\Controller;

use App\Traits\Subcategory\HasDeleteSubcategory;
use App\Models\Subcategory;
use App\Models\Category;

class DestroySubcategoryController extends Controller
{
    use HasDeleteSubcategory;

    public function __invoke(Category $category, Subcategory $subcategory)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to delete a subcategory"));
            }

            if ($subcategory->category_id !== $category->id) {
                throw new \Exception(__("Subcategory not found in this category"));
            }

            $this->delete($subcategory);

            return inertiaSuccessHandler(
                __("Success"),
                __("Subcategory deleted successfully")
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
        return true;
    }
}
