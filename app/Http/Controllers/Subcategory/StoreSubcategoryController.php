<?php

namespace App\Http\Controllers\Subcategory;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Requests\Subcategory\StoreSubcategoryRequest;
use App\Traits\Subcategory\HasCreateSubcategory;
use App\Models\Category;

class StoreSubcategoryController extends Controller
{
    use HasCreateSubcategory;

    public function __invoke(StoreSubcategoryRequest $request, Category $category)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to create a subcategory"));
            }

            $data = $request->validated();
            $data['category_id'] = $category->id;

            $this->create($data);

            return inertiaSuccessHandler(
                __("Success"),
                __("Subcategory created successfully")
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
