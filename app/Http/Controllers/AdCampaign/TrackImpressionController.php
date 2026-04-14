<?php

namespace App\Http\Controllers\AdCampaign;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use Illuminate\Http\Request;

class TrackImpressionController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|integer|exists:ad_campaigns,id',
        ]);

        AdCampaign::where('id', $request->input('campaign_id'))
            ->increment('impressions_count');

        return response()->json(['ok' => true]);
    }
}
