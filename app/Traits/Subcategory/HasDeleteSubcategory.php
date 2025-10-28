<?php

namespace App\Traits\Subcategory;

use App\Models\Subcategory;

trait HasDeleteSubcategory
{
    public function delete(Subcategory $subcategory)
    {
        if ($subcategory->hasMovies()) {
            throw new \Exception(__("Subcategory has movies"));
        }
        if ($subcategory->hasSeries()) {
            throw new \Exception(__("Subcategory has series"));
        }
        $subcategory->delete();
    }
}
