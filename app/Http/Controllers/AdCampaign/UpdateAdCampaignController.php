<?php

namespace App\Http\Controllers\AdCampaign;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use Illuminate\Http\Request;

class UpdateAdCampaignController extends Controller
{
    public function __invoke(Request $request, AdCampaign $adCampaign)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
        ]);

        $adCampaign->update([
            'name' => $request->input('name'),
            'company_name' => $request->input('company_name'),
        ]);

        return redirect()->back()->with([
            'type' => 'success',
            'title' => __('Campaign Updated'),
            'message' => __('The ad campaign has been updated successfully.'),
        ]);
    }
}
