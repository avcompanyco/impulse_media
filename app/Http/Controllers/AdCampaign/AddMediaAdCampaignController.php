<?php

namespace App\Http\Controllers\AdCampaign;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\AdCampaignMedia;
use Illuminate\Http\Request;

class AddMediaAdCampaignController extends Controller
{
    public function __invoke(Request $request, AdCampaign $adCampaign)
    {
        $request->validate([
            'media' => 'required|file|max:102400', // 100MB max
        ]);

        $file = $request->file('media');
        
        // Auto-detect media type
        $mediaType = str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image';

        // Upload to S3
        $path = $file->store('ad_campaigns', getDisk());

        $adCampaign->mediaItems()->create([
            'media_path' => $path,
            'media_type' => $mediaType,
        ]);

        return redirect()->back()->with([
            'type' => 'success',
            'title' => __('Media Added'),
            'message' => __('Media file has been added to the campaign.'),
        ]);
    }
}
