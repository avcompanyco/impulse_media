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

        $frontpage = Content::whereIn('type', [ContentType::MOVIE, ContentType::SERIE])
            ->where('status', ContentStatus::PUBLISHED)
            ->with(['contentable', 'user'])
            ->inRandomOrder()
            ->limit(5)->get();

        $categories = Category::with(['movies' => function ($query) {
            $query->whereHas('content', function ($query) {
                $query->where('status', ContentStatus::PUBLISHED);
            })->inRandomOrder()->limit(20);
        }, 'series' => function ($query) {
            $query->whereHas('content', function ($query) {
                $query->where('status', ContentStatus::PUBLISHED);
            })->inRandomOrder()->limit(20);
        }])
        ->inRandomOrder()->limit(20)->get();

        return Inertia::render('user/dashboard/IndexUserDashboard', [
            'frontpage' => $frontpage,
            'categories' => $categories,
        ]);
    }
}
