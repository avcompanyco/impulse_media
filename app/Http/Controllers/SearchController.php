<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Content;
use App\Models\Movie;
use App\Models\Serie;
use App\Enums\Content\ContentType;
use App\Enums\Content\ContentStatus;
use App\Models\Category;
use App\Models\Subcategory;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $category = $request->query('category');
        $subcategory = $request->query('subcategory');
        $search = $request->query('search');

        $contents = Content::with(['contentable' => function ($query) use ($category, $subcategory, $search) {
            // Filtrar por título si hay búsqueda
            if ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            }

            // Filtrar por categoría
            if ($category) {
                $query->where('category_id', $category);
            }

            // Filtrar por subcategoría
            if ($subcategory) {
                $query->where('subcategory_id', $subcategory);
            }
        }])->whereNot('type', ContentType::SHORT->value)->get();

        $categories = Category::with(['movies' => function ($query) {
                $query->whereHas('content', function ($query) {
                    $query->where('status', ContentStatus::PUBLISHED->value);
                });
                $query->inRandomOrder()->limit(20);
            }, 'series' => function ($query) {
                $query->whereHas('content', function ($query) {
                    $query->where('status', ContentStatus::PUBLISHED->value);
                });
                $query->inRandomOrder()->limit(20);
            }])
            ->get();

        return Inertia::render('user/search/Search', [
            'contents' => $contents,
            'categories' => $categories,
        ]);
    }
}
