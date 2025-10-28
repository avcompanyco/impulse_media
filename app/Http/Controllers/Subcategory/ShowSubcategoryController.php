<?php

namespace App\Http\Controllers\Subcategory;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\Subcategory;
use App\Enums\Content\ContentStatus;

class ShowSubcategoryController extends Controller
{
    public function __invoke(Subcategory $subcategory)
    {

        $subcategory->load(['movies.content', 'series.content', 'movies' => function ($query) {
            $query->whereHas('content', function ($query) {
                $query->where('status', ContentStatus::PUBLISHED->value);
            });
            $query->inRandomOrder()->limit(20);
        }, 'series' => function ($query) {
            $query->whereHas('content', function ($query) {
                $query->where('status', ContentStatus::PUBLISHED->value);
            });
            $query->inRandomOrder()->limit(20);
        }]);
        $subcategories = Subcategory::where('category_id', $subcategory->category_id)
            ->whereNot('id', $subcategory->id)
            ->with(['movies.content', 'series.content', 'movies' => function ($query) {
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

        return Inertia::render('user/subcategory/ShowSubcategory', [
            'subcategory' => $subcategory,
            'subcategories' => $subcategories,
        ]);
    }
}
