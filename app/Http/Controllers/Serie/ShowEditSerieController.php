<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\User;
use App\Models\Content;
use App\Models\Serie;
use App\Models\Category;
use App\Enums\Content\ContentType;
use App\Enums\Content\ContentStatus;

class ShowEditSerieController extends Controller
{
    public function __invoke(Serie $serie)
    {
        $_user = User::find(Auth::user()->id);


        try {
            $serie = Serie::where('id', $serie->id)
                ->where('user_id', $_user->id)
                ->whereHas('content', function ($query) {
                    $query->where('type', ContentType::SERIE->value)
                        ->where('status', ContentStatus::PUBLISHED->value);
                })
                ->first();
            

            if (!$serie) {
                throw new \Exception('Serie not found');
            }

            $serie->load(['content', 'category', 'subcategory', 'seasons' => function ($query) {
                $query->orderBy('id', 'asc');
            }, 'seasons.chapters']);

            $categories = Category::with('subcategories')->orderBy('name', 'asc')->get();

            return Inertia::render('user/serie/ShowEditSerie', [
                'serie' => $serie,
                'categories' => $categories,
            ]);
        } catch (\Throwable $th) {
            return redirect()->route('dashboard');
        }
    }
}
