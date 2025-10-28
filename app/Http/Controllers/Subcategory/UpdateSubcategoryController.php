<?php

namespace App\Http\Controllers\Subcategory;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Requests\Subcategory\UpdateSubcategoryRequest;
use App\Traits\Subcategory\HasUpdateSubcategory;
use App\Models\Subcategory;
use App\Models\Category;

class UpdateSubcategoryController extends Controller
{
    use HasUpdateSubcategory;

    public function __invoke(UpdateSubcategoryRequest $request, Category $category, Subcategory $subcategory)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to update a subcategory"));
            }

            if ($subcategory->category_id !== $category->id) {
                throw new \Exception(__("Subcategory not found in this category"));
            }

            $data = $request->validated();

            $this->update($subcategory, $data);

            return inertiaSuccessHandler(
                __("Success"),
                __("Subcategory updated successfully")
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
