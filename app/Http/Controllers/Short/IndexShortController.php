<?php

namespace App\Http\Controllers\Short;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\Short;
use App\Models\Content;
use App\Enums\Content\ContentStatus;

class IndexShortController extends Controller
{
    public function __invoke(Request $request)
    {
        // Get query parameters
        $shortId = $request->query('short');
        $direction = $request->query('direction', 'random'); // next, prev, random
        
        // Get all published shorts with their content and user info
        $shortsQuery = Short::whereHas('content', function ($query) {
            $query->where('status', ContentStatus::PUBLISHED->value);
        })->with(['content', 'user' => function ($query) {
            $query->select('id', 'name', 'username', 'image');
        }]);

        $currentShort = null;
        $nextShorts = [];
        $prevShorts = [];

        if ($shortId) {
            // Find the specific short
            $currentShort = $shortsQuery->find($shortId);
            
            if (!$currentShort) {
                // If short not found, get a random one
                $currentShort = $shortsQuery->inRandomOrder()->first();
            } else {
                // Get surrounding shorts for navigation
                $nextShorts = Short::whereHas('content', function ($query) {
                    $query->where('status', ContentStatus::PUBLISHED->value);
                })
                ->where('id', '>', $currentShort->id)
                ->with(['content', 'user' => function ($query) {
                    $query->select('id', 'name', 'username', 'image');
                }])
                ->orderBy('id', 'asc')
                ->limit(5)
                ->get();

                $prevShorts = Short::whereHas('content', function ($query) {
                    $query->where('status', ContentStatus::PUBLISHED->value);
                })
                ->where('id', '<', $currentShort->id)
                ->with(['content', 'user' => function ($query) {
                    $query->select('id', 'name', 'username', 'image');
                }])
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get();
            }
        } else {
            // No specific short requested, get a random one
            $currentShort = $shortsQuery->inRandomOrder()->first();
            
            if ($currentShort) {
                // Get some shorts for navigation
                $nextShorts = Short::whereHas('content', function ($query) {
                    $query->where('status', ContentStatus::PUBLISHED->value);
                })
                ->where('id', '!=', $currentShort->id)
                ->with(['content', 'user' => function ($query) {
                    $query->select('id', 'name', 'username', 'image');
                }])
                ->inRandomOrder()
                ->limit(10)
                ->get();
            }
        }

        // If no shorts found at all
        if (!$currentShort) {
            return redirect()->route('dashboard')->with('error', 'No shorts available');
        }

        return Inertia::render('user/short/ShowShort', [
            'currentShort' => $currentShort,
            'nextShorts' => $nextShorts,
            'prevShorts' => $prevShorts,
            'totalShorts' => Short::whereHas('content', function ($query) {
                $query->where('status', ContentStatus::PUBLISHED->value);
            })->count(),
        ]);
    }
}