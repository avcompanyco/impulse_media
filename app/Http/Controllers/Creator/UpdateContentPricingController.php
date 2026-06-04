<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Content;
use App\Models\Setting;

class UpdateContentPricingController extends Controller
{
    public function __invoke(Request $request, Content $content)
    {
        try {
            if ($content->user_id !== Auth::id()) {
                throw new \Exception(__("You are not authorized to update this content's pricing."));
            }

            $minPpvPrice = (float)Setting::get('min_ppv_price', 0.99);

            $data = $request->validate([
                'ppv_price' => [
                    'required',
                    'numeric',
                    function ($attribute, $value, $fail) use ($minPpvPrice) {
                        $val = (float)$value;
                        if ($val !== 0.00 && $val < $minPpvPrice) {
                            $fail(__('The price must be either 0 (free) or at least $' . number_format($minPpvPrice, 2)));
                        }
                    }
                ],
                'allow_membership' => 'required|boolean',
            ]);

            $content->update([
                'ppv_price' => $data['ppv_price'],
                'allow_membership' => $data['allow_membership'],
            ]);

            return back()->with('success', __('Content pricing updated successfully.'));
        } catch (\Throwable $th) {
            return back()->withErrors(['ppv_price' => $th->getMessage()]);
        }
    }
}
