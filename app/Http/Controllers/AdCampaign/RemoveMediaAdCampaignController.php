<?php

namespace App\Http\Controllers\AdCampaign;

use App\Http\Controllers\Controller;
use App\Models\AdCampaignMedia;

class RemoveMediaAdCampaignController extends Controller
{
    public function __invoke(AdCampaignMedia $media)
    {
        $media->deleteMedia();
        $media->delete();

        return redirect()->back()->with([
            'type' => 'success',
            'title' => __('Media Removed'),
            'message' => __('Media file has been removed from the campaign.'),
        ]);
    }
}
