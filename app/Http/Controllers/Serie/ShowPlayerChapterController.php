<?php

namespace App\Http\Controllers\Serie;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\Serie;
use App\Models\SeasonSerie;
use App\Models\ChapterSerie;
use App\Models\Setting;
use App\Enums\Content\ContentStatus;
use App\Enums\Content\ChapterStatus;

class ShowPlayerChapterController extends Controller
{
    public function __invoke(Serie $serie, SeasonSerie $season, ChapterSerie $chapter)
    {
        // Verify serie content is published
        $content = $serie->content;
        if ($content->status !== ContentStatus::PUBLISHED) {
            return redirect()->route('dashboard')->with('error', 'Serie not published');
        }

        // Verify chapter belongs to this season and serie
        if ($chapter->season_id !== $season->id || $season->serie_id !== $serie->id) {
            return redirect()->route('user.serie.show', $serie)->with('error', 'Chapter not found');
        }

        // Verify chapter is published
        if ($chapter->status !== ChapterStatus::PUBLISHED) {
            return redirect()->route('user.serie.show', $serie)->with('error', 'Chapter not available');
        }
        
        // Load serie with related data
        $serie = $serie->load([
            'content', 
            'watchlist', 
            'category' => function ($query) {
                $query->with(['movies' => function ($query) {
                    $query->whereHas('content', function ($query) {
                        $query->where('status', ContentStatus::PUBLISHED->value);
                    })->inRandomOrder()->limit(20);
                }, 'series' => function ($query) {
                    $query->whereHas('content', function ($query) {
                        $query->where('status', ContentStatus::PUBLISHED->value);
                    })->inRandomOrder()->limit(20);
                }]);
            }, 
            'subcategory', 
            'user' => function ($query) {
                $query->select('id', 'name', 'username', 'image');
            },
            'seasons' => function ($query) {
                $query->orderBy('id', 'asc');
            },
            'seasons.chapters' => function ($query) {
                $query->orderBy('chapter_number', 'asc');
            }
        ]);

        // Load the specific chapter
        $chapter = $chapter->load(['season', 'user']);

        $user = Auth::user();
        $hasFullAccess = false;
        $isMember = $user ? $user->isImpulseMember() : false;
        
        $rawPpvPrice = (float)($content->ppv_price ?? 0);
        $discountRate = (float)Setting::get('membership_discount_rate', 10);
        
        $ppvPrice = $rawPpvPrice;
        if ($isMember && $discountRate > 0) {
            $ppvPrice = max(0.00, round($rawPpvPrice * (1 - ($discountRate / 100)), 2));
        }

        if ($user) {
            if ($user->hasRole('admin') || $user->id === $serie->user_id) {
                $hasFullAccess = true;
            } else {
                if ($rawPpvPrice <= 0) {
                    $hasFullAccess = true;
                } else {
                    if ($content->isPurchasedBy($user)) {
                        $hasFullAccess = true;
                    } elseif ($content->allow_membership && $isMember) {
                        $hasFullAccess = true;
                    }
                }
            }
        }

        return Inertia::render('user/serie/ShowPlayerChapter', [
            'serie' => $serie,
            'season' => $season,
            'chapter' => $chapter,
            'hasFullAccess' => $hasFullAccess,
            'ppvPrice' => $ppvPrice,
            'rawPpvPrice' => $rawPpvPrice,
            'isMember' => $isMember,
            'allowMembership' => (bool)$content->allow_membership,
            'freePreviewSeconds' => (int)Setting::get('free_preview_seconds', 300),
        ]);
    }
}