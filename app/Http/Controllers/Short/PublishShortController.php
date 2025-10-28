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
