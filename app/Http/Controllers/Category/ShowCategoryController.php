<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\Category;
use App\Models\Subcategory;
use App\Enums\Content\ContentStatus;
use App\Models\User;

class ShowCategoryController extends Controller
{
    public function __invoke(Category $category)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to access this resource"));
            }
    
            $subcategories = Subcategory::where('category_id', $category->id)
                ->with(['movies.content','series.content','movies' => function ($query) {
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
    
            return Inertia::render('user/category/ShowCategory', [
                'category' => $category,
                'subcategories' => $subcategories,
            ]);
        } catch (\Throwable $th) {
            return inertiaErrorHandler(
                __("Error"),
                $th->getMessage()
            );
        }
    }

    public function canAccess()
    {
        $_user = User::find(Auth::user()->id);
        if ($_user && $_user->hasRole('admin')) {
            return true;
        }
        return false;
    }
}
