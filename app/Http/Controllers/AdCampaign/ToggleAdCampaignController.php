<?php

namespace App\Http\Controllers\AdCampaign;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use Illuminate\Http\Request;

class ToggleAdCampaignController extends Controller
{
    public function __invoke(AdCampaign $campaign)
    {
        $campaign->status = $campaign->status === 'active' ? 'inactive' : 'active';
        $campaign->save();

        $statusText = $campaign->status === 'active' ? 'activated' : 'deactivated';

        return redirect()->back()->with([
            'type' => 'success',
            'title' => __('Campaign Updated'),
            'message' => __("The campaign has been {$statusText}."),
        ]);
    }
}
