<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Movie;
use App\Models\Serie;
use App\Models\Short;

use App\Models\Purchase;

use App\Enums\Content\ContentStatus;
use App\Enums\Payment\PaymentStatus;

class IndexAdminDashboardController extends Controller
{
    public function __invoke()
    {
        $subscriptionRevenue = (float) DB::table('payments')->where('status', PaymentStatus::COMPLETED)->sum('amount');
        $ppvRevenue = (float) DB::table('purchases')->where('status', 'completed')->sum('amount');
        $totalRevenue = $subscriptionRevenue + $ppvRevenue;

        $cards = [
            [
                'title' => 'Total Users',
                'value' => User::whereHas('roles', function ($query) {
                    $query->where('name', 'user');
                })->count(),
            ],
            [
                'title' => __("Active Subscriptions"),
                'value' => User::whereHas('roles', function ($query) {
                    $query->where('name', 'user');
                })->whereHas('subscriptions', function ($query) {
                    $query->where('stripe_status', 'active');
                })->count(),
            ],
            [
                'title' => __("Total Revenue"),
                'value' => "$" . number_format($totalRevenue, 2),
            ],
            [
                'title' => __("PPV Sales Revenue"),
                'value' => "$" . number_format($ppvRevenue, 2),
            ],
            [
                'title' => 'Total Movies',
                'value' => Movie::whereHas('content', function ($query) {
                    $query->where('status', ContentStatus::PUBLISHED);
                })->count(),
            ],
            [
                'title' => 'Total Series',
                'value' => Serie::whereHas('content', function ($query) {
                    $query->where('status', ContentStatus::PUBLISHED);
                })->count(),
            ],
            [
                'title' => 'Total Shorts',
                'value' => Short::whereHas('content', function ($query) {
                    $query->where('status', ContentStatus::PUBLISHED);
                })->count(),
            ],
            [
                'title' => __('Spectators'),
                'value' => User::where('user_type', 'spectator')->count(),
            ],
            [
                'title' => __('Creators'),
                'value' => User::where('user_type', 'creator')->count(),
            ],
        ];

        return Inertia::render('admin/dashboard/IndexAdmin', [
            'cards' => $cards,
        ]);
    }
}
