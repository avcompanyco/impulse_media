<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Movie;
use App\Models\Serie;
use App\Enums\Content\ContentStatus;

class ShowCategoryController extends Controller
{
    public function __invoke($category)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to access this resource"));
            }

            $catKey = strtolower(trim((string)$category));

            // Virtual Section 1: Popular & New Releases
            if ($catKey === 'popular' || $catKey === 'popular-new-releases') {
                $popularMovies = Movie::whereHas('content', fn($q) => $q->where('status', ContentStatus::PUBLISHED->value))
                    ->with('content')->get()->sortByDesc(fn($m) => $m->content?->views_count ?? 0)->values();
                $popularSeries = Serie::whereHas('content', fn($q) => $q->where('status', ContentStatus::PUBLISHED->value))
                    ->with('content')->get()->sortByDesc(fn($s) => $s->content?->views_count ?? 0)->values();

                return Inertia::render('user/category/ShowCategory', [
                    'category' => [
                        'id' => 'popular',
                        'name' => 'Popular & New Releases',
                    ],
                    'subcategories' => [
                        [
                            'id' => 99901,
                            'name' => 'Popular & New Releases',
                            'movies' => $popularMovies,
                            'series' => $popularSeries,
                        ]
                    ],
                ]);
            }

            // Virtual Section 2: Pay Per View
            if ($catKey === 'pay_per_view' || $catKey === 'pay-per-view') {
                $ppvMovies = Movie::whereHas('content', function ($q) {
                    $q->where('status', ContentStatus::PUBLISHED->value)
                      ->where(fn($q2) => $q2->where('allow_membership', false)->orWhere('ppv_price', '>', 0));
                })->with('content')->get()->values();

                $ppvSeries = Serie::whereHas('content', function ($q) {
                    $q->where('status', ContentStatus::PUBLISHED->value)
                      ->where(fn($q2) => $q2->where('allow_membership', false)->orWhere('ppv_price', '>', 0));
                })->with('content')->get()->values();

                return Inertia::render('user/category/ShowCategory', [
                    'category' => [
                        'id' => 'pay_per_view',
                        'name' => 'Pay Per View',
                    ],
                    'subcategories' => [
                        [
                            'id' => 99902,
                            'name' => 'Pay Per View Content',
                            'movies' => $ppvMovies,
                            'series' => $ppvSeries,
                        ]
                    ],
                ]);
            }

            // Virtual Section 3: Movies
            if ($catKey === 'all_movies' || $catKey === 'movies') {
                $allMovies = Movie::whereHas('content', fn($q) => $q->where('status', ContentStatus::PUBLISHED->value))->with('content')->get()->values();

                return Inertia::render('user/category/ShowCategory', [
                    'category' => [
                        'id' => 'all_movies',
                        'name' => 'Movies',
                    ],
                    'subcategories' => [
                        [
                            'id' => 99903,
                            'name' => 'All Movies',
                            'movies' => $allMovies,
                            'series' => collect(),
                        ]
                    ],
                ]);
            }

            // Virtual Section 4: TV Shows / Series
            if ($catKey === 'all_series' || $catKey === 'series' || $catKey === 'tv-shows') {
                $allSeries = Serie::whereHas('content', fn($q) => $q->where('status', ContentStatus::PUBLISHED->value))->with('content')->get()->values();

                return Inertia::render('user/category/ShowCategory', [
                    'category' => [
                        'id' => 'all_series',
                        'name' => 'TV Shows',
                    ],
                    'subcategories' => [
                        [
                            'id' => 99904,
                            'name' => 'All TV Shows',
                            'movies' => collect(),
                            'series' => $allSeries,
                        ]
                    ],
                ]);
            }

            // Virtual Section 5: Documentary
            if ($catKey === 'documentary' || $catKey === 'documentaries') {
                $docCategory = Category::where('name', 'LIKE', '%documentar%')->first();
                $docMovies = Movie::whereHas('content', fn($q) => $q->where('status', ContentStatus::PUBLISHED->value))
                    ->when($docCategory, fn($q) => $q->where('category_id', $docCategory->id))
                    ->with('content')->get()->values();

                $docSeries = Serie::whereHas('content', fn($q) => $q->where('status', ContentStatus::PUBLISHED->value))
                    ->when($docCategory, fn($q) => $q->where('category_id', $docCategory->id))
                    ->with('content')->get()->values();

                return Inertia::render('user/category/ShowCategory', [
                    'category' => [
                        'id' => 'documentary',
                        'name' => 'Documentary',
                    ],
                    'subcategories' => [
                        [
                            'id' => 99905,
                            'name' => 'Documentary Collection',
                            'movies' => $docMovies,
                            'series' => $docSeries,
                        ]
                    ],
                ]);
            }

            // Standard DB Category Lookup
            if (is_numeric($category)) {
                $categoryModel = Category::find($category);
            } else {
                $categoryModel = Category::where('slug', $category)->orWhere('name', 'LIKE', $category)->first();
            }

            if (!$categoryModel) {
                return redirect()->route('dashboard');
            }

            $subcategories = Subcategory::where('category_id', $categoryModel->id)
                ->with(['movies.content','series.content','movies' => function ($query) {
                    $query->whereHas('content', function ($query) {
                        $query->where('status', ContentStatus::PUBLISHED->value);
                    });
                }, 'series' => function ($query) {
                    $query->whereHas('content', function ($query) {
                        $query->where('status', ContentStatus::PUBLISHED->value);
                    });
                }])
                ->get();

            // If no subcategories exist for this category, create a default subcategory container
            if ($subcategories->isEmpty()) {
                $catMovies = Movie::where('category_id', $categoryModel->id)
                    ->whereHas('content', fn($q) => $q->where('status', ContentStatus::PUBLISHED->value))
                    ->with('content')->get();

                $catSeries = Serie::where('category_id', $categoryModel->id)
                    ->whereHas('content', fn($q) => $q->where('status', ContentStatus::PUBLISHED->value))
                    ->with('content')->get();

                $subcategories = collect([
                    [
                        'id' => $categoryModel->id,
                        'name' => $categoryModel->name,
                        'movies' => $catMovies,
                        'series' => $catSeries,
                    ]
                ]);
            }

            return Inertia::render('user/category/ShowCategory', [
                'category' => $categoryModel,
                'subcategories' => $subcategories,
            ]);
        } catch (\Throwable $th) {
            return redirect()->route('dashboard');
        }
    }

    public function canAccess()
    {
        $user = Auth::user();
        if ($user) {
            return true;
        }
        return false;
    }
}
