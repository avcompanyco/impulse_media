<?php

namespace App\Http\Controllers\AdCampaign;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class StoreAdCampaignController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'media_files' => 'required|array|min:1',
            'media_files.*' => 'file|max:102400', // 100MB max each
        ]);

        $files = $request->file('media_files');
        $firstPath = null;
        $firstType = null;

        // Upload first file to get the legacy media_path
        $firstFile = $files[0];
        $firstType = str_starts_with($firstFile->getMimeType(), 'video/') ? 'video' : 'image';
        $firstPath = $firstFile->store('ad_campaigns', getDisk());

        if (!$firstPath) {
            Log::error('Ad campaign media upload failed', ['name' => $request->input('name')]);
            return redirect()->back()->withErrors(['media_files' => 'Failed to upload media file. Please try again.']);
        }

        $campaign = AdCampaign::create([
            'name' => $request->input('name'),
            'company_name' => $request->input('company_name'),
            'media_path' => $firstPath,
            'media_type' => $firstType,
            'status' => 'active',
        ]);

        // Create mediaItem for the first file
        $campaign->mediaItems()->create([
            'media_path' => $firstPath,
            'media_type' => $firstType,
        ]);

        // Upload and create mediaItems for ALL additional files
        for ($i = 1; $i < count($files); $i++) {
            $file = $files[$i];
            $mediaType = str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image';
            $path = $file->store('ad_campaigns', getDisk());

            if ($path) {
                $campaign->mediaItems()->create([
                    'media_path' => $path,
                    'media_type' => $mediaType,
                ]);
            } else {
                Log::warning('Ad campaign extra media upload failed', ['campaign_id' => $campaign->id, 'file_index' => $i]);
            }
        }

        return redirect()->back()->with([
            'type' => 'success',
            'title' => __('Campaign Created'),
            'message' => __('The ad campaign has been created with ' . count($files) . ' media file(s).'),
        ]);
    }
}
