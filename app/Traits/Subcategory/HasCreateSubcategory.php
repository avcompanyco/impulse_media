<?php

namespace App\Traits\Subcategory;

use App\Models\Subcategory;

trait HasCreateSubcategory
{
    public function create(array $data)
    {
        if (Subcategory::where('name', $data['name'])->where('category_id', $data['category_id'])->exists()) {
            throw new \Exception(__("Subcategory already exists in this category"));
        }

        $subcategory = Subcategory::create($data);

        return $subcategory;
    }
}
