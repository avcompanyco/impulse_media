<?php

namespace App\Traits\Category;

use App\Models\Category;

trait HasDeleteCategory
{
    public function delete(Category $category)
    {
        if ($category->hasSubcategories()) {
            throw new \Exception(__("Cannot delete category with subcategories"));
        }

        if ($category->hasMovies()) {
            throw new \Exception(__("Cannot delete category with movies"));
        }

        if ($category->hasSeries()) {
            throw new \Exception(__("Cannot delete category with series"));
        }   

        $category->deleteImage();
        $category->delete();
    }
}
