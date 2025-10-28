<?php

namespace App\Traits\Subcategory;

use App\Models\Subcategory;

trait HasUpdateSubcategory
{
    public function update(Subcategory $subcategory, array $data)
    {
        if (Subcategory::where('name', $data['name'])
            ->where('category_id', $subcategory->category_id)
            ->where('id', '!=', $subcategory->id)
            ->exists()
        ) {
            throw new \Exception(__("Subcategory already exists in this category"));
        }
        $subcategory->update($data);

        return $subcategory;
    }
}
