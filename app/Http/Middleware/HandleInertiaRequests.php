<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Category;
use App\Models\User;
use App\Models\AdCampaign;
use Illuminate\Support\Facades\Auth;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $web_categories = Category::with('subcategories')
            ->orderBy('name', 'asc')
            ->get();

        $subscriptions = [];
        $showAds = false;
        $adCampaigns = [];
        if (Auth::check()) {
            $_user = User::find(Auth::user()->id);
            $subscriptions = $_user->followings;
            $currentPlan = $_user->getCurrentPlan();
            
            // Show ads to spectators on standard plans or with no plan (free/guest view)
            if ($_user->user_type === \App\Enums\User\UserType::SPECTATOR) {
                if (!$currentPlan || $currentPlan->has_ads) {
                    $showAds = true;
                }
            } else {
                if ($currentPlan && $currentPlan->has_ads) {
                    $showAds = true;
                }
            }

            if ($showAds) {
                // Build flat list of ad items from all active campaigns for equal rotation
                $campaigns = AdCampaign::active()->with('mediaItems')->inRandomOrder()->get();
                foreach ($campaigns as $campaign) {
                    $mediaItemPaths = [];
                    // Add each media item as a separate ad entry for equal rotation
                    foreach ($campaign->mediaItems as $media) {
                        // Skip media items with no valid path
                        if (!$media->media_path || $media->media_path === '0' || $media->media_path === 0) {
                            continue;
                        }
                        $mediaItemPaths[] = $media->media_path;
                        $adCampaigns[] = [
                            'campaign_id' => $campaign->id,
                            'campaign_name' => $campaign->name,
                            'company_name' => $campaign->company_name,
                            'media_url' => $media->media_url,
                            'media_type' => $media->media_type,
                        ];
                    }
                    // Include legacy media_path if it's valid and NOT already in mediaItems
                    if ($campaign->media_path && $campaign->media_path !== '0'
                        && !in_array($campaign->media_path, $mediaItemPaths)) {
                        $adCampaigns[] = [
                            'campaign_id' => $campaign->id,
                            'campaign_name' => $campaign->name,
                            'company_name' => $campaign->company_name,
                            'media_url' => $campaign->media_url,
                            'media_type' => $campaign->media_type,
                        ];
                    }
                }
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user() ? array_merge($request->user()->toArray(), [
                    'is_member' => $request->user()->isImpulseMember(),
                    'is_admin' => $request->user()->hasRole('admin') || $request->user()->user_type === \App\Enums\User\UserType::ADMIN,
                ]) : null,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'complete' => $request->session()->get('complete'),
                'type' => $request->session()->get('type'),
                'title' => $request->session()->get('title'),
                'message' => $request->session()->get('message'),
                'url' => $request->session()->get('url'),
            ],
            'web_categories' => $web_categories,
            'subscriptions' => $subscriptions,
            'show_ads' => $showAds,
            'ad_campaigns' => $adCampaigns,
        ];
    }
}
