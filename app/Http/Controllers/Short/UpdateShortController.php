<?php

namespace App\Http\Controllers\Short;

use App\Http\Controllers\Controller;
use App\Traits\Short\HasPublishShort;
use App\Http\Requests\Short\UpdateShortRequest;
use App\Traits\Short\HasUpdateShort;

use App\Models\Short;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UpdateShortController extends Controller
{
    use HasUpdateShort;

    public function __invoke(UpdateShortRequest $request, Short $short)
    {
        try {
            if (!$this->canAccess()) {
                throw new \Exception(__("You are not authorized to update a short"));
            }

            $_user = User::find(Auth::user()->id);
            if ($_user->id !== $short->user_id) {
                throw new \Exception(__("You are not authorized to update this short"));
            }

            $data = $request->validated();
            $this->update($short, $data);
            return inertiaSuccessHandler(
                __("Success"),
                __("Short is updated")
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
