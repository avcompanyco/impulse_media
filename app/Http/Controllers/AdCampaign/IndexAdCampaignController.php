<?php

namespace App\Http\Controllers\AdCampaign;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use Inertia\Inertia;

class IndexAdCampaignController extends Controller
{
    public function __invoke()
    {
        $campaigns = AdCampaign::orderBy('created_at', 'desc')->get();

        return Inertia::render('admin/ads/IndexAds', [
            'campaigns' => $campaigns,
        ]);
    }
}
