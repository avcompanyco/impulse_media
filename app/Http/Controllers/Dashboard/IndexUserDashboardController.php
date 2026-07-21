<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Models\User;
use App\Models\Movie;
use App\Models\Content;
use App\Models\Category;
use App\Enums\Content\ContentType;
use App\Enums\Content\ContentStatus;

class IndexUserDashboardController extends Controller
{
    public function __invoke()
    {
        // 1. Featured Pool for Carousel
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
        })->with('content')->get();

        $publishedSeries = Serie::whereHas('content', function ($q) {
            $q->where('status', ContentStatus::PUBLISHED);
        })->with('content')->get();

        // Section A: Popular & New Releases
        $popularMovies = $publishedMovies->sortByDesc(fn($m) => $m->content?->views_count ?? 0)->take(15)->values();
        $popularSeries = $publishedSeries->sortByDesc(fn($s) => $s->content?->views_count ?? 0)->take(15)->values();

        // Section B: Pay Per View
        $ppvMovies = $publishedMovies->filter(fn($m) => $m->content && (!$m->content->allow_membership || (float)$m->content->ppv_price > 0))->values();
        $ppvSeries = $publishedSeries->filter(fn($s) => $s->content && (!$s->content->allow_membership || (float)$s->content->ppv_price > 0))->values();

        // Section C: All Categories from DB
        $allCategories = Category::with(['movies' => function ($query) {
            $query->whereHas('content', function ($query) {
                $query->where('status', ContentStatus::PUBLISHED);
            })->with('content');
        }, 'series' => function ($query) {
            $query->whereHas('content', function ($query) {
                $query->where('status', ContentStatus::PUBLISHED);
            })->with('content');
        }])->get();

        $moviesCategory = $allCategories->first(fn($c) => strtolower(trim($c->name)) === 'movies');
        $seriesCategory = $allCategories->first(fn($c) => in_array(strtolower(trim($c->name)), ['series', 'tv shows', 'tv show']));
        $docsCategory = $allCategories->first(fn($c) => in_array(strtolower(trim($c->name)), ['documentaries', 'documentary', 'documentales']));

        $categories = collect();

        // 1. Popular Section
        if ($popularMovies->isNotEmpty() || $popularSeries->isNotEmpty()) {
            $categories->push([
                'id' => 'popular',
                'name' => 'Popular & New Releases',
                'movies' => $popularMovies,
                'series' => $popularSeries,
            ]);
        }

        // 2. Pay Per View Section
        if ($ppvMovies->isNotEmpty() || $ppvSeries->isNotEmpty()) {
            $categories->push([
                'id' => 'pay_per_view',
                'name' => 'Pay Per View',
                'movies' => $ppvMovies,
                'series' => $ppvSeries,
            ]);
        }

        // 3. Movies Section
        if ($moviesCategory) {
            $categories->push($moviesCategory);
        } else if ($publishedMovies->isNotEmpty()) {
            $categories->push([
                'id' => 'all_movies',
                'name' => 'Movies',
                'movies' => $publishedMovies,
                'series' => collect(),
            ]);
        }

        // 4. TV Shows / Series Section
        if ($seriesCategory) {
            $categories->push($seriesCategory);
        } else if ($publishedSeries->isNotEmpty()) {
            $categories->push([
                'id' => 'all_series',
                'name' => 'TV Shows',
                'movies' => collect(),
                'series' => $publishedSeries,
            ]);
        }

        // 5. Documentaries Section
        if ($docsCategory) {
            $categories->push($docsCategory);
        }

        // 6. Remaining categories
        $usedCategoryIds = array_filter([$moviesCategory?->id, $seriesCategory?->id, $docsCategory?->id]);
        $remainingCategories = $allCategories->reject(fn($c) => in_array($c->id, $usedCategoryIds));
        foreach ($remainingCategories as $cat) {
            $categories->push($cat);
        }

        return Inertia::render('user/dashboard/IndexUserDashboard', [
            'frontpage' => $frontpage,
            'categories' => $categories->values(),
        ]);
    }
}
