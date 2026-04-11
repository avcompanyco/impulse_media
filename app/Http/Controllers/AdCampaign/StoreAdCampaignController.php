<?php

namespace App\Http\Controllers\AdCampaign;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreAdCampaignController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'media' => 'required|file|max:102400', // 100MB max
            'media_type' => 'required|in:image,video',
        ]);

        $file = $request->file('media');
        $mediaType = $request->input('media_type');

        // Upload to S3
        $path = $file->store('ad_campaigns', getDisk());

        $campaign = AdCampaign::create([
            'name' => $request->input('name'),
            'company_name' => $request->input('company_name'),
            'media_path' => $path,
            'media_type' => $mediaType,
            'status' => 'active',
        ]);

        return redirect()->back()->with([
            'type' => 'success',
            'title' => __('Campaign Created'),
            'message' => __('The ad campaign has been created successfully.'),
        ]);
    }
}
