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
use App\Http\Resources\Search\FilterSearchContentCollection;

class FilterSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $category = $request->query('category');
        $search = $request->query('search');

        $page = $request->query('page', 1);
        $perPage = $request->query('perPage', 100);


        $contents = Content::whereNot('type', ContentType::SHORT->value)
            ->when($search, function ($query) use ($search) {
                $query->whereHasMorph('contentable', [Movie::class, Serie::class], function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%');
                });
            })
            ->when($category, function ($query) use ($category) {
                $query->whereHasMorph('contentable', [Movie::class, Serie::class], function ($q) use ($category) {
                    $q->where('category_id', $category);
                });
            })
            ->inRandomOrder();


        $contents->with(['contentable']);

        return $contents->paginate($perPage, ['*'], 'page', $page);
    }
}
