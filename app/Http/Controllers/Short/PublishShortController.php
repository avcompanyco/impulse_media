<?php

namespace App\Http\Controllers\Short;

use App\Http\Controllers\Controller;
use App\Traits\Short\HasPublishShort;
use App\Http\Requests\Short\UpdateShortRequest;

use App\Models\Short;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PublishShortController extends Controller
{
    use HasPublishShort;

    public function __invoke(UpdateShortRequest $request, Short $short)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to publish a short"));
            }

            $_user = User::find(Auth::user()->id);
            if ($_user->id !== $short->user_id) {
                throw new \Exception(__("You are not authorized to publish this short"));
            }

            // Enforce per-category upload limit
            $plan = $_user->getCurrentPlan();
            if ($plan && !$plan->hasUnlimitedContent()) {
                $monthlyCount = $_user->shorts()
                    ->where('status', \App\Enums\Content\ContentStatus::PUBLISHED->value)
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->count();
                if ($monthlyCount >= $plan->shorts_upload_count) {
                    throw new \Exception(__("Upload limit reached. Your plan allows {$plan->shorts_upload_count} shorts per month."));
                }
            }

            $data = $request->validated();
            $this->publish($short, $data);
            // return redirect()->route('user.upload.short');
            return inertiaSuccessHandler(
                __("Success"),
                __("Short is published")
            );

        } catch (\Throwable $th) {
            return inertiaErrorHandler(
                __("Error"),
                $th->getMessage()
            );
        }
    }

    public function canAccess()
    {
        return true;
    }
}
