<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Movie;
use App\Models\Serie;
use App\Models\Payout;
use App\Models\CreatorEarning;
use App\Models\Setting;
use Inertia\Inertia;

class CreatorDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        // Creators only
        if ($user->isSpectator()) {
            return redirect()->route('dashboard')->with('error', 'Access Denied');
        }

        // 1. Calculate balance stats
        $lifetimeEarnings = $user->lifetime_earnings;
        $currentBalance = $user->creator_balance;
        
        $withdrawn = $user->payouts()
            ->where('status', 'approved')
            ->sum('amount');
            
        $pendingPayouts = $user->payouts()
            ->where('status', 'pending')
            ->sum('amount');

        // 2. Fetch payout history
        $payouts = $user->payouts()
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Fetch earnings breakdown
        $earnings = $user->earnings()
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // 4. Fetch content list & performance
        $contentsData = collect();

        // Fetch Movies
        $movies = Movie::where('user_id', $user->id)->with(['content'])->get();
        foreach ($movies as $movie) {
            $content = $movie->content;
            if ($content) {
                $salesCount = $content->purchases()->where('status', 'completed')->count();
                $revenue = (float)$content->purchases()->where('status', 'completed')->sum('creator_share');
                
                $contentsData->push([
                    'id' => $content->id,
                    'contentable_id' => $movie->id,
                    'title' => $movie->title ?: 'Untitled Movie',
                    'type' => 'movie',
                    'views_count' => $content->views_count,
                    'ppv_price' => (float)$content->ppv_price,
                    'allow_membership' => (bool)$content->allow_membership,
                    'sales_count' => $salesCount,
                    'revenue' => $revenue,
                ]);
            }
        }

        // Fetch Series
        $series = Serie::where('user_id', $user->id)->with(['content'])->get();
        foreach ($series as $serie) {
            $content = $serie->content;
            if ($content) {
                $salesCount = $content->purchases()->where('status', 'completed')->count();
                $revenue = (float)$content->purchases()->where('status', 'completed')->sum('creator_share');

                $contentsData->push([
                    'id' => $content->id,
                    'contentable_id' => $serie->id,
                    'title' => $serie->title ?: 'Untitled Series',
                    'type' => 'series',
                    'views_count' => $content->views_count,
                    'ppv_price' => (float)$content->ppv_price,
                    'allow_membership' => (bool)$content->allow_membership,
                    'sales_count' => $salesCount,
                    'revenue' => $revenue,
                ]);
            }
        }

        // Settings fallbacks
        $minPayoutThreshold = (float)Setting::get('min_payout_threshold', 50.00);
        $minPpvPrice = (float)Setting::get('min_ppv_price', 0.99);

        return Inertia::render('user/creator/CreatorDashboard', [
            'stats' => [
                'lifetime_earnings' => $lifetimeEarnings,
                'current_balance' => $currentBalance,
                'withdrawn' => (float)$withdrawn,
                'pending_payouts' => (float)$pendingPayouts,
            ],
            'payouts' => $payouts,
            'earnings' => $earnings,
            'contents' => $contentsData,
            'settings' => [
                'min_payout_threshold' => $minPayoutThreshold,
                'min_ppv_price' => $minPpvPrice,
            ],
        ]);
    }
}
