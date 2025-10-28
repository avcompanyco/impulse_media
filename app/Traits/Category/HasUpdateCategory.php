<?php

namespace App\Traits\Category;

use App\Models\Category;

trait HasUpdateCategory
{
    public function update(Category $category, array $data)
    {
        if (Category::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) {
            throw new \Exception('Category already exists');
        }

        if (isset($data['image'])) {
            $category->updateImage($data['image']);
            unset($data['image']);
        }
        $category->update($data);

    }
}
