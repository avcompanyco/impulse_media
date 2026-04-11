<?php

namespace App\Http\Controllers\AdCampaign;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;

class DestroyAdCampaignController extends Controller
{
    public function __invoke(AdCampaign $campaign)
    {
        // Delete media file from storage
        $campaign->deleteMedia();

        // Delete the campaign record
        $campaign->delete();

        return redirect()->back()->with([
            'type' => 'success',
            'title' => __('Campaign Deleted'),
            'message' => __('The ad campaign and all its media have been deleted.'),
        ]);
    }
}
