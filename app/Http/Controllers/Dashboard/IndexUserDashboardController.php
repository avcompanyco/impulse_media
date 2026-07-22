<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Models\Movie;
use App\Models\Serie;
use App\Models\Content;
use App\Models\Category;
use App\Enums\Content\ContentType;
use App\Enums\Content\ContentStatus;

class IndexUserDashboardController extends Controller
{
    public function __invoke()
    {
        // 1. Featured Pool for Carousel (Only Movies & Series)
        $featured = Content::whereIn('type', [ContentType::MOVIE, ContentType::SERIE])
            ->where('status', ContentStatus::PUBLISHED)
            ->where('is_featured', true)
            ->with(['contentable', 'user'])
            ->latest()
            ->limit(5)->get();

        if ($featured->count() < 5) {
            $existingIds = $featured->pluck('id');
            $fillers = Content::whereIn('type', [ContentType::MOVIE, ContentType::SERIE])
                ->where('status', ContentStatus::PUBLISHED)
                ->whereNotIn('id', $existingIds)
                ->with(['contentable', 'user'])
                ->orderBy('views_count', 'desc')
                ->latest()
                ->limit(5 - $featured->count())->get();
            $frontpage = $featured->concat($fillers);
        } else {
            $frontpage = $featured;
        }

        // 2. Query All Published Movies & Series with Content
        $publishedMovies = Movie::whereHas('content', function ($q) {
            $q->where('status', ContentStatus::PUBLISHED);
        })->with(['content', 'category'])->latest()->get();

        $publishedSeries = Serie::whereHas('content', function ($q) {
            $q->where('status', ContentStatus::PUBLISHED);
        })->with(['content', 'category'])->latest()->get();

        // Section 1: Popular & New Releases (Algorithm: Sorted by highest views_count & created_at)
        $popularMovies = $publishedMovies->sortByDesc(fn($m) => ($m->content?->views_count ?? 0) * 1000 + ($m->content?->id ?? 0))->take(15)->values();
        $popularSeries = $publishedSeries->sortByDesc(fn($s) => ($s->content?->views_count ?? 0) * 1000 + ($s->content?->id ?? 0))->take(15)->values();

        // Section 2: Pay Per View (All content with ppv_price > 0 or allow_membership = false)
        $ppvMovies = $publishedMovies->filter(fn($m) => $m->content && (!$m->content->allow_membership || (float)$m->content->ppv_price > 0))->values();
        $ppvSeries = $publishedSeries->filter(fn($s) => $s->content && (!$s->content->allow_membership || (float)$s->content->ppv_price > 0))->values();

        // Section 3: Movies (ALL Movies regardless of free/paid/category)
        $allMoviesSection = $publishedMovies->values();

        // Section 4: TV Shows / Series (ALL Series regardless of free/paid/category)
        $allSeriesSection = $publishedSeries->values();

        // Section 5: Documentaries Category Lookup
        $allCategories = Category::with(['movies' => function ($query) {
            $query->whereHas('content', function ($query) {
                $query->where('status', ContentStatus::PUBLISHED);
            })->with('content');
        }, 'series' => function ($query) {
            $query->whereHas('content', function ($query) {
                $query->where('status', ContentStatus::PUBLISHED);
            })->with('content');
        }])->get();

        $docsCategory = $allCategories->first(fn($c) => in_array(strtolower(trim($c->name)), ['documentaries', 'documentary', 'documentales']));

        $categories = collect();

        // 1. Popular & New Releases
        $categories->push([
            'id' => 'popular',
            'name' => 'Popular & New Releases',
            'movies' => $popularMovies,
            'series' => $popularSeries,
        ]);

        // 2. Pay Per View
        $categories->push([
            'id' => 'pay_per_view',
            'name' => 'Pay Per View',
            'movies' => $ppvMovies,
            'series' => $ppvSeries,
        ]);

        // 3. Movies (All Movies)
        $categories->push([
            'id' => 'all_movies',
            'name' => 'Movies',
            'movies' => $allMoviesSection,
            'series' => collect(),
        ]);

        // 4. TV Shows (All Series)
        $categories->push([
            'id' => 'all_series',
            'name' => 'TV Shows',
            'movies' => collect(),
            'series' => $allSeriesSection,
        ]);

        // 5. Documentaries
        if ($docsCategory) {
            $categories->push([
                'id' => $docsCategory->id,
                'name' => 'Documentary',
                'movies' => $docsCategory->movies,
                'series' => $docsCategory->series,
            ]);
        } else {
            // Fallback filtering by category/subcategory name if DB model not found
            $docMovies = $publishedMovies->filter(fn($m) => str_contains(strtolower($m->category?->name ?? ''), 'doc'))->values();
            $docSeries = $publishedSeries->filter(fn($s) => str_contains(strtolower($s->category?->name ?? ''), 'doc'))->values();
            
            $categories->push([
                'id' => 'documentary',
                'name' => 'Documentary',
                'movies' => $docMovies,
                'series' => $docSeries,
            ]);
        }

        // NOTE: Per Mark's specification, NO extra categories (Sci-Fi, Adventure, etc.) below Documentary!

        return Inertia::render('user/dashboard/IndexUserDashboard', [
            'frontpage' => $frontpage,
            'categories' => $categories->values(),
        ]);
    }
}
