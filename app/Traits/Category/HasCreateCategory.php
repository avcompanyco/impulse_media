<?php

namespace App\Traits\Category;

use App\Models\Category;

trait HasCreateCategory
{
    public function create(array $data)
    {
        if (Category::where('name', $data['name'])->exists()) {
            throw new \Exception('Category already exists');
        }

        $image = null;
        if (isset($data['image'])) {
            $image = $data['image'];
            unset($data['image']);
        }
        $data['image'] = "";

        $category = Category::create($data);

        if ($image) {
            $category->updateImage($image);
        }

        return $category;
    }
}
